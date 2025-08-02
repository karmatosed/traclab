<?php
/**
 * WordPress Trac Dashboard Installation Script
 * 
 * This script helps verify the plugin installation and MCP server connection.
 * Run this file directly in your browser to check your setup.
 */

// Prevent direct access if not in WordPress context
if (!defined('ABSPATH')) {
    // If not in WordPress, create a simple test environment
    if (!class_exists('WP_Trac_Dashboard_MCP_Client')) {
        require_once __DIR__ . '/includes/class-mcp-trac-client.php';
    }
}

// Define constants if not already defined
if (!defined('WP_TRAC_DASHBOARD_VERSION')) {
    define('WP_TRAC_DASHBOARD_VERSION', '1.0.0');
}

/**
 * Installation Test Class
 */
class WP_Trac_Dashboard_Install_Test {
    
    private $results = array();
    
    public function run_tests() {
        $this->test_php_version();
        $this->test_wordpress_environment();
        $this->test_plugin_files();
        $this->test_mcp_client();
        $this->test_mcp_connection();
        
        return $this->results;
    }
    
    private function test_php_version() {
        $required = '7.4';
        $current = PHP_VERSION;
        
        $this->results['php_version'] = array(
            'test' => 'PHP Version',
            'required' => $required,
            'current' => $current,
            'status' => version_compare($current, $required, '>=') ? 'pass' : 'fail',
            'message' => version_compare($current, $required, '>=') 
                ? "PHP version {$current} meets requirements" 
                : "PHP version {$current} is below required {$required}"
        );
    }
    
    private function test_wordpress_environment() {
        $this->results['wordpress'] = array(
            'test' => 'WordPress Environment',
            'status' => defined('ABSPATH') ? 'pass' : 'warning',
            'message' => defined('ABSPATH') 
                ? 'Running in WordPress environment' 
                : 'Not running in WordPress environment (standalone test)'
        );
    }
    
    private function test_plugin_files() {
        $required_files = array(
            'wp-trac-dashboard.php',
            'includes/class-mcp-trac-client.php',
            'includes/class-settings.php',
            'templates/admin-page.php',
            'assets/js/admin.js',
            'assets/css/admin.css'
        );
        
        $missing_files = array();
        foreach ($required_files as $file) {
            if (!file_exists(__DIR__ . '/' . $file)) {
                $missing_files[] = $file;
            }
        }
        
        $this->results['plugin_files'] = array(
            'test' => 'Plugin Files',
            'status' => empty($missing_files) ? 'pass' : 'fail',
            'message' => empty($missing_files) 
                ? 'All required plugin files are present' 
                : 'Missing files: ' . implode(', ', $missing_files)
        );
    }
    
    private function test_mcp_client() {
        try {
            if (class_exists('WP_Trac_Dashboard_MCP_Client')) {
                $client = new WP_Trac_Dashboard_MCP_Client();
                $this->results['mcp_client'] = array(
                    'test' => 'MCP Client Class',
                    'status' => 'pass',
                    'message' => 'MCP client class loaded successfully'
                );
            } else {
                $this->results['mcp_client'] = array(
                    'test' => 'MCP Client Class',
                    'status' => 'fail',
                    'message' => 'MCP client class not found'
                );
            }
        } catch (Exception $e) {
            $this->results['mcp_client'] = array(
                'test' => 'MCP Client Class',
                'status' => 'fail',
                'message' => 'Error loading MCP client: ' . $e->getMessage()
            );
        }
    }
    
    private function test_mcp_connection() {
        try {
            if (class_exists('WP_Trac_Dashboard_MCP_Client')) {
                $client = new WP_Trac_Dashboard_MCP_Client();
                $result = $client->test_connection();
                
                $this->results['mcp_connection'] = array(
                    'test' => 'MCP Server Connection',
                    'status' => $result['success'] ? 'pass' : 'fail',
                    'message' => $result['message']
                );
            } else {
                $this->results['mcp_connection'] = array(
                    'test' => 'MCP Server Connection',
                    'status' => 'skip',
                    'message' => 'Skipped - MCP client not available'
                );
            }
        } catch (Exception $e) {
            $this->results['mcp_connection'] = array(
                'test' => 'MCP Server Connection',
                'status' => 'fail',
                'message' => 'Connection failed: ' . $e->getMessage()
            );
        }
    }
    
    public function display_results() {
        $results = $this->results;
        
        echo '<!DOCTYPE html>';
        echo '<html><head>';
        echo '<title>WordPress Trac Dashboard - Installation Test</title>';
        echo '<style>';
        echo 'body { font-family: Arial, sans-serif; margin: 40px; background: #f1f1f1; }';
        echo '.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
        echo 'h1 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }';
        echo '.test-result { margin: 15px 0; padding: 15px; border-radius: 4px; border-left: 4px solid; }';
        echo '.pass { background: #d4edda; border-color: #28a745; color: #155724; }';
        echo '.fail { background: #f8d7da; border-color: #dc3545; color: #721c24; }';
        echo '.warning { background: #fff3cd; border-color: #ffc107; color: #856404; }';
        echo '.skip { background: #e2e3e5; border-color: #6c757d; color: #495057; }';
        echo '.summary { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px; }';
        echo '</style>';
        echo '</head><body>';
        
        echo '<div class="container">';
        echo '<h1>WordPress Trac Dashboard - Installation Test</h1>';
        
        $pass_count = 0;
        $fail_count = 0;
        $warning_count = 0;
        $skip_count = 0;
        
        foreach ($results as $result) {
            $status_class = $result['status'];
            echo '<div class="test-result ' . $status_class . '">';
            echo '<strong>' . htmlspecialchars($result['test']) . '</strong><br>';
            echo htmlspecialchars($result['message']);
            
            if (isset($result['required']) && isset($result['current'])) {
                echo '<br><small>Required: ' . htmlspecialchars($result['required']) . ', Current: ' . htmlspecialchars($result['current']) . '</small>';
            }
            echo '</div>';
            
            switch ($result['status']) {
                case 'pass': $pass_count++; break;
                case 'fail': $fail_count++; break;
                case 'warning': $warning_count++; break;
                case 'skip': $skip_count++; break;
            }
        }
        
        echo '<div class="summary">';
        echo '<h3>Test Summary</h3>';
        echo '<p><strong>Passed:</strong> ' . $pass_count . '</p>';
        echo '<p><strong>Failed:</strong> ' . $fail_count . '</p>';
        echo '<p><strong>Warnings:</strong> ' . $warning_count . '</p>';
        echo '<p><strong>Skipped:</strong> ' . $skip_count . '</p>';
        
        if ($fail_count === 0) {
            echo '<p style="color: #28a745; font-weight: bold;">✅ All tests passed! Your plugin is ready to use.</p>';
        } else {
            echo '<p style="color: #dc3545; font-weight: bold;">❌ Some tests failed. Please check the issues above.</p>';
        }
        
        echo '<h4>Next Steps:</h4>';
        echo '<ul>';
        if ($fail_count === 0) {
            echo '<li>Activate the plugin in WordPress admin</li>';
            echo '<li>Go to Trac Dashboard > Settings to configure your MCP server</li>';
            echo '<li>Test the connection using the "Test Connection" button</li>';
        } else {
            echo '<li>Fix the failed tests above</li>';
            echo '<li>Ensure your MCP server is running and accessible</li>';
            echo '<li>Check your WordPress and PHP versions</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        echo '</div>';
        echo '</body></html>';
    }
}

// Run the installation test
if (basename($_SERVER['SCRIPT_NAME']) === 'install.php') {
    $test = new WP_Trac_Dashboard_Install_Test();
    $test->display_results();
} 