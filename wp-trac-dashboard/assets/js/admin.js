/**
 * WordPress Trac Dashboard Admin JavaScript
 */

(function($) {
    'use strict';
    
    var WPTracDashboard = {
        
        init: function() {
            this.bindEvents();
            this.loadInitialData();
        },
        
        bindEvents: function() {
            $('#search-tickets').on('click', this.searchTickets);
            $('#trac-search').on('keypress', function(e) {
                if (e.which === 13) {
                    WPTracDashboard.searchTickets();
                }
            });
            
            $('#load-ticket-url').on('click', this.loadTicketFromUrl);
            $('#trac-url').on('keypress', function(e) {
                if (e.which === 13) {
                    WPTracDashboard.loadTicketFromUrl();
                }
            });
            
            $('#status-filter, #component-filter, #limit-select').on('change', this.searchTickets);
            
            $(document).on('click', '.ticket-link', this.showTicketDetails);
            $(document).on('click', '.wp-trac-modal-close', this.hideModal);
            $(document).on('click', '.wp-trac-modal', function(e) {
                if (e.target === this) {
                    WPTracDashboard.hideModal();
                }
            });
        },
        
        loadInitialData: function() {
            this.loadComponents();
            this.loadRecentTimeline();
            this.searchTickets();
        },
        
        searchTickets: function() {
            var query = $('#trac-search').val();
            var status = $('#status-filter').val();
            var component = $('#component-filter').val();
            var limit = $('#limit-select').val();
            
            WPTracDashboard.showLoading();
            
            $.ajax({
                url: wpTracDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_trac_dashboard_get_tickets',
                    nonce: wpTracDashboard.nonce,
                    query: query,
                    status: status,
                    component: component,
                    limit: limit
                },
                success: function(response) {
                    if (response.success) {
                        WPTracDashboard.displayTickets(response.data);
                    } else {
                        WPTracDashboard.showError(response.data.message);
                    }
                },
                error: function() {
                    WPTracDashboard.showError(wpTracDashboard.strings.error);
                },
                complete: function() {
                    WPTracDashboard.hideLoading();
                }
            });
        },
        
        loadComponents: function() {
            $.ajax({
                url: wpTracDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_trac_dashboard_get_components',
                    nonce: wpTracDashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        WPTracDashboard.populateComponents(response.data);
                    }
                }
            });
        },
        
        loadRecentTimeline: function() {
            $.ajax({
                url: wpTracDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_trac_dashboard_get_timeline',
                    nonce: wpTracDashboard.nonce,
                    days: 7,
                    limit: 10
                },
                success: function(response) {
                    if (response.success) {
                        WPTracDashboard.displayTimeline(response.data);
                    }
                }
            });
        },
        
        loadTicketFromUrl: function() {
            var url = $('#trac-url').val().trim();
            
            if (!url) {
                WPTracDashboard.showError('Please enter a Trac ticket URL');
                return;
            }
            
            // Extract ticket ID from URL
            var ticketId = WPTracDashboard.extractTicketIdFromUrl(url);
            
            if (!ticketId) {
                WPTracDashboard.showError('Invalid Trac ticket URL. Please use a URL like: https://core.trac.wordpress.org/ticket/12345');
                return;
            }
            
            WPTracDashboard.showLoading();
            
            $.ajax({
                url: wpTracDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_trac_dashboard_get_ticket_details',
                    nonce: wpTracDashboard.nonce,
                    ticket_id: ticketId
                },
                success: function(response) {
                    if (response.success) {
                        // Display the single ticket
                        WPTracDashboard.displaySingleTicket(response.data);
                    } else {
                        WPTracDashboard.showError(response.data.message);
                    }
                },
                error: function() {
                    WPTracDashboard.showError(wpTracDashboard.strings.error);
                },
                complete: function() {
                    WPTracDashboard.hideLoading();
                }
            });
        },
        
        extractTicketIdFromUrl: function(url) {
            // Match patterns like:
            // https://core.trac.wordpress.org/ticket/12345
            // https://core.trac.wordpress.org/ticket/12345?action=diff
            var match = url.match(/\/ticket\/(\d+)/);
            return match ? match[1] : null;
        },
        
        showTicketDetails: function(e) {
            e.preventDefault();
            
            var ticketId = $(this).closest('.wp-trac-ticket').data('ticket-id');
            
            WPTracDashboard.showLoading();
            
            $.ajax({
                url: wpTracDashboard.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wp_trac_dashboard_get_ticket_details',
                    nonce: wpTracDashboard.nonce,
                    ticket_id: ticketId
                },
                success: function(response) {
                    if (response.success) {
                        WPTracDashboard.displayTicketDetails(response.data);
                    } else {
                        WPTracDashboard.showError(response.data.message);
                    }
                },
                error: function() {
                    WPTracDashboard.showError(wpTracDashboard.strings.error);
                },
                complete: function() {
                    WPTracDashboard.hideLoading();
                }
            });
        },
        
        displayTickets: function(data) {
            var $container = $('.wp-trac-tickets-list');
            var template = $('#ticket-template').html();
            
            if (!data.tickets || data.tickets.length === 0) {
                $('#no-tickets').show();
                $container.empty();
                return;
            }
            
            $('#no-tickets').hide();
            $container.empty();
            
            data.tickets.forEach(function(ticket) {
                var html = template
                    .replace(/\{\{id\}\}/g, ticket.id)
                    .replace(/\{\{summary\}\}/g, ticket.summary)
                    .replace(/\{\{status\}\}/g, ticket.status)
                    .replace(/\{\{component\}\}/g, ticket.component)
                    .replace(/\{\{priority\}\}/g, ticket.priority)
                    .replace(/\{\{created\}\}/g, ticket.created)
                    .replace(/\{\{description\}\}/g, ticket.description)
                    .replace(/\{\{url\}\}/g, ticket.url || '#');
                
                $container.append(html);
            });
            
            // Show total count
            if (data.total > 0) {
                $('.wrap h1').after('<div class="notice notice-info"><p>Found ' + data.total + ' tickets</p></div>');
            }
        },
        
        displaySingleTicket: function(ticket) {
            var $container = $('.wp-trac-tickets-list');
            var template = $('#ticket-template').html();
            
            $('#no-tickets').hide();
            $container.empty();
            
            var html = template
                .replace(/\{\{id\}\}/g, ticket.id)
                .replace(/\{\{summary\}\}/g, ticket.summary)
                .replace(/\{\{status\}\}/g, ticket.status)
                .replace(/\{\{component\}\}/g, ticket.component)
                .replace(/\{\{priority\}\}/g, ticket.priority)
                .replace(/\{\{created\}\}/g, ticket.created)
                .replace(/\{\{description\}\}/g, ticket.description)
                .replace(/\{\{url\}\}/g, ticket.url || '#');
            
            $container.append(html);
            
            // Show single ticket message
            $('.wrap h1').after('<div class="notice notice-info"><p>Displaying ticket #' + ticket.id + '</p></div>');
        },
        
        displayTicketDetails: function(ticket) {
            $('#modal-ticket-title').text('#' + ticket.id + ' - ' + ticket.summary);
            
            var content = '<div class="ticket-details">';
            content += '<div class="ticket-meta-details">';
            content += '<p><strong>Status:</strong> <span class="status-' + ticket.status + '">' + ticket.status + '</span></p>';
            content += '<p><strong>Priority:</strong> <span class="priority-' + ticket.priority + '">' + ticket.priority + '</span></p>';
            content += '<p><strong>Component:</strong> ' + ticket.component + '</p>';
            content += '<p><strong>Created:</strong> ' + ticket.created + '</p>';
            content += '</div>';
            
            content += '<div class="ticket-description-full">';
            content += '<h3>Description</h3>';
            content += '<div>' + ticket.description + '</div>';
            content += '</div>';
            
            if (ticket.comments && ticket.comments.length > 0) {
                content += '<div class="ticket-comments">';
                content += '<h3>Comments</h3>';
                ticket.comments.forEach(function(comment) {
                    content += '<div class="comment">';
                    content += '<div class="comment-meta">';
                    content += '<strong>' + comment.author + '</strong> - ' + comment.date;
                    content += '</div>';
                    content += '<div class="comment-content">' + comment.content + '</div>';
                    content += '</div>';
                });
                content += '</div>';
            }
            
            content += '</div>';
            
            $('#modal-ticket-content').html(content);
            $('#ticket-modal').show();
        },
        
        displayTimeline: function(data) {
            var $container = $('#recent-timeline');
            var template = $('#timeline-template').html();
            
            $container.empty();
            
            if (data.events && data.events.length > 0) {
                data.events.forEach(function(event) {
                    var html = template
                        .replace(/\{\{time\}\}/g, event.time)
                        .replace(/\{\{action\}\}/g, event.action)
                        .replace(/\{\{link\}\}/g, event.link);
                    
                    $container.append(html);
                });
            } else {
                $container.html('<p>No recent activity</p>');
            }
        },
        
        populateComponents: function(components) {
            var $select = $('#component-filter');
            
            components.forEach(function(component) {
                $select.append('<option value="' + component + '">' + component + '</option>');
            });
        },
        
        showModal: function() {
            $('#ticket-modal').show();
        },
        
        hideModal: function() {
            $('#ticket-modal').hide();
        },
        
        showLoading: function() {
            $('#loading-indicator').show();
        },
        
        hideLoading: function() {
            $('#loading-indicator').hide();
        },
        
        showError: function(message) {
            var notice = '<div class="notice notice-error"><p>' + message + '</p></div>';
            $('.wrap h1').after(notice);
            
            setTimeout(function() {
                $('.notice').fadeOut();
            }, 5000);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        WPTracDashboard.init();
    });
    
})(jQuery); 