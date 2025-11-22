/**
 * FILE: /assets/js/admin/funds_maintenance.js
 * ============================================================
 * HealthRunCare Admin Funds Maintenance.js
 * Purpose: Frontend logic for the Admin Maintenance Fund Management page.
 * Handles: Metrics, Plan CRUD, Active/Completed User Tasks.
 * Assumes global utility functions (fetchApi, formatCurrency, showToast, showModal, closeModal) are available.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for tables and search
    let currentActivePage = 1;
    let currentCompletedPage = 1;
    let currentSearchTerm = '';
    
    // --- UI Helpers ---

    /** Renders a status badge for Maintenance Plans/Tasks. */
    function renderStatusBadge(status) {
        status = status.toLowerCase();

        let badgeClass = 'bg-Gray';
        if (status === 'active') {
            badgeClass = 'bg-Primary';
        } else if (status === 'matured' || status === 'completed') {
            badgeClass = 'bg-Green';
        } else if (status === 'unlocked' || status === 'expired') {
            badgeClass = 'bg-Red';
        }

        const displayStatus = status.replace('-', ' ');

        return `<div class="box-status ${badgeClass}"><span class="font-poppins key-sort">${displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1)}</span></div>`;
    }

    // --- Core Data Fetcher & UI Renderer ---

    /**
     * Loads all data for the Maintenance dashboard (Metrics, Plans, Active/Completed Tasks).
     */
    async function loadMaintenanceDashboard() {
        // Show loading indicators
        const plansBody = $('#maintenance-plans-body');
        const activeBody = $('#active-maintenance-body');
        const completedBody = $('#completed-maintenance-body');
        
        plansBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading plans...</td></tr>');
        activeBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading active tasks...</td></tr>');
        completedBody.empty().html('<tr><td colspan="5" class="text-center text-Primary f14-regular">Loading completed tasks...</td></tr>');
        $('#active-maintenance-pagination').empty();
        $('#completed-maintenance-pagination').empty();

        try {
            const res = await fetchApi('/api/admin/funds_maintenance.php', {
                search: currentSearchTerm,
                active_page: currentActivePage,
                completed_page: currentCompletedPage
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load dashboard data.', 'error');
                plansBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading plans.</td></tr>');
                activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading active tasks.</td></tr>');
                completedBody.html('<tr><td colspan="5" class="text-center text-Red f14-regular">Error loading completed tasks.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderMaintenancePlansTable(data.plans);
            renderActiveMaintenanceTasksTable(data.active_tasks);
            renderCompletedMaintenanceTasksTable(data.completed_tasks);
            renderPagination('active-maintenance-pagination', data.active_page, data.active_total_pages, 'active');
            renderPagination('completed-maintenance-pagination', data.completed_page, data.completed_total_pages, 'completed');
            
            if (typeof window.counter === 'function') {
                window.counter();
            }

        } catch (error) {
            console.error('API Error loading maintenance dashboard:', error);
            window.showToast('A network error occurred while fetching data.', 'error');
            plansBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            completedBody.html('<tr><td colspan="5" class="text-center text-Red f14-regular">Network error.</td></tr>');
        }
    }

    /**
     * Updates the summary cards.
     */
    function updateMetrics(m) {
        if (!m) return;
        const format = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        $('#total-maintenance').text(format(m.total_maintenance ?? 0));
        $('#active-tasks').text(m.active_tasks ?? 0);
        $('#total-spent').text(format(m.total_spent ?? 0)); 
        $('#next-scheduled').text(m.next_scheduled ?? '—');
    }

    /**
     * Renders the Maintenance Plans table (Table 1).
     */
    function renderMaintenancePlansTable(plans) {
        const tableBody = $('#maintenance-plans-body');
        tableBody.empty();

        if (!plans || plans.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No Maintenance Plans defined.</td></tr>');
            return;
        }

        plans.forEach(plan => {
            const row = `
                <tr data-plan-id="${plan.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Plan Name">
                        <span class="f14-bold text-Primary">${plan.name}</span>
                    </td>
                    <td class="f14-regular" data-label="Min Amount">$${window.formatCurrency(plan.min_amount)}</td>
                    <td class="f14-regular" data-label="Duration">${plan.duration_days} days</td>
                    <td class="f14-regular" data-label="ROI %">${plan.roi_percent}%</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(plan.status)}</td>
                    <td class="f14-regular" data-label="Actions">
                        <div class="dropdown default style-fill actions-dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item action-edit-maintenance-plan" data-id="${plan.id}">
                                        Edit Plan
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });
    }
    
    /**
     * Renders the Active Maintenance Tasks table (Table 2).
     */
    function renderActiveMaintenanceTasksTable(tasks) {
        const tableBody = $('#active-maintenance-body');
        tableBody.empty();

        if (!tasks || tasks.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No active maintenance tasks found.</td></tr>');
            return;
        }

        tasks.forEach(a => {
            const row = `
                <tr data-task-id="${a.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Plan">
                        <span class="f14-bold text-Primary">${a.plan_name}</span>
                    </td>
                    <td class="f14-regular" data-label="User">${a.user_name}</td>
                    <td class="f14-bold" data-label="Amount">$${window.formatCurrency(a.amount)}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(a.status)}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }

    /**
     * Renders the Completed/Archived Tasks table (Table 3).
     */
    function renderCompletedMaintenanceTasksTable(tasks) {
        const tableBody = $('#completed-maintenance-body');
        tableBody.empty();

        if (!tasks || tasks.length === 0) {
            tableBody.html('<tr><td colspan="5" class="text-center text-Gray f14-regular">No completed/archived maintenance tasks found.</td></tr>');
            return;
        }

        tasks.forEach(a => {
            const row = `
                <tr data-task-id="${a.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Plan">
                        <span class="f14-bold">${a.plan_name}</span>
                    </td>
                    <td class="f14-regular" data-label="User">${a.user_name}</td>
                    <td class="f14-bold" data-label="Amount">$${window.formatCurrency(a.amount)}</td>
                    <td class="f14-regular" data-label="Completed On">${a.completed_on}</td>
                    <td class="f14-regular text-Green" data-label="Earnings">$${window.formatCurrency(a.roi_earned)}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }

    /**
     * Renders generic pagination.
     */
    function renderPagination(elId, currentPage, totalPages, type) {
        const paginationEl = $(`#${elId}`);
        paginationEl.empty();
        if (totalPages <= 1) return;

        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'bg-Primary text-White' : 'bg-GrayLight text-Black';
            paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link-${type} ${activeClass}" data-page="${i}">${i}</button>`);
        }
        
        // Bind click events
        paginationEl.find(`.page-link-${type}`).off('click').on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('bg-Primary')) return; 
            const newPage = $(this).data('page');
            
            if (type === 'active') {
                currentActivePage = newPage;
            } else if (type === 'completed') {
                currentCompletedPage = newPage;
            }
            loadMaintenanceDashboard(); 
        });
    }

    // --- Modal Logic ---

    /** Prepares the modal for a new plan entry. */
    function setupAddPlanModal() {
        $('#maintenance-plan-title').text('Add New Maintenance Plan');
        $('#maintenance-plan-form')[0].reset();
        $('#maintenance-plan-id').val(''); 
        $('#plan-min-amount').val(''); 
        $('#plan-max-amount').val(''); 
        $('#plan-duration-days').val(''); 
        $('#plan-roi-percent').val(''); 
        $('#plan-summary').val(''); 
        $('.modal-confirm-btn').text('Save Plan').removeClass('bg-Accent text-Black').addClass('bg-Primary text-White');
        
        window.showModal('#maintenance-plan-modal');
    }

    /** Fetches plan data and populates the Edit Plan modal. */
    async function setupEditPlanModal(id) {
        try {
            window.showToast('Loading plan details...', 'info');
            const res = await fetchApi('/api/admin/funds_maintenance.php', {
                fetch: 'plan_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const plan = res.data;
                
                $('#maintenance-plan-title').text(`Edit Plan: ${plan.name}`);
                $('#maintenance-plan-id').val(plan.id);
                $('#plan-name').val(plan.name);
                $('#plan-min-amount').val(plan.min_amount);
                $('#plan-max-amount').val(plan.max_amount);
                $('#plan-duration-days').val(plan.duration_days);
                $('#plan-roi-percent').val(plan.roi_percent);
                $('#plan-summary').val(plan.summary);
                
                $('.modal-confirm-btn').text('Update Plan').removeClass('bg-Primary text-White').addClass('bg-Accent text-Black');
                window.showModal('#maintenance-plan-modal');
                window.showToast('Plan details loaded.', 'success');
            } else {
                window.showToast(res.message || 'Failed to load plan details for editing.', 'error');
            }
        } catch (error) {
            console.error('Error loading plan details:', error);
            window.showToast('Network error while fetching details.', 'error');
        }
    }


    // --- Interaction Binding ---
    function bindInteractions() {
        
        // --- Plan Management (Table 1) ---
        
        // 1. Add Plan Button
        $('#add-maintenance-plan-btn').on('click', function() {
            setupAddPlanModal();
        });
        
        // 2. Edit Plan Button (Delegated click handler for the Plans table)
        $(document).on('click', '.action-edit-maintenance-plan', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            setupEditPlanModal(id);
        });
        
        // 3. Plan Form Submission (Add/Edit Plan)
        $('#maintenance-plan-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#maintenance-plan-id').val();
            const isEdit = !!id;
            const action = isEdit ? 'edit_plan' : 'add_plan';
            
            const payload = {
                action: action,
                id: id,
                name: $('#plan-name').val(),
                min_amount: parseFloat($('#plan-min-amount').val()),
                max_amount: parseFloat($('#plan-max-amount').val()),
                duration_days: parseInt($('#plan-duration-days').val()),
                roi_percent: parseFloat($('#plan-roi-percent').val()),
                summary: $('#plan-summary').val()
            };

            // Basic client-side validation
            if (!payload.name || payload.min_amount <= 0 || payload.duration_days <= 0 || payload.roi_percent <= 0) {
                 window.showToast('Please ensure all required Plan fields are valid.', 'error');
                 return;
            }

            window.showToast(`${isEdit ? 'Updating' : 'Creating'} Maintenance Plan...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds_maintenance.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                window.closeModal('#maintenance-plan-modal');
                // Reset to page 1 to see the new/updated plan immediately
                currentActivePage = 1; 
                currentCompletedPage = 1;
                loadMaintenanceDashboard(); 
            } else {
                window.showToast(res.message || `${isEdit ? 'Update' : 'Creation'} failed.`, 'error');
            }
        });

        // --- Task Management (Status Updates - Table 2) ---

        // 4. Update Task Status
        $(document).on('click', '.action-task-status-update', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const taskId = $(this).data('id');
            const newStatus = $(this).data('status'); 
            const taskName = $(this).data('name');

            if (!confirm(`Are you sure you want to change the status of task "${taskName}" to "${newStatus.toUpperCase()}"?`)) return;
            
            const payload = {
                action: 'update_task_status',
                task_id: taskId,
                status: newStatus
            };

            window.showToast(`Updating task status to ${newStatus}...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds_maintenance.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                loadMaintenanceDashboard(); 
            } else {
                window.showToast(res.message || `Status update failed.`, 'error');
            }
        });
    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        loadMaintenanceDashboard(); 

        window.refreshMaintenanceDashboard = loadMaintenanceDashboard;
    });

})(jQuery);