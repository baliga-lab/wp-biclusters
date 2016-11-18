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

function biclusters_hello() {
    echo "<p>Hello from Biclusters !</p>";
}

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

function biclusters_fakepage_detect($posts)
{
    global $wp, $wp_query;
    $plugin_slug =  get_option('bicluster_slug', 'biclusters');
    $source_url = get_option('source_url', '');
    $content_template .= "<script src=" . $js_src . "></script>";
    $content_template .= get_option('bicluster_info_template');

    $url_comps = explode('/', $wp->request);
    $lead = $url_comps[0];
    $bicluster_num = get_query_var('bicluster');
    error_log('bicluster: ' . get_query_var('bicluster'));

    if ($lead == $plugin_slug) {

        error_log("FAKE PAGE DETECTOR EXECUTED for biclusters plugin");
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = 'bicluster';
        $post->post_title = 'Information for Bicluster ' . $bicluster_num;
        $post->post_content = $content_template; //$content;
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
    } else {
        error_log("FAKE PAGE DETECTOR, unknown request: " . $url_comps[0] . ', ' . $url_comps[1]);
    }
    return $posts;
}


/*
 * Custom variables that are supposed to be used must be made
 * available explicitly through the filter mechanism.
 */
function add_query_vars_filter($vars) {
    $vars[] = "bicluster";
    return $vars;
}

function biclusters_init()
{
    // This line will include ISBlogo in all pages that are used generated by this
    // plugin
    wp_enqueue_script('isblogo', plugin_dir_url(__FILE__) . 'isblogo.js', array('jquery'));

    add_shortcode('bicluster_genes', 'bicluster_genes_shortcode');
    add_shortcode('bicluster_conditions', 'bicluster_conditions_shortcode');
    add_shortcode('bicluster_motifs', 'bicluster_motifs_shortcode');
    add_filter('the_posts', 'biclusters_fakepage_detect');
    add_filter('query_vars', 'add_query_vars_filter');
}

add_action('admin_init', 'biclusters_settings_init');
add_action('admin_notices', 'biclusters_hello');
add_action('init', 'biclusters_init');


?>