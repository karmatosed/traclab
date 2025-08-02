<?php
/**
 * Plugin Name: WordPress Trac Dashboard
 * Plugin URI: https://github.com/your-username/wp-trac-dashboard
 * Description: A WordPress plugin that displays latest WordPress Trac ticket status using MCP server integration.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: wp-trac-dashboard
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_TRAC_DASHBOARD_VERSION', '1.0.0');
define('WP_TRAC_DASHBOARD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_TRAC_DASHBOARD_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WP_TRAC_DASHBOARD_PLUGIN_DIR . 'includes/class-mcp-trac-client.php';
require_once WP_TRAC_DASHBOARD_PLUGIN_DIR . 'includes/class-real-mcp-client.php';
require_once WP_TRAC_DASHBOARD_PLUGIN_DIR . 'includes/class-settings.php';

/**
 * Main plugin class
 */
class WP_Trac_Dashboard {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wp_trac_dashboard_get_tickets', array($this, 'ajax_get_tickets'));
        add_action('wp_ajax_wp_trac_dashboard_get_ticket_details', array($this, 'ajax_get_ticket_details'));
        add_action('wp_ajax_wp_trac_dashboard_get_components', array($this, 'ajax_get_components'));
        add_action('wp_ajax_wp_trac_dashboard_get_timeline', array($this, 'ajax_get_timeline'));
        add_action('wp_ajax_wp_trac_dashboard_test_connection', array($this, 'ajax_test_connection'));
        
        // Initialize settings
        new WP_Trac_Dashboard_Settings();
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        load_plugin_textdomain('wp-trac-dashboard', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WordPress Trac Dashboard', 'wp-trac-dashboard'),
            __('Trac Dashboard', 'wp-trac-dashboard'),
            'manage_options',
            'wp-trac-dashboard',
            array($this, 'admin_page'),
            'dashicons-tickets-alt',
            30
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_wp-trac-dashboard') {
            return;
        }
        
        wp_enqueue_script(
            'wp-trac-dashboard-admin',
            WP_TRAC_DASHBOARD_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WP_TRAC_DASHBOARD_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wp-trac-dashboard-admin',
            WP_TRAC_DASHBOARD_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_TRAC_DASHBOARD_VERSION
        );
        
        wp_localize_script('wp-trac-dashboard-admin', 'wpTracDashboard', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_trac_dashboard_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'wp-trac-dashboard'),
                'error' => __('Error occurred', 'wp-trac-dashboard'),
                'noTickets' => __('No tickets found', 'wp-trac-dashboard')
            )
        ));
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        include WP_TRAC_DASHBOARD_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Get the appropriate MCP client based on settings
     */
    private function get_mcp_client() {
        $use_real_mcp = get_option('wp_trac_dashboard_use_real_mcp', false);
        
        if ($use_real_mcp) {
            return new WP_Trac_Dashboard_Real_MCP_Client();
        } else {
            return new WP_Trac_Dashboard_MCP_Client();
        }
    }
    
    /**
     * AJAX handler for getting tickets
     */
    public function ajax_get_tickets() {
        check_ajax_referer('wp_trac_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this resource.', 'wp-trac-dashboard'));
        }
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $component = sanitize_text_field($_POST['component'] ?? '');
        $limit = intval($_POST['limit'] ?? 10);
        
        try {
            $tickets = $this->get_trac_tickets($query, $status, $component, $limit);
            wp_send_json_success($tickets);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX handler for getting ticket details
     */
    public function ajax_get_ticket_details() {
        check_ajax_referer('wp_trac_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this resource.', 'wp-trac-dashboard'));
        }
        
        $ticket_id = intval($_POST['ticket_id'] ?? 0);
        
        if (!$ticket_id) {
            wp_send_json_error(array('message' => __('Invalid ticket ID.', 'wp-trac-dashboard')));
        }
        
        try {
            $ticket_details = $this->get_trac_ticket_details($ticket_id);
            wp_send_json_success($ticket_details);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX handler for getting components
     */
    public function ajax_get_components() {
        check_ajax_referer('wp_trac_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this resource.', 'wp-trac-dashboard'));
        }
        
        try {
            $mcp_client = $this->get_mcp_client();
            $components = $mcp_client->get_trac_info('components');
            wp_send_json_success($components);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX handler for getting timeline
     */
    public function ajax_get_timeline() {
        check_ajax_referer('wp_trac_dashboard_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this resource.', 'wp-trac-dashboard'));
        }
        
        $days = intval($_POST['days'] ?? 7);
        $limit = intval($_POST['limit'] ?? 10);
        
        try {
            $mcp_client = $this->get_mcp_client();
            $timeline = $mcp_client->get_timeline($days, $limit);
            wp_send_json_success($timeline);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * AJAX handler for testing connection
     */
    public function ajax_test_connection() {
        check_ajax_referer('wp_trac_dashboard_test_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this resource.', 'wp-trac-dashboard'));
        }
        
        try {
            $mcp_client = $this->get_mcp_client();
            $result = $mcp_client->test_connection();
            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * Get Trac tickets using MCP server
     */
    private function get_trac_tickets($query = '', $status = '', $component = '', $limit = 10) {
        try {
            $mcp_client = $this->get_mcp_client();
            $result = $mcp_client->search_tickets($query, $status, $component, $limit);
            
            // Transform the result to match our expected format
            $tickets = array();
            if (isset($result['results']) && is_array($result['results'])) {
                foreach ($result['results'] as $ticket) {
                    $tickets[] = array(
                        'id' => $ticket['id'] ?? 0,
                        'summary' => $ticket['title'] ?? '',
                        'status' => $ticket['metadata']['status'] ?? '',
                        'priority' => $ticket['metadata']['priority'] ?? '',
                        'component' => $ticket['metadata']['type'] ?? '',
                        'created' => '', // Not available in current response
                        'description' => $ticket['text'] ?? '',
                        'url' => $ticket['url'] ?? '',
                        'owner' => $ticket['metadata']['owner'] ?? '',
                        'milestone' => $ticket['metadata']['milestone'] ?? ''
                    );
                }
            }
            
            return array(
                'tickets' => $tickets,
                'total' => $result['totalFound'] ?? count($tickets),
                'query' => $query,
                'status' => $status,
                'component' => $component,
                'limit' => $limit
            );
        } catch (Exception $e) {
            // Return empty result on error
            return array(
                'tickets' => array(),
                'total' => 0,
                'query' => $query,
                'status' => $status,
                'component' => $component,
                'limit' => $limit,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Get Trac ticket details using MCP server
     */
    private function get_trac_ticket_details($ticket_id) {
        try {
            $mcp_client = $this->get_mcp_client();
            $result = $mcp_client->get_ticket($ticket_id, true, 10);
            
            // Transform the result to match our expected format
            $ticket = array(
                'id' => $ticket_id,
                'summary' => $result['title'] ?? '',
                'description' => $result['text'] ?? '',
                'status' => $result['metadata']['status'] ?? '',
                'priority' => $result['metadata']['priority'] ?? '',
                'component' => $result['metadata']['type'] ?? '',
                'created' => '', // Not available in current response
                'url' => $result['url'] ?? '',
                'owner' => $result['metadata']['owner'] ?? '',
                'milestone' => $result['metadata']['milestone'] ?? '',
                'comments' => array()
            );
            
            // Transform comments if available
            if (isset($result['comments']) && is_array($result['comments'])) {
                foreach ($result['comments'] as $comment) {
                    $ticket['comments'][] = array(
                        'author' => $comment['author'] ?? '',
                        'date' => $comment['date'] ?? $comment['time'] ?? '',
                        'content' => $comment['content'] ?? $comment['comment'] ?? ''
                    );
                }
            }
            
            return $ticket;
        } catch (Exception $e) {
            // Return basic ticket info on error
            return array(
                'id' => $ticket_id,
                'summary' => 'Error loading ticket details',
                'description' => $e->getMessage(),
                'status' => '',
                'priority' => '',
                'component' => '',
                'created' => '',
                'comments' => array()
            );
        }
    }
}

// Initialize the plugin
new WP_Trac_Dashboard(); 