# WordPress Trac Dashboard

A WordPress plugin that displays latest WordPress Trac ticket status using MCP (Model Context Protocol) server integration.

## Features

- **Real-time Trac Data**: Fetch and display WordPress Trac tickets directly in your WordPress admin
- **Advanced Search**: Search tickets by keywords, status, and component
- **Ticket Details**: View detailed ticket information including comments
- **Recent Activity**: Display recent Trac timeline activity
- **Responsive Design**: Works on desktop and mobile devices
- **Caching**: Built-in caching to improve performance
- **Settings Panel**: Easy configuration of MCP server connection

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MCP server with WordPress Trac integration
- Administrator access to WordPress

## Installation

1. **Download the Plugin**
   ```bash
   git clone https://github.com/your-username/wp-trac-dashboard.git
   ```

2. **Install in WordPress**
   - Copy the `wp-trac-dashboard` folder to your WordPress `wp-content/plugins/` directory
   - Activate the plugin through the WordPress admin panel

3. **Configure MCP Server**
   - Go to **Trac Dashboard > Settings** in your WordPress admin
   - Enter your MCP server URL and port
   - Test the connection

## MCP Server Setup

### Option 1: Local MCP Server

If you're running the MCP server locally:

1. **Install MCP Server**
   ```bash
   # Example installation (adjust based on your MCP server)
   npm install -g @modelcontextprotocol/server
   ```

2. **Configure WordPress Trac Integration**
   ```bash
   # Add WordPress Trac server to your MCP configuration
   mcp add-server wordpress-trac
   ```

3. **Start MCP Server**
   ```bash
   mcp start --port 3000
   ```

4. **Configure Plugin**
   - MCP Server URL: `http://localhost`
   - MCP Server Port: `3000`

### Option 2: Remote MCP Server

If you're using a remote MCP server:

1. **Get Server Details**
   - Contact your MCP server administrator
   - Obtain the server URL and port
   - Ensure you have proper access credentials

2. **Configure Plugin**
   - MCP Server URL: `https://your-mcp-server.com`
   - MCP Server Port: `443` (or your server's port)

### Option 3: Command Line Integration

The plugin also supports direct command-line integration:

1. **Install MCP CLI Tools**
   ```bash
   # Install WordPress Trac MCP CLI tool
   npm install -g mcp-wordpress-trac
   ```

2. **Configure Plugin**
   - The plugin will automatically detect CLI tools
   - No additional configuration needed

## Usage

### Dashboard Overview

1. **Access Dashboard**
   - Navigate to **Trac Dashboard** in your WordPress admin menu
   - View recent tickets and activity

2. **Search Tickets**
   - Use the search box to find specific tickets
   - Filter by status (Open, Closed, New)
   - Filter by component
   - Adjust results limit

3. **View Ticket Details**
   - Click on any ticket to view full details
   - See ticket description, comments, and metadata
   - Modal popup for easy viewing

### Settings Configuration

1. **MCP Server Settings**
   - **MCP Server URL**: The base URL of your MCP server
   - **MCP Server Port**: The port number for your MCP server
   - **Cache Duration**: How long to cache Trac data (in minutes)
   - **Default Results Limit**: Number of tickets to display by default

2. **Test Connection**
   - Use the "Test Connection" button to verify MCP server connectivity
   - Check for any configuration errors

## File Structure

```
wp-trac-dashboard/
├── wp-trac-dashboard.php          # Main plugin file
├── includes/
│   ├── class-mcp-trac-client.php  # MCP server communication
│   └── class-settings.php         # Settings management
├── templates/
│   └── admin-page.php             # Dashboard template
├── assets/
│   ├── js/
│   │   └── admin.js               # Frontend JavaScript
│   └── css/
│       └── admin.css              # Dashboard styles
└── README.md                      # This file
```

## API Integration

The plugin integrates with the WordPress Trac MCP server using these functions:

- `searchTickets`: Search for tickets with filters
- `getTicket`: Get detailed ticket information
- `getTimeline`: Get recent activity timeline
- `getTracInfo`: Get metadata (components, priorities, etc.)

### Example API Usage

```php
// Get MCP client instance
$mcp_client = new WP_Trac_Dashboard_MCP_Client();

// Search for tickets
$tickets = $mcp_client->search_tickets('bug', 'open', 'Administration', 10);

// Get ticket details
$ticket = $mcp_client->get_ticket(12345, true, 10);

// Get recent timeline
$timeline = $mcp_client->get_timeline(7, 20);
```

## Troubleshooting

### Common Issues

1. **Connection Failed**
   - Verify MCP server is running
   - Check URL and port settings
   - Ensure firewall allows connections
   - Test with curl: `curl http://localhost:3000/mcp/wordpress-trac/searchTickets`

2. **No Tickets Displayed**
   - Check MCP server logs for errors
   - Verify WordPress Trac integration is working
   - Test with a simple search query

3. **Permission Errors**
   - Ensure you have administrator access
   - Check WordPress user capabilities
   - Verify nonce validation

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Logs

Check these locations for error logs:
- WordPress debug log: `wp-content/debug.log`
- MCP server logs: Check your MCP server documentation
- Browser console: Press F12 to view JavaScript errors

## Development

### Adding New Features

1. **Extend MCP Client**
   ```php
   // Add new methods to class-mcp-trac-client.php
   public function get_custom_data($params) {
       return $this->make_mcp_request('customFunction', $params);
   }
   ```

2. **Add AJAX Handlers**
   ```php
   // Add to main plugin class
   add_action('wp_ajax_wp_trac_dashboard_custom_action', array($this, 'ajax_custom_action'));
   ```

3. **Update Frontend**
   ```javascript
   // Add to admin.js
   customFunction: function() {
       // Your custom functionality
   }
   ```

### Styling

Customize the dashboard appearance by modifying `assets/css/admin.css`:

```css
/* Custom ticket styling */
.wp-trac-ticket {
    border-left: 4px solid #0073aa;
}

/* Custom status colors */
.status-custom {
    background: #your-color;
    color: #your-text-color;
}
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and questions:
- Create an issue on GitHub
- Check the troubleshooting section
- Review WordPress and MCP server documentation

## Changelog

### Version 1.0.0
- Initial release
- MCP server integration
- Dashboard interface
- Search and filtering
- Ticket details modal
- Settings configuration
- Responsive design 