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

    // This is the writing section
    add_settings_section(
        "writing_section",
        "Biclusters",
        "writing_section_cb",
        'writing'
    );
    add_settings_field('bicluster_info_template', 'Template for Bicluster Pages', 'info_template_field_cb', 'writing',
                       'writing_section');
    register_setting('writing', 'bicluster_info_template');
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

function writing_section_cb()
{
    echo "<p>General settings for the Biclusters Plugin</p>";
}

function info_template_field_cb()
{
    $info_template = get_option('bicluster_info_template', 'This is a test');
    echo "<textarea rows=\"3\" cols=\"80\" name=\"bicluster_info_template\">" . $info_template . "</textarea>";
}

/**********************************************************************
 * Plugin Section
 **********************************************************************/
/**********************************************************************
 * Custom Short codes
 * Render the custom fields by interfacting with the web service
 **********************************************************************/

function bicluster_genes_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $use_microformats = $atts['microformats'] == "true";

    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $row_membs_json = file_get_contents($source_url . "/api/v1.0.0/cluster_genes/" . $bicluster_num);
    $row_membs = json_decode($row_membs_json, true)["genes"];
    $content .= "<ul style=\"font-size: 8pt\">";
    foreach ($row_membs as $m) {
        $content .= "<li>" . $m . "</li>";
    }
    $content .= "</ul>";
    if ($use_microformats) {
        $species = "Species (TODO)";
        $content .= "<div class=\"gaggle-data\" style=\"display:none\">";
        $content .= "  <span class=\"gaggle-name\">Row members cluster " . $bicluster_num . "</span>";
        $content .= "  <span class=\"gaggle-species\">" . $species . "</span>";
        $content .= "  <span class=\"gaggle-namelist\">";
        $content .= "    <ol>";
        foreach ($row_membs as $m) {
            $content .= "<li>" . $m . "</li>";
        }
        $content .= "    </ol>";
        $content .= "  </span>";
        $content .= "</div>";
    }
    return $content;
}

function bicluster_conditions_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    $use_microformats = $atts['microformats'] == "true";

    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $col_membs_json = file_get_contents($source_url . "/api/v1.0.0/cluster_conditions/" . $bicluster_num);
    $col_membs = json_decode($col_membs_json, true)["conditions"];
    $content .= "<ul style=\"font-size: 8pt\">";
    foreach ($col_membs as $m) {
        $content .= "<li>" . $m . "</li>";
    }
    $content .= "</ul>";
    if ($use_microformats) {
        $species = "Species (TODO)";
        $content .= "<div class=\"gaggle-data\" style=\"display:none\">";
        $content .= "  <span class=\"gaggle-name\">Column members cluster " . $bicluster_num . "</span>";
        $content .= "  <span class=\"gaggle-species\">" . $species . "</span>";
        $content .= "  <span class=\"gaggle-namelist\">";
        $content .= "    <ol>";
        foreach ($col_membs as $m) {
            $content .= "<li>" . $m . "</li>";
        }
        $content .= "    </ol>";
        $content .= "  </span>";
        $content .= "</div>";
    }
    return $content;
}

function bicluster_motifs_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $source_url = get_option('source_url', '');
    $bicluster_num = get_query_var('bicluster');
    $motifs_json = file_get_contents($source_url . "/api/v1.0.0/cluster_pssms/" . $bicluster_num);

    $content .= "<div id=\"canvas_1\"></div>";
    $content .= "<div id=\"canvas_2\"></div>";

    $content .= "<script>";
    $content .= "  var canvasOptions = {";
    $content .= "    width: 300,";
    $content .= "    height: 150,";
    $content .= "    glyphStyle: '20pt Helvetica'";
    $content .= "  };";
    $content .= "  var motifs = " . $motifs_json;
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    isblogo.makeLogo('canvas_1', motifs['motifs'][0], canvasOptions);";
    $content .= "    isblogo.makeLogo('canvas_2', motifs['motifs'][1], canvasOptions);";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function model_overview_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $summary_json = file_get_contents($source_url . "/api/v1.0.0/summary");
    $summary = json_decode($summary_json, true);

    if ($content == null) {
        $content = '';
    }
    $content .= "<h2>Model Overview</h2>";
    $content .= "<table id=\"summary\" class=\"row-border\">";
    $content .= "  <thead><tr><th>#</th><th>Description</th></tr></thead>";
    $content .= "  <tbody>";
    $content .= "    <tr><td><a href=\"index.php/genes/\">" . $summary["num_genes"] . "</a></td><td>Genes</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/conditions/\">" . $summary["num_conditions"] . "</a></td><td>Conditions</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/corems/\">" . $summary["num_corems"] . "</a></td><td>Corems</td></tr>";
    $content .= "    <tr><td><a href=\"index.php/biclusters/\">" . $summary["num_biclusters"] . "</a></td><td>Biclusters</td></tr>";
    $content .= "    <tr><td>" . $summary["num_gres"] . "</td><td>GREs</td></tr>";
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#summary').DataTable({";
    $content .= "      'paging': false,";
    $content .= "      'info': false";
    $content .= "      'searching': false";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function corems_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $corems_json = file_get_contents($source_url . "/api/v1.0.0/corems");
    $corems = json_decode($corems_json, true)["corems"];

    if ($content == null) {
        $content = '';
    }

    $content .= "<table id=\"corems\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Corem ID</th><th># Genes</th><th># Conditions</th><th>#GREs</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($corems as $c) {
        $content .= "    <tr><td>" . $c["id"] . "</td><td>". $c["num_genes"] . "</td><td>" . $c["num_conds"] . "</td><td>(TODO)</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#corems').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function conditions_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $conds_json = file_get_contents($source_url . "/api/v1.0.0/conditions");
    $conds = json_decode($conds_json, true)["conditions"];

    if ($content == null) {
        $content = '';
    }

    $content .= "<table id=\"conditions\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Condition ID</th><th>Name</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($conds as $c) {
        $content .= "    <tr><td>" . $c["id"] . "</td><td><a href=\"index.php/condition/?condition=" . $c["id"] . "\">". $c["name"] . "</a></td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#conditions').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function genes_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $genes_json = file_get_contents($source_url . "/api/v1.0.0/genes");
    $genes = json_decode($genes_json, true)["genes"];
    error_log("GENES: " . $genes);

    if ($content == null) {
        $content = '';
    }

    $content .= "<table id=\"genes\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Gene ID</th><th>Name</th><th>Common Name</th><th>Accession</th><th>Description</th><th>Start</th><th>Stop</th><th>Strand</th><th>Chromosome</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($genes as $g) {
        $content .= "    <tr><td>" . $g["id"] . "</td><td>". $g["gene_name"] . "</td><td>" . $g["common_name"] . "</td><td>" . $g["accession"] ."</td><td>" . $g["description"] . "</td><td>" . $g["start"] . "</td><td>" . $g["stop"] . "</td><td>"  . $g["strand"] . "</td><td>" . $g["chromosome"] . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#genes').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

function condition_name_shortcode($attr=[], $content=null)
{
    $condition_id = get_query_var('condition');
    if (!$condition_id) return "(no condition provided)";
    $source_url = get_option('source_url', '');
    $cond_json = file_get_contents($source_url . "/api/v1.0.0/condition_info/" . $condition_id);
    error_log($cond_json);
    $cond = json_decode($cond_json, true)["condition"];
    error_log('$condition_id: ' . $condition_id . " name: " . $cond["name"]);
    return $cond["name"];
}

function biclusters_table_shortcode($attr=[], $content=null)
{
    $source_url = get_option('source_url', '');
    $clusters_json = file_get_contents($source_url . "/api/v1.0.0/biclusters");
    $clusters = json_decode($clusters_json, true)["biclusters"];

    if ($content == null) {
        $content = '';
    }

    $content .= "<table id=\"biclusters\" class=\"stripe row-border\">";
    $content .= "  <thead><tr><th>Bicluster ID</th><th># Genes</th><th># Conditions</th><th>Residual</th></tr></thead>";
    $content .= "  <tbody>";
    foreach ($clusters as $c) {
        $content .= "    <tr><td>" . $c["id"] . "</td><td>". $c["num_genes"] . "</td><td>" . $c["num_conditions"] . "</td><td>" . $c["residual"] . "</td></tr>";
    }
    $content .= "  </tbody>";
    $content .= "</table>";
    $content .= "<script>";
    $content .= "  jQuery(document).ready(function() {";
    $content .= "    jQuery('#biclusters').DataTable({";
    $content .= "    })";
    $content .= "  });";
    $content .= "</script>";
    return $content;
}

/**********************************************************************
 * Custom post type (TODO)
 * We can define custom bicluster pages using a custom post type
 **********************************************************************/

/**********************************************************************
 * Fake page
 * We define a fake page for the bicluster slug, in order to
 * avoid that the user has to create many real pages for each cluster
 * To executed: the URL needs to have "<prefix>/index.php/biclusters"
 * TODO: can we have user-defined page templates for bicluster info pages ?
 * Example URL: http://localhost/~weiju/wordpress/index.php/bicluster/?bicluster=23
 **********************************************************************/

function make_wp_post($posts, $post_name, $post_title, $post_content)
{
    global $wp_query;
    $post = new stdClass;
    $post->post_author = 1;
    $post->post_name = $post_name;
    $post->post_title = $post_title;
    $post->post_content = $post_content;
    $post->ID = -999;
    $post->post_type = 'page';
    $post->post_status = 'status';
    $post->comment_status = 'closed';
    $post->ping_status = 'open';
    $post->comment_count = 0;
    $post->post_date = current_time('mysql');
    $post->post_date_gmt = current_time('mysql', 1);

    $posts = NULL;
    $posts[] = $post;

    // information to wp_query
    $wp_query->is_page = true;
    $wp_query->is_singular = true;
    $wp_query->is_home = false;
    $wp_query->is_archive = false;
    $wp_query->is_category = false;
    unset($wp_query->query['error']);
    $wp_query->query_vars['error'] = '';
    $wp_query->is_404 = false;
    return $posts;
}


function biclusters_fakepage_detect($posts)
{
    global $wp, $wp_query;
    $plugin_slug =  get_option('bicluster_slug', 'biclusters');
    $source_url = get_option('source_url', '');

    $url_comps = explode('/', $wp->request);
    $lead = $url_comps[0];

    if ($lead == $plugin_slug) {
        $content_template = get_option('bicluster_info_template');
        error_log("FAKE PAGE DETECTOR EXECUTED for biclusters plugin");
        $bicluster_num = get_query_var('bicluster');
        error_log('bicluster: ' . get_query_var('bicluster'));
        $posts = make_wp_post($posts, "bicluster", 'Information for Bicluster ' . $bicluster_num, $content_template);

    } else {
        error_log("FAKE PAGE DETECTOR, unknown request: " . $url_comps[0]);
    }
    return $posts;
}


/*
 * Custom variables that are supposed to be used must be made
 * available explicitly through the filter mechanism.
 */
function add_query_vars_filter($vars) {
    $vars[] = "bicluster";
    $vars[] = "condition";
    return $vars;
}

function biclusters_init()
{
    // add all javascript and style files that are used by our plugin
    wp_enqueue_style('datatables', plugin_dir_url(__FILE__) . 'css/jquery.dataTables.min.css');
    wp_enqueue_style('wp-biclusters', plugin_dir_url(__FILE__) . 'css/wp-biclusters.css');

    wp_enqueue_script('datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', array('jquery'));
    wp_enqueue_script('isblogo', plugin_dir_url(__FILE__) . 'js/isblogo.js', array('jquery'));

    add_shortcode('bicluster_genes', 'bicluster_genes_shortcode');
    add_shortcode('bicluster_conditions', 'bicluster_conditions_shortcode');
    add_shortcode('bicluster_motifs', 'bicluster_motifs_shortcode');
    add_shortcode('model_overview', 'model_overview_shortcode');
    add_shortcode('condition_name', 'condition_name_shortcode');

    // EGRIN2 specific
    add_shortcode('corems_table', 'corems_table_shortcode');
    add_shortcode('conditions_table', 'conditions_table_shortcode');
    add_shortcode('genes_table', 'genes_table_shortcode');
    add_shortcode('biclusters_table', 'biclusters_table_shortcode');

    add_filter('the_posts', 'biclusters_fakepage_detect');
    add_filter('query_vars', 'add_query_vars_filter');
}

add_action('admin_init', 'biclusters_settings_init');
add_action('init', 'biclusters_init');


?>