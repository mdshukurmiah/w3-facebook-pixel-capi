/**
 * W3 Facebook Pixel CAPI Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Initialize admin functionality
        initPasswordToggle();
        initFormValidation();
        initTestConnection();
        
        /**
         * Initialize password field toggle
         */
        function initPasswordToggle() {
            var $accessTokenField = $('#access_token');
            
            if ($accessTokenField.length) {
                var $toggleButton = $('<button type="button" class="button button-secondary" style="margin-left: 5px;">Show</button>');
                
                $accessTokenField.after($toggleButton);
                
                $toggleButton.on('click', function() {
                    var $this = $(this);
                    var $field = $accessTokenField;
                    
                    if ($field.attr('type') === 'password') {
                        $field.attr('type', 'text');
                        $this.text('Hide');
                    } else {
                        $field.attr('type', 'password');
                        $this.text('Show');
                    }
                });
            }
        }
        
        /**
         * Initialize form validation
         */
        function initFormValidation() {
            var $form = $('form[action="options.php"]');
            
            if ($form.length) {
                $form.on('submit', function(e) {
                    var isValid = true;
                    var errors = [];
                    
                    // Validate Pixel ID
                    var pixelId = $('#pixel_id').val().trim();
                    if (pixelId && !/^[0-9]{15,16}$/.test(pixelId)) {
                        errors.push('Pixel ID must be 15-16 digits');
                        isValid = false;
                    }
                    
                    // Validate Access Token
                    var accessToken = $('#access_token').val().trim();
                    if (accessToken && accessToken.length < 50) {
                        errors.push('Access Token appears to be too short');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fix the following errors:\n\n' + errors.join('\n'));
                    }
                });
            }
        }
        
        /**
         * Initialize test connection functionality
         */
        function initTestConnection() {
            var $testButton = $('input[name="test_connection"]');
            
            if ($testButton.length) {
                $testButton.on('click', function() {
                    var $this = $(this);
                    var originalText = $this.val();
                    
                    // Check if required fields are filled
                    var pixelId = $('#pixel_id').val().trim();
                    var accessToken = $('#access_token').val().trim();
                    
                    if (!pixelId || !accessToken) {
                        alert('Please enter both Pixel ID and Access Token before testing the connection.');
                        return false;
                    }
                    
                    // Add loading state
                    $this.val('Testing...').prop('disabled', true);
                    
                    // Re-enable button after form submission
                    setTimeout(function() {
                        $this.val(originalText).prop('disabled', false);
                    }, 3000);
                });
            }
        }
        
        /**
         * Handle event checkbox dependencies
         */
        function initEventDependencies() {
            var $wooCommerceEvents = $('input[name*="[enabled_events][AddToCart]"], input[name*="[enabled_events][InitiateCheckout]"], input[name*="[enabled_events][Purchase]"]');
            
            // Check if WooCommerce is active
            if ($('.fbpixel-capi-info-box').text().indexOf('WooCommerce Status: Not Active') !== -1) {
                $wooCommerceEvents.prop('disabled', true).closest('label').css('color', '#999');
            }
        }
        
        // Initialize event dependencies
        initEventDependencies();
        
        /**
         * Auto-save settings on change (optional feature)
         */
        function initAutoSave() {
            var $inputs = $('#pixel_id, #access_token, #test_event_code, input[name*="enabled_events"], input[name*="debug_mode"]');
            var saveTimeout;
            
            $inputs.on('change', function() {
                clearTimeout(saveTimeout);
                
                saveTimeout = setTimeout(function() {
                    // Show auto-save indicator
                    var $indicator = $('<span class="fbpixel-capi-autosave">Saving...</span>');
                    $('.wrap h1').append($indicator);
                    
                    // Remove indicator after 2 seconds
                    setTimeout(function() {
                        $indicator.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 2000);
                }, 1000);
            });
        }
        
        // Uncomment to enable auto-save
        // initAutoSave();
        
    });
    
})(jQuery);

