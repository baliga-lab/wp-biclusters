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
require_once('ajax_source.php');

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
    wp_enqueue_style('qtip', plugin_dir_url(__FILE__) . 'css/jquery.qtip.min.css', null, false, false);

    wp_enqueue_script('datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', array('jquery'));
    // for debugging only
    //wp_enqueue_script('datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.js', array('jquery'));
    wp_enqueue_script('isblogo', plugin_dir_url(__FILE__) . 'js/isblogo.js', array('jquery'));
    wp_enqueue_script('qtip', plugin_dir_url(__FILE__) . 'js/jquery.qtip.min.js', array('jquery'), false, true);
    wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . 'js/highcharts.js', array('jquery'));

    biclusters_add_shortcodes();
    biclusters_datatables_source_init();
    add_filter('query_vars', 'add_query_vars_filter');
}

add_action('admin_init', 'biclusters_settings_init');
add_action('init', 'biclusters_init');

?>
