<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GitHubAPI {
    private $access_token;

    public function __construct($access_token = '') {
        $this->access_token = $access_token;
    }

    public function get_latest_release($repo) {
        $url = "https://api.github.com/repos/$repo/releases/latest";
        $args = array(
            'headers' => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress Plugin Updater',
            ),
        );

        // Add Authorization header if access token is available
        if (!empty($this->access_token)) {
            $args['headers']['Authorization'] = 'token ' . $this->access_token;
        }

        // Debug: Log the headers and URL
        error_log('GitHub API Request URL: ' . $url);
        error_log('GitHub API Request Headers: ' . print_r($args['headers'], true));

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            error_log('GitHub API request failed: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Debug the response
        error_log('GitHub API response: ' . print_r($data, true));

        if (isset($data['tag_name'])) {
            // Normalize the version by removing the "v" prefix
            $data['tag_name'] = ltrim($data['tag_name'], 'v');
            return $data;
        }

        error_log('GitHub API response does not contain tag_name: ' . $body);
        return false;
    }
}
