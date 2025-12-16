/**
 * Accessibility Scanner Admin JavaScript
 *
 * Handles all AJAX operations and interactive features for the admin interface
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\AccessibilityScanner
 * @version    1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Main Accessibility Scanner object
     */
    const ShahiA11y = {
        
        /**
         * Initialize the scanner
         */
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initCharts();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Scan controls
            $('#shahi-a11y-run-scan').on('click', this.runScan.bind(this));
            $('#shahi-a11y-scan-url').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    ShahiA11y.runScan(e);
                }
            });
            
            // Issue actions
            $(document).on('click', '.shahi-a11y-apply-fix', this.applyFix.bind(this));
            $(document).on('click', '.shahi-a11y-ignore-issue', this.ignoreIssue.bind(this));
            $(document).on('click', '.shahi-a11y-view-details', this.viewDetails.bind(this));
            
            // Export actions
            $('#shahi-a11y-export-report').on('click', this.exportReport.bind(this));
            
            // Filter controls
            $('.shahi-a11y-filter-select').on('change', this.applyFilters.bind(this));
            $('#shahi-a11y-search-issues').on('input', this.searchIssues.bind(this));
            
            // Settings
            $('#shahi-a11y-save-settings').on('click', this.saveSettings.bind(this));
            $('#shahi-a11y-reset-settings').on('click', this.resetSettings.bind(this));
            
            // Bulk actions
            $('#shahi-a11y-bulk-action-apply').on('click', this.applyBulkAction.bind(this));
            $('#shahi-a11y-select-all-issues').on('change', this.toggleSelectAll.bind(this));
        },
        
        /**
         * Run accessibility scan
         */
        runScan: function(e) {
            e.preventDefault();
            
            const $btn = $('#shahi-a11y-run-scan');
            const $urlInput = $('#shahi-a11y-scan-url');
            const url = $urlInput.val().trim();
            
            // Validate URL
            if (!url) {
                this.showNotification(shahiA11y.strings.scanFailed, 'error');
                return;
            }
            
            // Update button state
            $btn.prop('disabled', true).html('<span class="shahi-a11y-spinner"></span> ' + shahiA11y.strings.scanRunning);
            
            // Show progress bar
            this.showProgressBar();
            
            // Make AJAX request
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_run_scan',
                    nonce: shahiA11y.nonce,
                    url: url,
                    scan_depth: $('#shahi-a11y-scan-depth').val() || 'full'
                },
                success: function(response) {
                    if (response.success) {
                        ShahiA11y.displayScanResults(response.data);
                        ShahiA11y.showNotification(shahiA11y.strings.scanComplete, 'success');
                    } else {
                        ShahiA11y.showNotification(response.data.message || shahiA11y.strings.scanFailed, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Scan error:', error);
                    ShahiA11y.showNotification(shahiA11y.strings.scanFailed, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Run Scan');
                    ShahiA11y.hideProgressBar();
                }
            });
        },
        
        /**
         * Apply a fix to an issue
         */
        applyFix: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const issueId = $btn.data('issue-id');
            const $row = $btn.closest('tr');
            
            // Confirm action
            if (!confirm('Are you sure you want to apply this fix?')) {
                return;
            }
            
            // Update button state
            $btn.prop('disabled', true).html('<span class="shahi-a11y-spinner"></span> ' + shahiA11y.strings.fixApplying);
            
            // Make AJAX request
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_apply_fix',
                    nonce: shahiA11y.nonce,
                    issue_id: issueId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            ShahiA11y.updateStats();
                        });
                        ShahiA11y.showNotification(shahiA11y.strings.fixApplied, 'success');
                    } else {
                        ShahiA11y.showNotification(response.data.message || shahiA11y.strings.fixFailed, 'error');
                        $btn.prop('disabled', false).text('Apply Fix');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Fix error:', error);
                    ShahiA11y.showNotification(shahiA11y.strings.fixFailed, 'error');
                    $btn.prop('disabled', false).text('Apply Fix');
                }
            });
        },
        
        /**
         * Ignore an issue
         */
        ignoreIssue: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const issueId = $btn.data('issue-id');
            const $row = $btn.closest('tr');
            
            // Make AJAX request
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_ignore_issue',
                    nonce: shahiA11y.nonce,
                    issue_id: issueId,
                    reason: 'User ignored via admin'
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        ShahiA11y.showNotification(shahiA11y.strings.issueIgnored, 'success');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ignore error:', error);
                }
            });
        },
        
        /**
         * View issue details
         */
        viewDetails: function(e) {
            e.preventDefault();
            
            const issueId = $(e.currentTarget).data('issue-id');
            
            // Fetch issue details via AJAX
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_get_issue_details',
                    nonce: shahiA11y.nonce,
                    issue_id: issueId
                },
                success: function(response) {
                    if (response.success) {
                        ShahiA11y.showDetailsModal(response.data);
                    }
                }
            });
        },
        
        /**
         * Export scan report
         */
        exportReport: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const scanId = $btn.data('scan-id');
            const format = $('#shahi-a11y-export-format').val() || 'pdf';
            
            $btn.prop('disabled', true).html('<span class="shahi-a11y-spinner"></span> ' + shahiA11y.strings.reportExporting);
            
            // Make AJAX request
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_export_report',
                    nonce: shahiA11y.nonce,
                    scan_id: scanId,
                    format: format
                },
                success: function(response) {
                    if (response.success) {
                        // Download file
                        window.location.href = response.data.download_url;
                        ShahiA11y.showNotification(shahiA11y.strings.reportExported, 'success');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Export Report');
                }
            });
        },
        
        /**
         * Save settings
         */
        saveSettings: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $form = $('#shahi-a11y-settings-form');
            
            // Serialize form data
            const settings = {};
            $form.find('input, select, textarea').each(function() {
                const $field = $(this);
                const name = $field.attr('name');
                
                if (!name) return;
                
                // Parse nested field names (e.g., "shahi_a11y_settings[general][wcag_level]")
                const matches = name.match(/\[([^\]]+)\]/g);
                if (matches && matches.length >= 2) {
                    const section = matches[0].replace(/[\[\]]/g, '');
                    const key = matches[1].replace(/[\[\]]/g, '');
                    
                    if (!settings[section]) {
                        settings[section] = {};
                    }
                    
                    if ($field.attr('type') === 'checkbox') {
                        settings[section][key] = $field.is(':checked');
                    } else {
                        settings[section][key] = $field.val();
                    }
                }
            });
            
            $btn.prop('disabled', true).text('Saving...');
            
            // Make AJAX request
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_save_settings',
                    nonce: shahiA11y.nonce,
                    settings: settings
                },
                success: function(response) {
                    if (response.success) {
                        ShahiA11y.showNotification(shahiA11y.strings.settingsSaved, 'success');
                    } else {
                        ShahiA11y.showNotification(response.data.message || shahiA11y.strings.settingsFailed, 'error');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Settings');
                }
            });
        },
        
        /**
         * Reset settings to defaults
         */
        resetSettings: function(e) {
            e.preventDefault();
            
            if (!confirm(shahiA11y.strings.confirmReset)) {
                return;
            }
            
            const $btn = $(e.currentTarget);
            $btn.prop('disabled', true).text('Resetting...');
            
            // Make AJAX request
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_reset_settings',
                    nonce: shahiA11y.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Reset to Defaults');
                }
            });
        },
        
        /**
         * Apply filters to issues table
         */
        applyFilters: function() {
            const severity = $('#shahi-a11y-filter-severity').val();
            const wcagLevel = $('#shahi-a11y-filter-wcag-level').val();
            const status = $('#shahi-a11y-filter-status').val();
            
            $('.shahi-a11y-issues-table tbody tr').each(function() {
                const $row = $(this);
                let show = true;
                
                if (severity && severity !== 'all') {
                    show = show && $row.data('severity') === severity;
                }
                
                if (wcagLevel && wcagLevel !== 'all') {
                    show = show && $row.data('wcag-level') === wcagLevel;
                }
                
                if (status && status !== 'all') {
                    show = show && $row.data('status') === status;
                }
                
                $row.toggle(show);
            });
            
            this.updateFilterCount();
        },
        
        /**
         * Search issues
         */
        searchIssues: function(e) {
            const query = $(e.currentTarget).val().toLowerCase();
            
            $('.shahi-a11y-issues-table tbody tr').each(function() {
                const $row = $(this);
                const text = $row.text().toLowerCase();
                $row.toggle(text.indexOf(query) > -1);
            });
        },
        
        /**
         * Apply bulk action
         */
        applyBulkAction: function(e) {
            e.preventDefault();
            
            const action = $('#shahi-a11y-bulk-action').val();
            const selectedIds = [];
            
            $('.shahi-a11y-issue-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                this.showNotification('Please select at least one issue', 'warning');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm('Are you sure you want to delete selected issues?')) {
                    return;
                }
            }
            
            // Process bulk action
            this.processBulkAction(action, selectedIds);
        },
        
        /**
         * Toggle select all checkboxes
         */
        toggleSelectAll: function(e) {
            const checked = $(e.currentTarget).is(':checked');
            $('.shahi-a11y-issue-checkbox').prop('checked', checked);
        },
        
        /**
         * Display scan results
         */
        displayScanResults: function(data) {
            const $container = $('#shahi-a11y-scan-results');
            
            if (!$container.length) {
                return;
            }
            
            // Update statistics
            $('#shahi-a11y-total-issues').text(data.total_issues || 0);
            $('#shahi-a11y-critical-issues').text(data.critical_issues || 0);
            $('#shahi-a11y-score').text(data.score || 0);
            
            // Update issues table
            if (data.issues && data.issues.length > 0) {
                this.populateIssuesTable(data.issues);
            }
            
            // Show results container
            $container.slideDown();
        },
        
        /**
         * Populate issues table
         */
        populateIssuesTable: function(issues) {
            const $tbody = $('.shahi-a11y-issues-table tbody');
            $tbody.empty();
            
            issues.forEach(function(issue) {
                const $row = $('<tr>')
                    .attr('data-severity', issue.severity)
                    .attr('data-wcag-level', issue.wcag_level)
                    .attr('data-status', issue.status);
                
                $row.append(
                    $('<td>').html('<input type="checkbox" class="shahi-a11y-issue-checkbox" value="' + issue.id + '">'),
                    $('<td>').html('<span class="shahi-a11y-severity shahi-a11y-severity-' + issue.severity + '">' + issue.severity + '</span>'),
                    $('<td>').text(issue.check_name),
                    $('<td>').text(issue.element_selector || 'N/A'),
                    $('<td>').html('<span class="shahi-a11y-wcag-level shahi-a11y-wcag-level-' + issue.wcag_level.toLowerCase() + '">' + issue.wcag_criterion + '</span>'),
                    $('<td>').html(ShahiA11y.buildActionButtons(issue))
                );
                
                $tbody.append($row);
            });
        },
        
        /**
         * Build action buttons for issue row
         */
        buildActionButtons: function(issue) {
            let html = '<div class="shahi-a11y-action-buttons">';
            
            if (issue.can_auto_fix) {
                html += '<button class="shahi-a11y-btn shahi-a11y-btn-fix shahi-a11y-apply-fix" data-issue-id="' + issue.id + '">Fix</button>';
            }
            
            html += '<button class="shahi-a11y-btn shahi-a11y-btn-details shahi-a11y-view-details" data-issue-id="' + issue.id + '">Details</button>';
            html += '<button class="shahi-a11y-btn shahi-a11y-btn-ignore shahi-a11y-ignore-issue" data-issue-id="' + issue.id + '">Ignore</button>';
            html += '</div>';
            
            return html;
        },
        
        /**
         * Show details modal
         */
        showDetailsModal: function(issue) {
            // Create modal content
            const modalHtml = `
                <div class="shahi-a11y-modal-overlay">
                    <div class="shahi-a11y-modal">
                        <div class="shahi-a11y-modal-header">
                            <h2>${issue.check_name}</h2>
                            <button class="shahi-a11y-modal-close">&times;</button>
                        </div>
                        <div class="shahi-a11y-modal-body">
                            <div class="shahi-a11y-modal-section">
                                <h3>Issue Description</h3>
                                <p>${issue.issue_description}</p>
                            </div>
                            <div class="shahi-a11y-modal-section">
                                <h3>Recommendation</h3>
                                <p>${issue.recommendation}</p>
                            </div>
                            <div class="shahi-a11y-modal-section">
                                <h3>Element</h3>
                                <pre><code>${this.escapeHtml(issue.element_html)}</code></pre>
                            </div>
                            <div class="shahi-a11y-modal-section">
                                <h3>WCAG Criterion</h3>
                                <p>${issue.wcag_criterion} - Level ${issue.wcag_level}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Append modal to body
            $('body').append(modalHtml);
            
            // Bind close event
            $('.shahi-a11y-modal-close, .shahi-a11y-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    $('.shahi-a11y-modal-overlay').remove();
                }
            });
        },
        
        /**
         * Show notification
         */
        showNotification: function(message, type) {
            type = type || 'info';
            
            const $notification = $('<div>')
                .addClass('shahi-a11y-notification shahi-a11y-notification-' + type)
                .text(message);
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('show');
            }, 10);
            
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        },
        
        /**
         * Show progress bar
         */
        showProgressBar: function() {
            const $progress = $('#shahi-a11y-progress');
            if ($progress.length) {
                $progress.show();
                this.animateProgress();
            }
        },
        
        /**
         * Hide progress bar
         */
        hideProgressBar: function() {
            $('#shahi-a11y-progress').hide();
        },
        
        /**
         * Animate progress bar
         */
        animateProgress: function() {
            let progress = 0;
            const $fill = $('.shahi-a11y-progress-fill');
            
            const interval = setInterval(function() {
                progress += Math.random() * 10;
                if (progress > 90) {
                    clearInterval(interval);
                    progress = 90;
                }
                $fill.css('width', progress + '%');
            }, 200);
        },
        
        /**
         * Update statistics
         */
        updateStats: function() {
            // Refresh stats via AJAX
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_get_stats',
                    nonce: shahiA11y.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#shahi-a11y-total-scans').text(response.data.total_scans || 0);
                        $('#shahi-a11y-total-issues').text(response.data.total_issues || 0);
                        $('#shahi-a11y-fixes-applied').text(response.data.fixes_applied || 0);
                    }
                }
            });
        },
        
        /**
         * Update filter count
         */
        updateFilterCount: function() {
            const visibleCount = $('.shahi-a11y-issues-table tbody tr:visible').length;
            const totalCount = $('.shahi-a11y-issues-table tbody tr').length;
            
            $('#shahi-a11y-filter-count').text('Showing ' + visibleCount + ' of ' + totalCount + ' issues');
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Use WordPress built-in tooltips or custom implementation
            if (typeof jQuery.fn.tooltip !== 'undefined') {
                $('[data-tooltip]').tooltip();
            }
        },
        
        /**
         * Initialize charts (using Chart.js if available)
         */
        initCharts: function() {
            if (typeof Chart === 'undefined' || !$('#shahi-a11y-chart').length) {
                return;
            }
            
            // Initialize accessibility score chart
            this.initScoreChart();
            this.initTrendChart();
        },
        
        /**
         * Initialize score chart
         */
        initScoreChart: function() {
            const ctx = document.getElementById('shahi-a11y-chart');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Passed', 'Failed', 'Warnings'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: ['#2ecc71', '#ff4757', '#f1c40f']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        },
        
        /**
         * Initialize trend chart
         */
        initTrendChart: function() {
            // Implementation for trend chart
        },
        
        /**
         * Process bulk action
         */
        processBulkAction: function(action, issueIds) {
            $.ajax({
                url: shahiA11y.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'shahi_a11y_bulk_action',
                    nonce: shahiA11y.nonce,
                    bulk_action: action,
                    issue_ids: issueIds
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        },
        
        /**
         * Escape HTML for display
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        ShahiA11y.init();
    });
    
    /**
     * Export to global scope for external access
     */
    window.ShahiA11y = ShahiA11y;
    
})(jQuery);
