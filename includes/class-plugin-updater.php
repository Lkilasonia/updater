<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-github-api.php';

class PluginUpdater {
    private static $github_api;

    public static function init($access_token) {
        self::$github_api = new GitHubAPI($access_token);
        add_action('admin_init', [__CLASS__, 'check_for_plugin_update']);
        add_filter('pre_set_site_transient_update_plugins', [__CLASS__, 'filter_plugin_updates']);
        add_filter('plugins_api', [__CLASS__, 'plugin_information'], 20, 3);
    }

    public static function check_for_plugin_update() {
        $plugin_file = plugin_dir_path(__DIR__) . 'updater.php';
        $plugin_data = get_plugin_data($plugin_file);
        $current_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '';
        $repo = 'Lkilasonia/updater';

        $github_response = self::$github_api->get_latest_release($repo);
        if ($github_response && version_compare($current_version, $github_response['tag_name'], '<')) {
            add_action('admin_notices', [__CLASS__, 'show_update_notification']);
        }
    }

    public static function show_update_notification() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('There is a new version of the Updater plugin available. Please update to the latest version.', 'text-domain'); ?></p>
        </div>
        <?php
    }

    public static function filter_plugin_updates($transient) {
        if (empty($transient->checked)) {
            error_log('No plugins checked for updates.');
            return $transient;
        }

        $plugin_file = plugin_dir_path(__DIR__) . 'updater.php';
        $plugin_data = get_plugin_data($plugin_file);
        $current_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '';
        error_log('Current Version: ' . $current_version);

        $repo = 'Lkilasonia/updater';
        $github_response = self::$github_api->get_latest_release($repo);

        if ($github_response && version_compare($current_version, $github_response['tag_name'], '<')) {
            $plugin_slug = plugin_basename($plugin_file);

            $transient->response[$plugin_slug] = (object) array(
                'slug'        => $plugin_slug,
                'new_version' => $github_response['tag_name'],
                'url'         => $plugin_data['PluginURI'],
                'package'     => $github_response['zipball_url'],
            );

            error_log('Transient response updated: ' . print_r($transient->response[$plugin_slug], true));
        }

        return $transient;
    }

    public static function plugin_information($res, $action, $args) {
        $plugin_file = plugin_dir_path(__DIR__) . 'updater.php';
        $plugin_slug = plugin_basename($plugin_file);

        if ('plugin_information' !== $action || $plugin_slug !== $args->slug) {
            return false;
        }

        $repo = 'Lkilasonia/updater';
        $github_response = self::$github_api->get_latest_release($repo);

        if (!$github_response) {
            return $res;
        }

        $plugin_data = get_plugin_data($plugin_file);

        $res = (object) array(
            'name'              => $plugin_data['Name'],
            'slug'              => $plugin_slug,
            'version'           => $github_response['tag_name'],
            'author'            => $plugin_data['Author'],
            'author_profile'    => $plugin_data['AuthorURI'],
            'homepage'          => $plugin_data['PluginURI'],
            'short_description' => $plugin_data['Description'],
            'sections'          => array(
                'description' => $plugin_data['Description'],
                'changelog'   => isset($github_response['body']) ? $github_response['body'] : '',
            ),
            'download_link'     => $github_response['zipball_url'],
            'last_updated'      => $github_response['published_at'],
        );

        return $res;
    }
}
