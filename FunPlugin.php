<?php
/**
 * Plugin Name: Updater
 * Plugin URI: https://github.com/Lkilasonia/updater
 * Description: This is a Fun Plugin.
 * Version: 5.0
 * Author: Lasha Kilasonia
 * Author URI: https://elementar.ge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Check for plugin updates
add_action('admin_init', 'check_for_plugin_update');

function check_for_plugin_update() {
    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'];
    $repo = 'Lkilasonia/updater';
    $github_response = get_github_version($repo);

    if ($github_response && version_compare($current_version, $github_response['tag_name'], '<')) {
        add_action('admin_notices', 'show_update_notification');
    }
}

function get_github_version($repo) {
    $url = "https://api.github.com/repos/$repo/releases/latest";
    $args = array(
        'headers' => array(
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress Plugin Updater',
            'Authorization' => 'token ' . getenv('GITHUB_ACCESS_TOKEN'),
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['tag_name'])) {
        return $data;
    }

    return false;
}


function show_update_notification() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e('There is a new version of the Updater plugin available. Please update to the latest version.', 'text-domain'); ?></p>
    </div>
    <?php
}

function plugin_update($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'];
    $repo = 'Lkilasonia/updater';
    $github_response = get_github_version($repo);

    if ($github_response && version_compare($current_version, $github_response['tag_name'], '<')) {
        $plugin_slug = plugin_basename(__FILE__);
        $transient->response[$plugin_slug] = (object) array(
            'slug' => $plugin_slug,
            'new_version' => $github_response['tag_name'],
            'url' => $plugin_data['PluginURI'],
            'package' => $github_response['zipball_url'],
        );
    }

    return $transient;
}

add_filter('pre_set_site_transient_update_plugins', 'plugin_update');
add_filter('plugins_api', 'plugin_update_info', 20, 3);

function plugin_update_info($res, $action, $args) {
    $plugin_slug = plugin_basename(__FILE__);

    if ('plugin_information' !== $action || $plugin_slug !== $args->slug) {
        return false;
    }

    $repo = 'Lkilasonia/updater';
    $github_response = get_github_version($repo);

    if (!$github_response) {
        return $res;
    }

    $plugin_data = get_plugin_data(__FILE__);

    $res = (object) array(
        'name' => $plugin_data['Name'],
        'slug' => $plugin_slug,
        'version' => $github_response['tag_name'],
        'author' => $plugin_data['Author'],
        'author_profile' => $plugin_data['AuthorURI'],
        'homepage' => $plugin_data['PluginURI'],
        'short_description' => $plugin_data['Description'],
        'sections' => array(
            'description' => $plugin_data['Description'],
            'changelog' => isset($github_response['body']) ? $github_response['body'] : '',
        ),
        'download_link' => $github_response['zipball_url'],
        'last_updated' => $github_response['published_at'],
    );

    return $res;
}
?>
