<?php
/**
 * @package biclusters
 * @version 1.01
 */
/*
Plugin Name: wp-biclusters
Plugin URI: https://github.com/baliga-lab/wp-biclusters
Description: A plugin that pulls in information from a biclusters web service
Author: Wei-ju Wu
Version: 1.0
Author URI: http://www.systemsbiology.org
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

/**********************************************************************
 * Settings Section
 * Users provide and store information about the web service and
 * structure of their web site here
 **********************************************************************/

function biclusters_settings_init() {

    // This is the General section
    add_settings_section(
        "general_section",
        "Biclusters",
        "general_section_cb",
        'general'  // general, writing, reading, discussion, media, privacy, permalink
    );
    add_settings_field('source_url', 'Data Source URL', 'source_url_field_cb', 'general',
                       'general_section');
    add_settings_field('bicluster_slug', 'Bicluster Slug', 'slug_field_cb', 'general',
                       'general_section');

    register_setting('general', 'source_url');
    register_setting('general', 'bicluster_slug');
}

function general_section_cb()
{
    echo "<p>General settings for the Biclusters Plugin</p>";
}

function source_url_field_cb()
{
    $url = get_option('source_url', '');
    echo "<input type=\"text\" name=\"source_url\" value=\"" . $url . "\">";
}

function slug_field_cb()
{
    $slug = get_option('bicluster_slug', 'biclusters');
    echo "<input type=\"text\" name=\"bicluster_slug\" value=\"" . $slug . "\">";
}

/**********************************************************************
 * Plugin Section
 **********************************************************************/

require_once('short_codes.php');

/*
 * Custom variables that are supposed to be used must be made
 * available explicitly through the filter mechanism.
 */
function add_query_vars_filter($vars) {
    $vars[] = "bicluster";
    $vars[] = "condition";
    $vars[] = "gene";
    $vars[] = "corem";
    return $vars;
}

function biclusters_init()
{
    // add all javascript and style files that are used by our plugin
    wp_enqueue_style('datatables', plugin_dir_url(__FILE__) . 'css/jquery.dataTables.min.css');
    wp_enqueue_style('wp-biclusters', plugin_dir_url(__FILE__) . 'css/wp-biclusters.css');

    wp_enqueue_script('datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', array('jquery'));
    // for debugging only
    //wp_enqueue_script('datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.js', array('jquery'));
    wp_enqueue_script('isblogo', plugin_dir_url(__FILE__) . 'js/isblogo.js', array('jquery'));

    // a hook Javascript to anchor our AJAX call
    wp_enqueue_script('ajax_dt', plugins_url('js/ajax_dt.js', __FILE__), array('jquery'));
    wp_localize_script('ajax_dt', 'ajax_dt', array('ajax_url' => admin_url('admin-ajax.php')), '1.0', true);

    biclusters_add_shortcodes();
    add_filter('query_vars', 'add_query_vars_filter');
}

/**
 * Datatables backend for genes. This is called via AJAX.
 */
function genes_dt_callback() {
    header("Content-type: application/json");
    $draw = intval($_GET['draw']);  // integer
    $start = $_GET['start'];  // integer
    $length = $_GET['length'];  // integer

    $source_url = get_option('source_url', '');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/genes?start=" . $start . "&length=" . $length);
    $genes = json_decode($genes_json)->genes;

    // turn gene_name, accession and chromosome into links before sending them to DataTables
    foreach ($genes as $g) {
        $chrom = $g->chromosome;
        $acc = $g->accession;
        $name = $g->gene_name;

        $g->gene_name = "<a href=\"index.php/gene/?gene=" . $name . "\">" . $name . "</a>";
        $g->chromosome = "<a href=\"https://www.ncbi.nlm.nih.gov/nuccore/" . $chrom . "\">" . $chrom . "</a>";
        $g->accession = "<a href=\"https://www.ncbi.nlm.nih.gov/protein/" . $acc . "\">" . $acc . "</a>";
    }
    $data = json_encode($genes);
    $summary_json = file_get_contents($source_url . "/api/v1.0.0/summary");
    $summary = json_decode($summary_json);

    error_log("start: " . $start . " length: " . $length);
    $records_total = $summary->num_genes;

    $doc = <<<EOT
{
  "draw": $draw,
  "recordsTotal": $records_total,
  "recordsFiltered": $records_total,
  "data": $data
}
EOT;
    #echo "{\"Hello\": \"World\", \"draw\": \"" . $draw . "\"}";
    echo $doc;
    wp_die();
}

add_action('admin_init', 'biclusters_settings_init');
add_action('init', 'biclusters_init');

// We need callbacks for both non-privileged and privileged users
add_action('wp_ajax_nopriv_genes_dt', 'genes_dt_callback');
add_action('wp_ajax_genes_dt', 'genes_dt_callback');

?>
