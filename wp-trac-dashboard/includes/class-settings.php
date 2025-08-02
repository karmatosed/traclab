<?php
/**
 * Settings Class for WordPress Trac Dashboard
 */

class WP_Trac_Dashboard_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_menu', array($this, 'add_settings_page'));
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('wp_trac_dashboard_settings', 'wp_trac_dashboard_mcp_url');
        register_setting('wp_trac_dashboard_settings', 'wp_trac_dashboard_mcp_port');
        register_setting('wp_trac_dashboard_settings', 'wp_trac_dashboard_cache_duration');
        register_setting('wp_trac_dashboard_settings', 'wp_trac_dashboard_default_limit');
        register_setting('wp_trac_dashboard_settings', 'wp_trac_dashboard_use_real_mcp');
        
        add_settings_section(
            'wp_trac_dashboard_mcp_section',
            __('MCP Server Configuration', 'wp-trac-dashboard'),
            array($this, 'mcp_section_callback'),
            'wp_trac_dashboard_settings'
        );
        
        add_settings_field(
            'wp_trac_dashboard_mcp_url',
            __('MCP Server URL', 'wp-trac-dashboard'),
            array($this, 'mcp_url_callback'),
            'wp_trac_dashboard_settings',
            'wp_trac_dashboard_mcp_section'
        );
        
        add_settings_field(
            'wp_trac_dashboard_mcp_port',
            __('MCP Server Port', 'wp-trac-dashboard'),
            array($this, 'mcp_port_callback'),
            'wp_trac_dashboard_settings',
            'wp_trac_dashboard_mcp_section'
        );
        
        add_settings_field(
            'wp_trac_dashboard_cache_duration',
            __('Cache Duration (minutes)', 'wp-trac-dashboard'),
            array($this, 'cache_duration_callback'),
            'wp_trac_dashboard_settings',
            'wp_trac_dashboard_mcp_section'
        );
        
        add_settings_field(
            'wp_trac_dashboard_default_limit',
            __('Default Results Limit', 'wp-trac-dashboard'),
            array($this, 'default_limit_callback'),
            'wp_trac_dashboard_settings',
            'wp_trac_dashboard_mcp_section'
        );
        
        add_settings_field(
            'wp_trac_dashboard_use_real_mcp',
            __('Use Real MCP Server', 'wp-trac-dashboard'),
            array($this, 'use_real_mcp_callback'),
            'wp_trac_dashboard_settings',
            'wp_trac_dashboard_mcp_section'
        );
    }
    
    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'wp-trac-dashboard',
            __('Settings', 'wp-trac-dashboard'),
            __('Settings', 'wp-trac-dashboard'),
            'manage_options',
            'wp-trac-dashboard-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Settings page content
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('wp_trac_dashboard_settings');
                do_settings_sections('wp_trac_dashboard_settings');
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Test Connection', 'wp-trac-dashboard'); ?></h2>
            <p><?php _e('Test your MCP server connection to ensure it\'s working properly.', 'wp-trac-dashboard'); ?></p>
            <button type="button" id="test-mcp-connection" class="button button-secondary">
                <?php _e('Test Connection', 'wp-trac-dashboard'); ?>
            </button>
            <div id="test-connection-result"></div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-mcp-connection').on('click', function() {
                var button = $(this);
                var resultDiv = $('#test-connection-result');
                
                button.prop('disabled', true).text('<?php _e('Testing...', 'wp-trac-dashboard'); ?>');
                resultDiv.html('');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wp_trac_dashboard_test_connection',
                        nonce: '<?php echo wp_create_nonce('wp_trac_dashboard_test_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            resultDiv.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                        } else {
                            resultDiv.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        resultDiv.html('<div class="notice notice-error"><p><?php _e('Connection test failed.', 'wp-trac-dashboard'); ?></p></div>');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('<?php _e('Test Connection', 'wp-trac-dashboard'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * MCP section callback
     */
    public function mcp_section_callback() {
        echo '<p>' . __('Configure your MCP server connection settings. The MCP server must be running locally or accessible via HTTP.', 'wp-trac-dashboard') . '</p>';
    }
    
    /**
     * MCP URL field callback
     */
    public function mcp_url_callback() {
        $value = get_option('wp_trac_dashboard_mcp_url', 'http://localhost');
        echo '<input type="url" name="wp_trac_dashboard_mcp_url" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('The URL where your MCP server is running (e.g., http://localhost)', 'wp-trac-dashboard') . '</p>';
    }
    
    /**
     * MCP port field callback
     */
    public function mcp_port_callback() {
        $value = get_option('wp_trac_dashboard_mcp_port', '3000');
        echo '<input type="number" name="wp_trac_dashboard_mcp_port" value="' . esc_attr($value) . '" class="small-text" min="1" max="65535" />';
        echo '<p class="description">' . __('The port number for your MCP server', 'wp-trac-dashboard') . '</p>';
    }
    
    /**
     * Cache duration field callback
     */
    public function cache_duration_callback() {
        $value = get_option('wp_trac_dashboard_cache_duration', '15');
        echo '<input type="number" name="wp_trac_dashboard_cache_duration" value="' . esc_attr($value) . '" class="small-text" min="1" max="1440" />';
        echo '<p class="description">' . __('How long to cache Trac data in minutes (1-1440)', 'wp-trac-dashboard') . '</p>';
    }
    
    /**
     * Default limit field callback
     */
    public function default_limit_callback() {
        $value = get_option('wp_trac_dashboard_default_limit', '10');
        echo '<input type="number" name="wp_trac_dashboard_default_limit" value="' . esc_attr($value) . '" class="small-text" min="1" max="50" />';
        echo '<p class="description">' . __('Default number of tickets to display (1-50)', 'wp-trac-dashboard') . '</p>';
    }
    
    /**
     * Use real MCP field callback
     */
    public function use_real_mcp_callback() {
        $value = get_option('wp_trac_dashboard_use_real_mcp', false);
        echo '<input type="checkbox" name="wp_trac_dashboard_use_real_mcp" value="1" ' . checked(1, $value, false) . ' />';
        echo '<p class="description">' . __('Enable to use real MCP server data instead of mock data', 'wp-trac-dashboard') . '</p>';
    }
} 