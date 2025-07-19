<?php
/**
 * W3 Facebook Pixel CAPI Helpers
 * 
 * Utility functions and helpers
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FBPixel_CAPI_Helpers {
    
    /**
     * Get client IP address
     * 
     * @return string Client IP address
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to REMOTE_ADDR even if it's private/reserved
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    
    /**
     * Get user agent
     * 
     * @return string User agent
     */
    public static function get_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
    
    /**
     * Get current URL
     * 
     * @return string Current URL
     */
    public static function get_current_url() {
        if (is_admin()) {
            return admin_url();
        }
        
        global $wp;
        return home_url(add_query_arg(array(), $wp->request));
    }
    
    /**
     * Sanitize and validate email
     * 
     * @param string $email Email address
     * @return string|false Sanitized email or false if invalid
     */
    public static function sanitize_email($email) {
        $email = sanitize_email($email);
        return is_email($email) ? $email : false;
    }
    
    /**
     * Sanitize phone number
     * 
     * @param string $phone Phone number
     * @return string Sanitized phone number (digits only)
     */
    public static function sanitize_phone($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }
    
    /**
     * Hash data using SHA256
     * 
     * @param string $data Data to hash
     * @return string Hashed data
     */
    public static function hash_data($data) {
        return hash('sha256', strtolower(trim($data)));
    }
    
    /**
     * Validate Pixel ID format
     * 
     * @param string $pixel_id Pixel ID
     * @return bool Whether Pixel ID is valid
     */
    public static function validate_pixel_id($pixel_id) {
        return preg_match('/^[0-9]{15,16}$/', $pixel_id);
    }
    
    /**
     * Validate access token format
     * 
     * @param string $access_token Access token
     * @return bool Whether access token is valid
     */
    public static function validate_access_token($access_token) {
        // Basic validation - access tokens are typically long alphanumeric strings
        return strlen($access_token) > 50 && preg_match('/^[a-zA-Z0-9_-]+$/', $access_token);
    }
    
    /**
     * Get WordPress user data
     * 
     * @param int $user_id User ID (optional, defaults to current user)
     * @return array User data
     */
    public static function get_wp_user_data($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return array();
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return array();
        }
        
        $user_data = array();
        
        if (!empty($user->user_email)) {
            $user_data['em'] = self::hash_data($user->user_email);
        }
        
        if (!empty($user->first_name)) {
            $user_data['fn'] = self::hash_data($user->first_name);
        }
        
        if (!empty($user->last_name)) {
            $user_data['ln'] = self::hash_data($user->last_name);
        }
        
        return $user_data;
    }
    
    /**
     * Get WooCommerce customer data
     * 
     * @param WC_Customer $customer Customer object
     * @return array Customer data
     */
    public static function get_wc_customer_data($customer) {
        if (!$customer || !class_exists('WooCommerce')) {
            return array();
        }
        
        $customer_data = array();
        
        $email = $customer->get_email();
        if (!empty($email)) {
            $customer_data['em'] = self::hash_data($email);
        }
        
        $first_name = $customer->get_first_name();
        if (!empty($first_name)) {
            $customer_data['fn'] = self::hash_data($first_name);
        }
        
        $last_name = $customer->get_last_name();
        if (!empty($last_name)) {
            $customer_data['ln'] = self::hash_data($last_name);
        }
        
        $phone = $customer->get_billing_phone();
        if (!empty($phone)) {
            $customer_data['ph'] = self::hash_data(self::sanitize_phone($phone));
        }
        
        $city = $customer->get_billing_city();
        if (!empty($city)) {
            $customer_data['ct'] = self::hash_data($city);
        }
        
        $state = $customer->get_billing_state();
        if (!empty($state)) {
            $customer_data['st'] = self::hash_data($state);
        }
        
        $postcode = $customer->get_billing_postcode();
        if (!empty($postcode)) {
            $customer_data['zp'] = self::hash_data($postcode);
        }
        
        $country = $customer->get_billing_country();
        if (!empty($country)) {
            $customer_data['country'] = self::hash_data($country);
        }
        
        return $customer_data;
    }
    
    /**
     * Check if WooCommerce is active
     * 
     * @return bool Whether WooCommerce is active
     */
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }
    
    /**
     * Get currency code
     * 
     * @return string Currency code
     */
    public static function get_currency() {
        if (self::is_woocommerce_active()) {
            return get_woocommerce_currency();
        }
        
        // Fallback to USD
        return 'USD';
    }
    
    /**
     * Format price for Facebook
     * 
     * @param float $price Price
     * @return float Formatted price
     */
    public static function format_price($price) {
        return round((float) $price, 2);
    }
    
    /**
     * Get page type
     * 
     * @return string Page type
     */
    public static function get_page_type() {
        if (is_front_page()) {
            return 'home';
        } elseif (is_shop() || is_product_category() || is_product_tag()) {
            return 'category';
        } elseif (is_product()) {
            return 'product';
        } elseif (is_cart()) {
            return 'cart';
        } elseif (is_checkout()) {
            return 'checkout';
        } elseif (is_account_page()) {
            return 'account';
        } elseif (is_search()) {
            return 'search';
        } elseif (is_404()) {
            return '404';
        } else {
            return 'other';
        }
    }
    
    /**
     * Log message to WordPress debug log
     * 
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     */
    public static function log($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf('[Facebook Pixel CAPI] [%s] %s', strtoupper($level), $message));
        }
    }
    
    /**
     * Check if current request is from a bot/crawler
     * 
     * @return bool Whether request is from a bot
     */
    public static function is_bot_request() {
        $user_agent = self::get_user_agent();
        
        $bot_patterns = array(
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget',
            'googlebot', 'bingbot', 'slurp', 'duckduckbot',
            'baiduspider', 'yandexbot', 'facebookexternalhit'
        );
        
        foreach ($bot_patterns as $pattern) {
            if (stripos($user_agent, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get Facebook click ID from URL or cookie
     * 
     * @return string|null Facebook click ID
     */
    public static function get_facebook_click_id() {
        // Check URL parameter first
        if (!empty($_GET['fbclid'])) {
            return sanitize_text_field($_GET['fbclid']);
        }
        
        // Check cookie
        if (!empty($_COOKIE['_fbc'])) {
            return sanitize_text_field($_COOKIE['_fbc']);
        }
        
        return null;
    }
    
    /**
     * Get Facebook browser ID from cookie
     * 
     * @return string|null Facebook browser ID
     */
    public static function get_facebook_browser_id() {
        if (!empty($_COOKIE['_fbp'])) {
            return sanitize_text_field($_COOKIE['_fbp']);
        }
        
        return null;
    }
}

