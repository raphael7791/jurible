<?php
/**
 * REST API Endpoints for progress tracking
 */

if (!defined('ABSPATH')) {
    exit;
}

class Jurible_Playlist_Rest_API {

    public static function register_routes() {
        // Progress tracking endpoints
        register_rest_route('jurible-playlist/v1', '/progress', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'update_progress'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
            'args' => array(
                'video_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'collection_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'status' => array(
                    'required' => false,
                    'type' => 'string',
                    'enum' => array('started', 'completed'),
                    'default' => 'started'
                ),
                'watch_time' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0
                )
            )
        ));

        register_rest_route('jurible-playlist/v1', '/progress/(?P<collection_id>[a-zA-Z0-9-]+)', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_collection_progress'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
            'args' => array(
                'collection_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));

        // Bunny collections endpoint for Gutenberg block
        register_rest_route('jurible/v1', '/bunny/collections', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_bunny_collections'),
            'permission_callback' => array(__CLASS__, 'check_editor_permission'),
            'args' => array(
                'filter' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => ''
                )
            )
        ));

        // Bunny collection videos endpoint
        register_rest_route('jurible/v1', '/bunny/collections/(?P<collection_id>[a-zA-Z0-9-]+)/videos', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_collection_videos'),
            'permission_callback' => '__return_true',
            'args' => array(
                'collection_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
    }

    public static function check_permission() {
        return is_user_logged_in();
    }

    public static function check_editor_permission() {
        return current_user_can('edit_posts');
    }

    public static function update_progress($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $video_id = $request->get_param('video_id');
        $collection_id = $request->get_param('collection_id');
        $status = $request->get_param('status');
        $watch_time = absint($request->get_param('watch_time'));

        $table_name = $wpdb->prefix . 'jurible_video_progress';

        // Check if record exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND video_id = %s",
            $user_id,
            $video_id
        ));

        if ($existing) {
            // Don't downgrade from completed to started
            if ($existing->status === 'completed' && $status === 'started') {
                return rest_ensure_response(array(
                    'success' => true,
                    'message' => 'Progress already completed'
                ));
            }

            $update_data = array(
                'watch_time' => max($existing->watch_time, $watch_time),
                'updated_at' => current_time('mysql')
            );

            if ($status === 'completed' && $existing->status !== 'completed') {
                $update_data['status'] = 'completed';
                $update_data['completed_at'] = current_time('mysql');
            }

            $wpdb->update(
                $table_name,
                $update_data,
                array('id' => $existing->id),
                array('%d', '%s', '%s', '%s'),
                array('%d')
            );
        } else {
            $insert_data = array(
                'user_id' => $user_id,
                'video_id' => $video_id,
                'collection_id' => $collection_id,
                'status' => $status,
                'watch_time' => $watch_time,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );

            if ($status === 'completed') {
                $insert_data['completed_at'] = current_time('mysql');
            }

            $wpdb->insert($table_name, $insert_data);
        }

        return rest_ensure_response(array(
            'success' => true,
            'status' => $status
        ));
    }

    public static function get_collection_progress($request) {
        global $wpdb;

        $user_id = get_current_user_id();
        $collection_id = $request->get_param('collection_id');

        $table_name = $wpdb->prefix . 'jurible_video_progress';

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT video_id, status, watch_time, completed_at
             FROM $table_name
             WHERE user_id = %d AND collection_id = %s",
            $user_id,
            $collection_id
        ), ARRAY_A);

        $progress = array();
        foreach ($results as $row) {
            $progress[$row['video_id']] = array(
                'status' => $row['status'],
                'watch_time' => (int) $row['watch_time'],
                'completed' => $row['status'] === 'completed'
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'progress' => $progress
        ));
    }

    /**
     * Get Bunny Stream collections
     * Optionally filter by name containing a specific string
     */
    public static function get_bunny_collections($request) {
        $filter = $request->get_param('filter');

        $library_id = get_option('jurible_playlist_library_id', '35843');
        $api_key = get_option('jurible_playlist_api_key', '');

        if (empty($api_key)) {
            return new WP_Error('no_api_key', 'Bunny API key not configured', array('status' => 500));
        }

        // Fetch collections from Bunny API
        $url = 'https://video.bunnycdn.com/library/' . $library_id . '/collections?page=1&itemsPerPage=100';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'AccessKey' => $api_key,
                'Accept' => 'application/json'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return new WP_Error('api_error', $response->get_error_message(), array('status' => 500));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON response from Bunny', array('status' => 500));
        }

        $collections = isset($data['items']) ? $data['items'] : array();

        // Filter collections if filter parameter is provided
        if (!empty($filter)) {
            $collections = array_filter($collections, function($collection) use ($filter) {
                return stripos($collection['name'], $filter) !== false;
            });
            $collections = array_values($collections); // Re-index array
        }

        // Format response
        $formatted = array();
        foreach ($collections as $collection) {
            $formatted[] = array(
                'id' => $collection['guid'],
                'name' => $collection['name'],
                'videoCount' => isset($collection['videoCount']) ? $collection['videoCount'] : 0
            );
        }

        // Sort by name
        usort($formatted, function($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        return rest_ensure_response(array(
            'success' => true,
            'collections' => $formatted
        ));
    }

    /**
     * Get videos from a specific collection
     */
    public static function get_collection_videos($request) {
        $collection_id = $request->get_param('collection_id');

        $bunny_api = new Jurible_Playlist_Bunny_API();
        $videos = $bunny_api->get_collection_videos($collection_id);

        if (isset($videos['error'])) {
            return new WP_Error('api_error', $videos['error'], array('status' => 500));
        }

        // Format videos with signed thumbnail URLs
        $formatted = array();
        foreach ($videos as $video) {
            $thumb_file = !empty($video['thumbnailFileName']) ? $video['thumbnailFileName'] : 'thumbnail.jpg';
            $formatted[] = array(
                'id' => $video['guid'],
                'title' => Jurible_Playlist_Bunny_API::clean_video_title($video['title']),
                'rawTitle' => $video['title'],
                'duration' => isset($video['length']) ? $video['length'] : 0,
                'durationFormatted' => isset($video['length']) ? Jurible_Playlist_Bunny_API::format_duration($video['length']) : '',
                'thumbnail' => $bunny_api->get_thumbnail_url($video['guid'], $thumb_file)
            );
        }

        return rest_ensure_response(array(
            'success' => true,
            'videos' => $formatted
        ));
    }
}
