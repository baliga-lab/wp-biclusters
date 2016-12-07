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

require_once('short_codes.php');

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
    wp_enqueue_script('isblogo', plugin_dir_url(__FILE__) . 'js/isblogo.js', array('jquery'));

    biclusters_add_shortcodes();
    //add_filter('the_posts', 'biclusters_fakepage_detect');
    add_filter('query_vars', 'add_query_vars_filter');
}

add_action('admin_init', 'biclusters_settings_init');
add_action('init', 'biclusters_init');

?>
