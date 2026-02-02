<?php
/**
 * Bunny Stream API Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Playlist_Bunny_API {

    private $library_id;
    private $api_key;
    private $api_base = 'https://video.bunnycdn.com/library/';

    public function __construct() {
        $this->library_id = get_option('jurible_playlist_library_id', '35843');
        $this->api_key = get_option('jurible_playlist_api_key', '');
    }

    private function make_request($endpoint, $method = 'GET') {
        $url = $this->api_base . $this->library_id . '/' . $endpoint;

        $args = array(
            'method' => $method,
            'headers' => array(
                'AccessKey' => $this->api_key,
                'Accept' => 'application/json'
            ),
            'timeout' => 30
        );

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('error' => 'Invalid JSON response');
        }

        return $data;
    }

    public function get_collection_videos($collection_id) {
        $cache_key = 'jurible_collection_' . $collection_id;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $videos = array();
        $page = 1;
        $per_page = 100;

        do {
            $endpoint = "videos?collection={$collection_id}&page={$page}&itemsPerPage={$per_page}";
            $response = $this->make_request($endpoint);

            if (isset($response['error'])) {
                return $response;
            }

            if (isset($response['items']) && is_array($response['items'])) {
                $videos = array_merge($videos, $response['items']);
            }

            $total_items = isset($response['totalItems']) ? $response['totalItems'] : 0;
            $page++;

        } while (count($videos) < $total_items);

        // Sort videos by title (which should start with numbers like 01-, 02-, etc.)
        usort($videos, function($a, $b) {
            return strnatcmp($a['title'], $b['title']);
        });

        // Cache for 5 minutes
        set_transient($cache_key, $videos, 5 * MINUTE_IN_SECONDS);

        return $videos;
    }

    public function get_video($video_id) {
        $endpoint = "videos/{$video_id}";
        return $this->make_request($endpoint);
    }

    public function get_collection($collection_id) {
        $endpoint = "collections/{$collection_id}";
        return $this->make_request($endpoint);
    }

    public function get_library_info() {
        $cache_key = 'jurible_library_' . $this->library_id;
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        // API endpoint for library info (no sub-path)
        $url = 'https://video.bunnycdn.com/library/' . $this->library_id;

        $response = wp_remote_get($url, array(
            'headers' => array(
                'AccessKey' => $this->api_key,
                'Accept' => 'application/json'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return array('error' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array('error' => 'Invalid JSON response');
        }

        // Cache for 1 hour
        set_transient($cache_key, $data, HOUR_IN_SECONDS);

        return $data;
    }

    public function get_cdn_hostname() {
        // Check if manually configured
        $manual_url = get_option('jurible_playlist_thumbnail_url', '');
        if (!empty($manual_url)) {
            return rtrim($manual_url, '/');
        }

        // Try to get from library info
        $library = $this->get_library_info();

        if (isset($library['PullZone']) && !empty($library['PullZone'])) {
            return 'https://' . $library['PullZone'] . '.b-cdn.net';
        }

        // Fallback: try common patterns
        return '';
    }

    public function get_thumbnail_url($video_guid, $thumbnail_filename = 'thumbnail.jpg') {
        $hostname = $this->get_cdn_hostname();

        if (empty($hostname)) {
            return '';
        }

        $path = '/' . $video_guid . '/' . $thumbnail_filename;
        $base_url = $hostname . $path;

        // Check if token signing is required
        $token_key = get_option('jurible_playlist_token_key', '');

        if (!empty($token_key)) {
            return $this->generate_signed_url($base_url, $path, $token_key);
        }

        return $base_url;
    }

    public function generate_signed_url($base_url, $path, $security_key, $expiration_seconds = 3600) {
        $expires = time() + $expiration_seconds;

        // Generate token hash
        $hashable_base = $security_key . $path . $expires;
        $token = hash('sha256', $hashable_base, true); // Get raw binary
        $token = base64_encode($token);

        // URL-safe base64
        $token = strtr($token, '+/', '-_');
        $token = rtrim($token, '=');

        return $base_url . '?token=' . $token . '&expires=' . $expires;
    }

    public static function clean_video_title($title) {
        // Remove file extension
        $title = preg_replace('/\.(mp4|webm|mov|avi|mkv)$/i', '', $title);

        // Remove leading numbers and separators (e.g., "01-", "02_", "1. ", etc.)
        $title = preg_replace('/^[\d]+[\s\-_.]+/', '', $title);

        // Replace underscores and hyphens with spaces
        $title = str_replace(array('_', '-'), ' ', $title);

        // Clean up multiple spaces
        $title = preg_replace('/\s+/', ' ', $title);

        // Trim and capitalize first letter
        $title = trim($title);

        return $title;
    }

    public static function format_duration($seconds) {
        if ($seconds < 0) {
            return '0:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%d:%02d', $minutes, $secs);
    }
}
