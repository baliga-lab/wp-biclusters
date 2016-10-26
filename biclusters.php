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


function biclusters_hello() {
    echo "<p>Hello from Biclusters !</p>";
}

add_action('admin_init', 'biclusters_settings_init');
add_action('admin_notices', 'biclusters_hello');

/**********************************************************************
 * Plugin Section
 * Render the custom fields by interfacting with the web service
 **********************************************************************/

?>