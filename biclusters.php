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
    add_settings_section(
        "general_section",
        "Biclusters",
        "general_section_cb",
        'general'  // general, writing, reading, discussion, media, privacy, permalink
    );
    add_settings_field(
        'source_url',
        'Data Source URL',
        'source_url_field_cb',
        'general',
        'general_section'
    );

    register_setting('general', 'source_url');

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

function demo_shortcode($atts=[], $content=null)
{
    if ($content == null) {
        $content = '';
    }
    $content .= 'This is rendered from demo shortcode';
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
 **********************************************************************/
function biclusters_fakepage_detect($posts)
{
    global $wp, $wp_query;
    $plugin_slug = 'biclusters';
    if ($wp->request == $plugin_slug) {
        error_log("FAKE PAGE DETECTOR EXECUTED for biclusters plugin");
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = 'bicluster';
        $post->post_title = 'Bicluster Information';
        $posts = NULL;
        $posts[] = $post;
    }
    return $posts;
}



function biclusters_init()
{
    add_shortcode('bicluster_demo', 'demo_shortcode');
    add_filter('the_posts', 'biclusters_fakepage_detect');
}

add_action('admin_init', 'biclusters_settings_init');
add_action('admin_notices', 'biclusters_hello');
add_action('init', 'biclusters_init');


?>