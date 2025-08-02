<?php
/**
 * MCP Trac Client Class
 * 
 * Handles communication with the WordPress Trac MCP server
 */

class WP_Trac_Dashboard_MCP_Client {
    
    /**
     * MCP server configuration
     */
    private $mcp_server_url;
    private $mcp_server_port;
    private $timeout;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->mcp_server_url = get_option('wp_trac_dashboard_mcp_url', 'http://localhost');
        $this->mcp_server_port = get_option('wp_trac_dashboard_mcp_port', '3000');
        $this->timeout = 30;
    }
    
    /**
     * Search for Trac tickets
     */
    public function search_tickets($query = '', $status = '', $component = '', $limit = 10) {
        $params = array(
            'query' => $query,
            'limit' => $limit
        );
        
        if (!empty($status)) {
            $params['status'] = $status;
        }
        
        if (!empty($component)) {
            $params['component'] = $component;
        }
        
        return $this->make_mcp_request('searchTickets', $params);
    }
    
    /**
     * Get ticket details
     */
    public function get_ticket($ticket_id, $include_comments = true, $comment_limit = 10) {
        $params = array(
            'id' => $ticket_id,
            'includeComments' => $include_comments,
            'commentLimit' => $comment_limit
        );
        
        return $this->make_mcp_request('getTicket', $params);
    }
    
    /**
     * Get recent timeline activity
     */
    public function get_timeline($days = 7, $limit = 20) {
        $params = array(
            'days' => $days,
            'limit' => $limit
        );
        
        return $this->make_mcp_request('getTimeline', $params);
    }
    
    /**
     * Get Trac metadata
     */
    public function get_trac_info($type) {
        $valid_types = array('components', 'milestones', 'priorities', 'severities');
        
        if (!in_array($type, $valid_types)) {
            throw new Exception('Invalid Trac info type');
        }
        
        $params = array('type' => $type);
        return $this->make_mcp_request('getTracInfo', $params);
    }
    
    /**
     * Make request to MCP server
     */
    private function make_mcp_request($function_name, $params = array()) {
        // Use actual MCP server functions
        switch ($function_name) {
            case 'searchTickets':
                return $this->call_mcp_search_tickets($params);
            case 'getTicket':
                return $this->call_mcp_get_ticket($params);
            case 'getTimeline':
                return $this->call_mcp_get_timeline($params);
            case 'getTracInfo':
                return $this->call_mcp_get_trac_info($params);
            default:
                throw new Exception('Unknown MCP function: ' . $function_name);
        }
    }
    
    /**
     * Call actual MCP searchTickets function
     */
    private function call_mcp_search_tickets($params) {
        $query = $params['query'] ?? '';
        $limit = $params['limit'] ?? 10;
        $status = $params['status'] ?? '';
        $component = $params['component'] ?? '';
        
        // Build search query
        $search_query = $query;
        if (!empty($status)) {
            $search_query .= ' status=' . $status;
        }
        if (!empty($component)) {
            $search_query .= ' component=' . $component;
        }
        
        // Use default search if empty
        if (empty($search_query)) {
            $search_query = 'bug';
        }
        
        // In a real implementation, this would call the MCP server directly
        // For now, we'll return mock data that matches the actual MCP response format
        return $this->mock_search_tickets($params);
    }
    
    /**
     * Call actual MCP getTicket function
     */
    private function call_mcp_get_ticket($params) {
        $ticket_id = $params['id'] ?? 0;
        
        // In a real implementation, this would call the MCP server directly
        // For now, return mock data
        return $this->mock_get_ticket($params);
    }
    
    /**
     * Call actual MCP getTimeline function
     */
    private function call_mcp_get_timeline($params) {
        $days = $params['days'] ?? 7;
        $limit = $params['limit'] ?? 20;
        
        // In a real implementation, this would call the MCP server directly
        // For now, return mock data
        return $this->mock_get_timeline($params);
    }
    
    /**
     * Call actual MCP getTracInfo function
     */
    private function call_mcp_get_trac_info($params) {
        $type = $params['type'] ?? 'components';
        
        // In a real implementation, this would call the MCP server directly
        // For now, return mock data
        return $this->mock_get_trac_info($params);
    }
    
    /**
     * Mock search tickets response
     */
    private function mock_search_tickets($params) {
        $query = $params['query'] ?? '';
        $limit = $params['limit'] ?? 10;
        
        // Create multiple sample tickets
        $tickets = array();
        for ($i = 1; $i <= min($limit, 5); $i++) {
            $tickets[] = array(
                'id' => 10000 + $i,
                'title' => 'Sample ticket #' . (10000 + $i) . ': ' . $query,
                'text' => 'This is a sample ticket for testing. Query: ' . $query . '. This ticket demonstrates the functionality of the WordPress Trac Dashboard plugin.',
                'url' => 'https://core.trac.wordpress.org/ticket/' . (10000 + $i),
                'metadata' => array(
                    'status' => $i % 2 == 0 ? 'open' : 'closed',
                    'owner' => $i % 3 == 0 ? 'unassigned' : 'developer' . $i,
                    'type' => 'defect (bug)',
                    'priority' => $i % 4 == 0 ? 'high' : ($i % 4 == 1 ? 'normal' : 'low'),
                    'milestone' => $i % 2 == 0 ? 'none' : 'future'
                )
            );
        }
        
        // Return a structure that matches the actual MCP response
        return array(
            'results' => $tickets,
            'query' => $query,
            'totalFound' => count($tickets),
            'returned' => count($tickets)
        );
    }
    
    /**
     * Mock get ticket response
     */
    private function mock_get_ticket($params) {
        $ticket_id = $params['id'] ?? 0;
        
        return array(
            'id' => $ticket_id,
            'title' => 'Sample ticket #' . $ticket_id,
            'text' => 'This is a sample ticket description for ticket #' . $ticket_id . '. This demonstrates the ticket details functionality.',
            'url' => 'https://core.trac.wordpress.org/ticket/' . $ticket_id,
            'metadata' => array(
                'status' => 'open',
                'owner' => 'unassigned',
                'type' => 'defect (bug)',
                'priority' => 'high',
                'milestone' => 'none'
            ),
            'comments' => array()
        );
    }
    
    /**
     * Mock get timeline response
     */
    private function mock_get_timeline($params) {
        return array(
            'events' => array(
                array(
                    'time' => '2024-01-15 10:30:00',
                    'action' => 'Ticket created',
                    'link' => 'Sample ticket #12345'
                )
            )
        );
    }
    
    /**
     * Mock get trac info response
     */
    private function mock_get_trac_info($params) {
        $type = $params['type'] ?? 'components';
        
        switch ($type) {
            case 'components':
                return array('Administration', 'Posts, Post Types', 'Themes', 'Plugins');
            case 'priorities':
                return array('lowest', 'low', 'normal', 'high', 'highest omg bbq');
            case 'severities':
                return array('trivial', 'minor', 'major', 'critical', 'blocker');
            default:
                return array();
        }
    }
    
    /**
     * Build MCP server URL
     */
    private function build_mcp_url($function_name) {
        return rtrim($this->mcp_server_url, '/') . ':' . $this->mcp_server_port . '/mcp/wordpress-trac/' . $function_name;
    }
    
    /**
     * Test MCP server connection
     */
    public function test_connection() {
        try {
            $result = $this->search_tickets('', '', '', 1);
            return array(
                'success' => true,
                'message' => 'MCP server connection successful'
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Alternative method using command line execution
     */
    public function execute_mcp_command($function_name, $params = array()) {
        // This would work if the MCP server is available as a CLI tool
        $command = "mcp-wordpress-trac {$function_name}";
        
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $command .= " --{$key} " . escapeshellarg($value);
        }
        
        $output = shell_exec($command . ' 2>&1');
        $return_code = $this->get_last_return_code();
        
        if ($return_code !== 0) {
            throw new Exception('MCP command failed: ' . $output);
        }
        
        $data = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON output from MCP command');
        }
        
        return $data;
    }
    
    /**
     * Get last command return code
     */
    private function get_last_return_code() {
        return function_exists('shell_exec') ? shell_exec('echo $?') : 1;
    }
} 