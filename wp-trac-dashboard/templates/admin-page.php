<?php
/**
 * Admin Dashboard Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Search and Filter Controls -->
    <div class="wp-trac-dashboard-controls">
        <div class="search-box">
            <input type="text" id="trac-search" placeholder="<?php _e('Search tickets...', 'wp-trac-dashboard'); ?>" />
            <button type="button" id="search-tickets" class="button button-primary">
                <?php _e('Search', 'wp-trac-dashboard'); ?>
            </button>
        </div>
        
        <div class="url-input-box">
            <input type="url" id="trac-url" placeholder="<?php _e('Or paste Trac ticket URL...', 'wp-trac-dashboard'); ?>" />
            <button type="button" id="load-ticket-url" class="button button-secondary">
                <?php _e('Load Ticket', 'wp-trac-dashboard'); ?>
            </button>
        </div>
        
        <div class="filter-controls">
            <select id="status-filter">
                <option value=""><?php _e('All Statuses', 'wp-trac-dashboard'); ?></option>
                <option value="open"><?php _e('Open', 'wp-trac-dashboard'); ?></option>
                <option value="closed"><?php _e('Closed', 'wp-trac-dashboard'); ?></option>
                <option value="new"><?php _e('New', 'wp-trac-dashboard'); ?></option>
            </select>
            
            <select id="component-filter">
                <option value=""><?php _e('All Components', 'wp-trac-dashboard'); ?></option>
                <!-- Will be populated via AJAX -->
            </select>
            
            <select id="limit-select">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    
    <!-- Loading Indicator -->
    <div id="loading-indicator" class="wp-trac-loading" style="display: none;">
        <div class="spinner is-active"></div>
        <span><?php _e('Loading tickets...', 'wp-trac-dashboard'); ?></span>
    </div>
    
    <!-- Results Container -->
    <div id="trac-results">
        <div class="wp-trac-tickets-list">
            <!-- Tickets will be loaded here -->
        </div>
        
        <div id="no-tickets" class="wp-trac-no-results" style="display: none;">
            <p><?php _e('No tickets found matching your criteria.', 'wp-trac-dashboard'); ?></p>
        </div>
    </div>
    
    <!-- Ticket Details Modal -->
    <div id="ticket-modal" class="wp-trac-modal" style="display: none;">
        <div class="wp-trac-modal-content">
            <div class="wp-trac-modal-header">
                <h2 id="modal-ticket-title"></h2>
                <span class="wp-trac-modal-close">&times;</span>
            </div>
            <div class="wp-trac-modal-body">
                <div id="modal-ticket-content">
                    <!-- Ticket details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="wp-trac-dashboard-stats">
    <div class="stat-card">
        <h3><?php _e('Recent Activity', 'wp-trac-dashboard'); ?></h3>
        <div id="recent-timeline">
            <!-- Recent timeline will be loaded here -->
        </div>
    </div>
</div>

<script type="text/template" id="ticket-template">
    <div class="wp-trac-ticket" data-ticket-id="{{id}}">
        <div class="ticket-header">
            <h3 class="ticket-summary">
                <a href="#" class="ticket-link">#{{id}} - {{summary}}</a>
            </h3>
            <span class="ticket-status status-{{status}}">{{status}}</span>
        </div>
        <div class="ticket-meta">
            <span class="ticket-component">{{component}}</span>
            <span class="ticket-priority priority-{{priority}}">{{priority}}</span>
            <span class="ticket-created">{{created}}</span>
            <a href="{{url}}" target="_blank" class="ticket-external-link">View on Trac</a>
        </div>
        <div class="ticket-description">
            {{description}}
        </div>
    </div>
</script>

<script type="text/template" id="timeline-template">
    <div class="timeline-item">
        <span class="timeline-time">{{time}}</span>
        <span class="timeline-action">{{action}}</span>
        <a href="#" class="timeline-link">{{link}}</a>
    </div>
</script> 