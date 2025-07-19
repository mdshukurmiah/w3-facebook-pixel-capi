<?php
/**
 * W3 Pixel CAPI Admin
 * 
 * Handles admin interface and settings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class FBPixel_CAPI_Admin {
    
    /**
     * Settings option name
     */
    const OPTION_NAME = 'fbpixel_capi_settings';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Admin hooks are handled in main plugin file
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting(
            'fbpixel_capi_settings_group',
            self::OPTION_NAME,
            array($this, 'sanitize_settings')
        );
        
        // General settings section
        add_settings_section(
            'fbpixel_capi_general_section',
            __('General Settings', 'w3-pixel-capi'),
            array($this, 'general_section_callback'),
            'fbpixel-capi-settings'
        );
        
        // Pixel ID field
        add_settings_field(
            'pixel_id',
            __('Facebook Pixel ID', 'w3-pixel-capi'),
            array($this, 'pixel_id_callback'),
            'fbpixel-capi-settings',
            'fbpixel_capi_general_section'
        );
        
        // Access Token field
        add_settings_field(
            'access_token',
            __('Conversions API Access Token', 'w3-pixel-capi'),
            array($this, 'access_token_callback'),
            'fbpixel-capi-settings',
            'fbpixel_capi_general_section'
        );
        
        // Test Event Code field
        add_settings_field(
            'testesc_html_event_code',
            __('Test Event Code', 'w3-pixel-capi'),
            array($this, 'testesc_html_event_code_callback'),
            'fbpixel-capi-settings',
            'fbpixel_capi_general_section'
        );
        
        // Events settings section
        add_settings_section(
            'fbpixel_capiesc_html_events_section',
            __('Event Settings', 'w3-pixel-capi'),
            array($this, 'events_section_callback'),
            'fbpixel-capi-settings'
        );
        
        // Enabled Events field
        add_settings_field(
            'enabledesc_html_events',
            __('Enabled Events', 'w3-pixel-capi'),
            array($this, 'enabledesc_html_events_callback'),
            'fbpixel-capi-settings',
            'fbpixel_capiesc_html_events_section'
        );
        
        // Debug settings section
        add_settings_section(
            'fbpixel_capi_debug_section',
            __('Debug Settings', 'w3-pixel-capi'),
            array($this, 'debug_section_callback'),
            'fbpixel-capi-settings'
        );
        
        // Debug Mode field
        add_settings_field(
            'debug_mode',
            __('Debug Mode', 'w3-pixel-capi'),
            array($this, 'debug_mode_callback'),
            'fbpixel-capi-settings',
            'fbpixel_capi_debug_section'
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle form submissions
        if (isset($_POST['test_connection'])) {
            $this->handle_test_connection();
        }
        
        $settings = get_option(self::OPTION_NAME, array());
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settingsesc_html_errors(); ?>
            
            <div class="fbpixel-capi-admin-container">
                <div class="fbpixel-capi-main-content">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('fbpixel_capi_settings_group');
                        do_settings_sections('fbpixel-capi-settings');
                        submit_button();
                        ?>
                    </form>
                    
                    <hr>
                    
                    <h2><?php esc_html_e('Test Connection', 'w3-pixel-capi'); ?></h2>
                    <p><?php esc_html_e('Test your Conversions API connection to ensure everything is working correctly.', 'w3-pixel-capi'); ?></p>
                    
                    <form method="post">
                        <?php wp_nonce_field('fbpixel_capi_test_connection', 'fbpixel_capi_test_nonce'); ?>
                        <input type="submit" name="test_connection" class="button button-secondary" value="<?php esc_html_e('Test Connection', 'w3-pixel-capi'); ?>">
                    </form>
                </div>
                
                <div class="fbpixel-capi-sidebar">
                    <div class="fbpixel-capi-info-box">
                        <h3><?php esc_html_e('Getting Started', 'w3-pixel-capi'); ?></h3>
                        <ol>
                            <li><?php esc_html_e('Create a Facebook Business Manager account', 'w3-pixel-capi'); ?></li>
                            <li><?php esc_html_e('Set up a Facebook Pixel in Events Manager', 'w3-pixel-capi'); ?></li>
                            <li><?php esc_html_e('Generate a Conversions API access token', 'w3-pixel-capi'); ?></li>
                            <li><?php esc_html_e('Enter your Pixel ID and Access Token above', 'w3-pixel-capi'); ?></li>
                            <li><?php esc_html_e('Configure which events to track', 'w3-pixel-capi'); ?></li>
                            <li><?php esc_html_e('Test your connection', 'w3-pixel-capi'); ?></li>
                        </ol>
                    </div>
                    
                    <div class="fbpixel-capi-info-box">
                        <h3><?php esc_html_e('Documentation', 'w3-pixel-capi'); ?></h3>
                        <ul>
                            <li><a href="https://developers.facebook.com/docs/marketing-api/conversions-api/" target="_blank"><?php esc_html_e('Facebook Conversions API Documentation', 'w3-pixel-capi'); ?></a></li>
                            <li><a href="https://www.facebook.com/business/help/AboutConversionsAPI" target="_blank"><?php esc_html_e('About Conversions API', 'w3-pixel-capi'); ?></a></li>
                            <li><a href="https://developers.facebook.com/docs/marketing-api/conversions-api/get-started" target="_blank"><?php esc_html_e('Getting Started Guide', 'w3-pixel-capi'); ?></a></li>
                        </ul>
                    </div>
                    
                    <?php if (!empty($settings['debug_mode'])): ?>
                    <div class="fbpixel-capi-info-box">
                        <h3><?php esc_html_e('Debug Information', 'w3-pixel-capi'); ?></h3>
                        <p><?php esc_html_e('Debug mode is enabled. Check your WordPress debug log for detailed information about API requests and responses.', 'w3-pixel-capi'); ?></p>
                        <p><strong><?php esc_html_e('WooCommerce Status:', 'w3-pixel-capi'); ?></strong> 
                            <?php echo classesc_html_exists('WooCommerce') ? __('Active', 'w3-pixel-capi') : __('Not Active', 'w3-pixel-capi'); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * General section callback
     */
    public function general_section_callback() {
        echo '<p>' . __('Configure your Facebook Pixel and Conversions API credentials.', 'w3-pixel-capi') . '</p>';
    }
    
    /**
     * Events section callback
     */
    public function events_section_callback() {
        echo '<p>' . __('Choose which events to track via server-side tracking.', 'w3-pixel-capi') . '</p>';
    }
    
    /**
     * Debug section callback
     */
    public function debug_section_callback() {
        echo '<p>' . __('Debug settings for troubleshooting and development.', 'w3-pixel-capi') . '</p>';
    }
    
    /**
     * Pixel ID field callback
     */
    public function pixel_id_callback() {
        $settings = get_option(self::OPTION_NAME, array());
        $value = isset($settings['pixel_id']) ? $settings['pixel_id'] : '';
        ?>
        <input type="text" id="pixel_id" name="<?php echo self::OPTION_NAME; ?>[pixel_id]" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="123456789012345">
        <p class="description"><?php esc_html_e('Your Facebook Pixel ID (15-16 digits). You can find this in your Facebook Events Manager.', 'w3-pixel-capi'); ?></p>
        <?php
    }
    
    /**
     * Access Token field callback
     */
    public function access_token_callback() {
        $settings = get_option(self::OPTION_NAME, array());
        $value = isset($settings['access_token']) ? $settings['access_token'] : '';
        ?>
        <input type="password" id="access_token" name="<?php echo self::OPTION_NAME; ?>[access_token]" value="<?php echo esc_attr($value); ?>" class="large-text" placeholder="EAAxxxxxxxxxxxxx">
        <p class="description"><?php esc_html_e('Your Conversions API access token. Generate this in Facebook Events Manager under Conversions API settings.', 'w3-pixel-capi'); ?></p>
        <?php
    }
    
    /**
     * Test Event Code field callback
     */
    public function testesc_html_event_code_callback() {
        $settings = get_option(self::OPTION_NAME, array());
        $value = isset($settings['testesc_html_event_code']) ? $settings['testesc_html_event_code'] : '';
        ?>
        <input type="text" id="testesc_html_event_code" name="<?php echo self::OPTION_NAME; ?>[testesc_html_event_code]" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="TEST12345">
        <p class="description"><?php esc_html_e('Optional test event code for testing your integration. You can find this in Facebook Events Manager.', 'w3-pixel-capi'); ?></p>
        <?php
    }
    
    /**
     * Enabled Events field callback
     */
    public function enabledesc_html_events_callback() {
        $settings = get_option(self::OPTION_NAME, array());
        $enabledesc_html_events = isset($settings['enabledesc_html_events']) ? $settings['enabledesc_html_events'] : array();
        
        $availableesc_html_events = array(
            'PageView' => __('Page View', 'w3-pixel-capi'),
            'AddToCart' => __('Add to Cart', 'w3-pixel-capi'),
            'InitiateCheckout' => __('Initiate Checkout', 'w3-pixel-capi'),
            'Purchase' => __('Purchase', 'w3-pixel-capi'),
            'ViewContent' => __('View Content', 'w3-pixel-capi'),
            'Search' => __('Search', 'w3-pixel-capi'),
            'Lead' => __('Lead', 'w3-pixel-capi'),
            'CompleteRegistration' => __('Complete Registration', 'w3-pixel-capi')
        );
        
        echo '<fieldset>';
        foreach ($availableesc_html_events as $event_key => $event_label) {
            $checked = !empty($enabledesc_html_events[$event_key]) ? 'checked' : '';
            $disabled = '';
            $description = '';
            
            // Add descriptions and disable unsupported events
            switch ($event_key) {
                case 'PageView':
                    $description = __('Track when users view pages on your website', 'w3-pixel-capi');
                    break;
                case 'AddToCart':
                case 'InitiateCheckout':
                case 'Purchase':
                    if (!classesc_html_exists('WooCommerce')) {
                        $disabled = 'disabled';
                        $description = __('Requires WooCommerce', 'w3-pixel-capi');
                    } else {
                        $descriptions = array(
                            'AddToCart' => __('Track when users add products to cart', 'w3-pixel-capi'),
                            'InitiateCheckout' => __('Track when users start the checkout process', 'w3-pixel-capi'),
                            'Purchase' => __('Track completed purchases', 'w3-pixel-capi')
                        );
                        $description = $descriptions[$event_key];
                    }
                    break;
                case 'ViewContent':
                    if (!classesc_html_exists('WooCommerce')) {
                        $disabled = 'disabled';
                        $description = __('Requires WooCommerce', 'w3-pixel-capi');
                    } else {
                        $description = __('Track when users view product pages', 'w3-pixel-capi');
                    }
                    break;
                default:
                    $disabled = 'disabled';
                    $description = __('Coming in future version', 'w3-pixel-capi');
                    break;
            }
            
            printf(
                '<label><input type="checkbox" name="%s[enabledesc_html_events][%s]" value="1" %s %s> %s</label><br>',
                self::OPTION_NAME,
                $event_key,
                $checked,
                $disabled,
                $event_label
            );
            
            if ($description) {
                echo '<p class="description" style="margin-left: 25px; margin-top: 0;">' . $description . '</p>';
            }
        }
        echo '</fieldset>';
    }
    
    /**
     * Debug Mode field callback
     */
    public function debug_mode_callback() {
        $settings = get_option(self::OPTION_NAME, array());
        $value = !empty($settings['debug_mode']);
        ?>
        <label>
            <input type="checkbox" name="<?php echo self::OPTION_NAME; ?>[debug_mode]" value="1" <?php checked($value); ?>>
            <?php esc_html_e('Enable debug mode', 'w3-pixel-capi'); ?>
        </label>
        <p class="description"><?php esc_html_e('When enabled, detailed logs will be written to the WordPress debug log and database for troubleshooting.', 'w3-pixel-capi'); ?></p>
        <?php
    }
    
    /**
     * Sanitize settings
     * 
     * @param array $input Input settings
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Sanitize Pixel ID
        if (!empty($input['pixel_id'])) {
            $pixel_id = sanitize_text_field($input['pixel_id']);
            if (FBPixel_CAPI_Helpers::validate_pixel_id($pixel_id)) {
                $sanitized['pixel_id'] = $pixel_id;
            } else {
                add_settingsesc_html_error(
                    self::OPTION_NAME,
                    'invalid_pixel_id',
                    __('Invalid Pixel ID format. Please enter a valid 15-16 digit Pixel ID.', 'w3-pixel-capi')
                );
            }
        }
        
        // Sanitize Access Token
        if (!empty($input['access_token'])) {
            $access_token = sanitize_text_field($input['access_token']);
            if (FBPixel_CAPI_Helpers::validate_access_token($access_token)) {
                $sanitized['access_token'] = $access_token;
            } else {
                add_settingsesc_html_error(
                    self::OPTION_NAME,
                    'invalid_access_token',
                    __('Invalid Access Token format. Please enter a valid Conversions API access token.', 'w3-pixel-capi')
                );
            }
        }
        
        // Sanitize Test Event Code
        if (!empty($input['testesc_html_event_code'])) {
            $sanitized['testesc_html_event_code'] = sanitize_text_field($input['testesc_html_event_code']);
        }
        
        // Sanitize Enabled Events
        if (!empty($input['enabledesc_html_events']) && is_array($input['enabledesc_html_events'])) {
            $sanitized['enabledesc_html_events'] = array();
            $allowedesc_html_events = array('PageView', 'AddToCart', 'InitiateCheckout', 'Purchase', 'ViewContent', 'Search', 'Lead', 'CompleteRegistration');
            
            foreach ($input['enabledesc_html_events'] as $event => $value) {
                if (in_array($event, $allowedesc_html_events) && !empty($value)) {
                    $sanitized['enabledesc_html_events'][$event] = true;
                }
            }
        }
        
        // Sanitize Debug Mode
        $sanitized['debug_mode'] = !empty($input['debug_mode']);
        
        return $sanitized;
    }
    
    /**
     * Handle test connection
     */
    private function handle_test_connection() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['fbpixel_capi_test_nonce'], 'fbpixel_capi_test_connection')) {
            add_settingsesc_html_error(
                self::OPTION_NAME,
                'invalid_nonce',
                __('Security check failed. Please try again.', 'w3-pixel-capi'),
                'error'
            );
            return;
        }
        
        // Test API connection
        $api_client = new FBPixel_CAPI_API_Client();
        $result = $api_client->test_connection();
        
        if (is_wpesc_html_error($result)) {
            add_settingsesc_html_error(
                self::OPTION_NAME,
                'connection_failed',
                sprintf(__('Connection test failed: %s', 'w3-pixel-capi'), $result->getesc_html_error_message()),
                'error'
            );
        } else {
            add_settingsesc_html_error(
                self::OPTION_NAME,
                'connection_success',
                __('Connection test successful! Your Conversions API is working correctly.', 'w3-pixel-capi'),
                'success'
            );
        }
    }
}

