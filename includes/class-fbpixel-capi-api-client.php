<?php
/**
 * W3 Facebook Pixel CAPI API Client
 * 
 * Handles communication with Facebook Conversions API
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FBPixel_CAPI_API_Client {
    
    /**
     * Facebook Graph API base URL
     */
    const API_BASE_URL = 'https://graph.facebook.com';
    
    /**
     * API version
     */
    const API_VERSION = 'v18.0';
    
    /**
     * Plugin settings
     */
    private $settings;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = get_option('fbpixel_capi_settings', array());
    }
    
    /**
     * Send events to Facebook Conversions API
     * 
     * @param array $events Array of event data
     * @return array|WP_Error Response data or error
     */
    public function send_events($events) {
        // Validate settings
        if (empty($this->settings['pixel_id']) || empty($this->settings['access_token'])) {
            return new WP_Error('missing_credentials', __('Pixel ID or Access Token is missing', 'w3-facebook-pixel-capi'));
        }
        
        // Prepare API endpoint
        $endpoint = sprintf(
            '%s/%s/%s/events',
            self::API_BASE_URL,
            self::API_VERSION,
            $this->settings['pixel_id']
        );
        
        // Prepare request body
        $body = array(
            'data' => $events
        );
        
        // Add test event code if provided
        if (!empty($this->settings['test_event_code'])) {
            $body['test_event_code'] = $this->settings['test_event_code'];
        }
        
        // Prepare request headers
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->settings['access_token']
        );
        
        // Prepare request arguments
        $args = array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => 30,
            'blocking' => false, // Make it asynchronous for better performance
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
        );
        
        // Log the request if debug mode is enabled
        if (!empty($this->settings['debug_mode'])) {
            $this->log_request($endpoint, $body, $args);
        }
        
        // Send the request
        $response = wp_remote_post($endpoint, $args);
        
        // Handle response
        if (is_wp_error($response)) {
            $this->log_error('API Request Failed', $response->get_error_message(), $events);
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Log the response if debug mode is enabled
        if (!empty($this->settings['debug_mode'])) {
            $this->log_response($response_code, $response_body, $events);
        }
        
        // Check for API errors
        if ($response_code >= 400) {
            $error_message = $this->parse_error_response($response_body);
            $this->log_error('API Error', $error_message, $events);
            return new WP_Error('api_error', $error_message);
        }
        
        return array(
            'status_code' => $response_code,
            'body' => json_decode($response_body, true)
        );
    }
    
    /**
     * Send a single event to Facebook Conversions API
     * 
     * @param array $event_data Single event data
     * @return array|WP_Error Response data or error
     */
    public function send_event($event_data) {
        return $this->send_events(array($event_data));
    }
    
    /**
     * Parse error response from Facebook API
     * 
     * @param string $response_body Response body
     * @return string Error message
     */
    private function parse_error_response($response_body) {
        $decoded = json_decode($response_body, true);
        
        if (isset($decoded['error']['message'])) {
            return $decoded['error']['message'];
        }
        
        if (isset($decoded['error']['error_user_msg'])) {
            return $decoded['error']['error_user_msg'];
        }
        
        return __('Unknown API error occurred', 'w3-facebook-pixel-capi');
    }
    
    /**
     * Log API request for debugging
     * 
     * @param string $endpoint API endpoint
     * @param array $body Request body
     * @param array $args Request arguments
     */
    private function log_request($endpoint, $body, $args) {
        $log_data = array(
            'type' => 'request',
            'endpoint' => $endpoint,
            'body' => $body,
            'headers' => $args['headers'],
            'timestamp' => current_time('mysql')
        );
        
        $this->write_log('API Request', $log_data);
    }
    
    /**
     * Log API response for debugging
     * 
     * @param int $status_code Response status code
     * @param string $response_body Response body
     * @param array $events Original events data
     */
    private function log_response($status_code, $response_body, $events) {
        $log_data = array(
            'type' => 'response',
            'status_code' => $status_code,
            'response_body' => $response_body,
            'events_count' => count($events),
            'timestamp' => current_time('mysql')
        );
        
        $this->write_log('API Response', $log_data);
    }
    
    /**
     * Log error for debugging
     * 
     * @param string $error_type Error type
     * @param string $error_message Error message
     * @param array $events Events data
     */
    private function log_error($error_type, $error_message, $events) {
        $log_data = array(
            'type' => 'error',
            'error_type' => $error_type,
            'error_message' => $error_message,
            'events_count' => count($events),
            'timestamp' => current_time('mysql')
        );
        
        $this->write_log('API Error', $log_data);
    }
    
    /**
     * Write log to database
     * 
     * @param string $event_name Event name
     * @param array $log_data Log data
     */
    private function write_log($event_name, $log_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fbpixel_capi_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'event_name' => $event_name,
                'event_data' => wp_json_encode($log_data),
                'status' => isset($log_data['type']) ? $log_data['type'] : 'info',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Test API connection
     * 
     * @return array|WP_Error Test result
     */
    public function test_connection() {
        // Create a test PageView event
        $test_event = array(
            'event_name' => 'PageView',
            'event_time' => time(),
            'action_source' => 'website',
            'event_source_url' => home_url(),
            'user_data' => array(
                'client_ip_address' => FBPixel_CAPI_Helpers::get_client_ip(),
                'client_user_agent' => FBPixel_CAPI_Helpers::get_user_agent()
            )
        );
        
        // Add test event code for testing
        $original_test_code = $this->settings['test_event_code'];
        if (empty($original_test_code)) {
            $this->settings['test_event_code'] = 'TEST12345';
        }
        
        $result = $this->send_event($test_event);
        
        // Restore original test code
        $this->settings['test_event_code'] = $original_test_code;
        
        return $result;
    }
}

