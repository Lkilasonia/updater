<?php
/**
 * Plugin Name: Updater
 * Plugin URI: https://github.com/Lkilasonia/updater
 * Description: This is a Fun Plugin.
 * Version: 2.0
 * Author: Lasha Kilasonia
 * Author URI: https://elementar.ge
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include necessary files
require_once __DIR__ . '/includes/class-plugin-updater.php';
require_once __DIR__ . '/includes/class-github-api.php';

// Hard-coded GitHub Access Token
$github_access_token = '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';

// Initialize plugin updater
add_action('admin_init', function() use ($github_access_token) {
    PluginUpdater::init($github_access_token);
});

delete_site_transient('update_plugins');

