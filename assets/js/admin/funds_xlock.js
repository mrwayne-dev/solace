/**
 * FILE: /assets/js/admin/funds_xlock.js
 * ============================================================
 * TitanXHoldings Admin Funds Holdlock.js
 * Purpose: Frontend logic for the Admin X-Lock Savings Management page.
 * Handles: Metrics, X-Lock Plan CRUD, Active X-Lock list/pagination/edit.
 * Assumes global utility functions (fetchApi, formatCurrency, showToast, showModal, closeModal) are available.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for tables and search
    let currentActivePage = 1;
    let currentSearchTerm = '';
    
    // --- UI Helpers ---

    /** Renders a status badge for X-Lock (using statuses from the 'holdlock' table). */
    function renderStatusBadge(status) {
        status = status.toLowerCase();

        let badgeClass = 'bg-Gray';
        if (status === 'locked' || status === 'active') {
            badgeClass = 'bg-Green';
        } else if (status === 'matured' || status === 'completed') {
            badgeClass = 'bg-Primary';
        } else if (status === 'unlock_pending') {
            badgeClass = 'bg-Accent text-Black';
        } else if (status === 'unlocked_early' || status === 'inactive') {
            badgeClass = 'bg-Orange';
        }

        // Replace underscores for display
        const displayStatus = status.replace('_', ' ');

        return `<div class="box-status ${badgeClass}"><span class="font-poppins key-sort">${displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1)}</span></div>`;
    }
    
    // --- Core Data Fetcher & UI Renderer ---

    /**
     * Loads all data for the funds dashboard (Metrics, Plans, Active X-Lock).
     */
    async function loadHoldlockDashboard() {
        // Show loading indicators
        const plansBody = $('#holdlock-plans-body');
        const activeBody = $('#active-holdlock-body');
        plansBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading plans...</td></tr>');
        activeBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading active savings...</td></tr>');
        $('#active-holdlock-pagination').empty();

        try {
            const res = await fetchApi('/api/admin/funds_xlock.php', {
                search: currentSearchTerm,
                active_page: currentActivePage
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load dashboard data.', 'error');
                plansBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading plans.</td></tr>');
                activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading savings.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderHoldlockPlansTable(data.plans);
            renderActiveHoldlockTable(data.active_holdlock);
            renderActiveHoldlockPagination(data.active_page, data.active_total_pages);
            
            if (typeof window.counter === 'function') {
                window.counter();
            }

        } catch (error) {
            console.error('API Error loading holdlock dashboard:', error);
            window.showToast('A network error occurred while fetching data.', 'error');
            plansBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
        }
    }

    /**
     * Updates the summary cards.
     */
    function updateMetrics(m) {
        if (!m) return;
        const format = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        $('#total-holdlock').text(format(m.total_holdlock ?? 0));
        $('#holdlock-users').text(m.holdlock_users ?? 0);
        $('#total-interest').text(format(m.total_interest ?? 0)); 
        $('#next-unlock').text(m.next_unlock ?? '—');
    }
    
    /**
     * Renders the X-Lock Plans table.
     */
    function renderHoldlockPlansTable(plans) {
        const tableBody = $('#holdlock-plans-body');
        tableBody.empty();

        if (!plans || plans.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No X-Lock plans defined.</td></tr>');
            return;
        }

        plans.forEach(plan => {
            const row = `
                <tr data-plan-id="${plan.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Plan Name">
                        <span class="f14-bold text-Primary">${plan.name}</span>
                    </td>
                    <td class="f14-regular" data-label="Lock Period">${plan.lock_period_text} (${plan.duration_days} days)</td>
                    <td class="f14-regular" data-label="Interest Rate"><span class="text-Green">${plan.roi_range}</span></td>
                    <td class="f14-regular" data-label="Min Amount">$${window.formatCurrency(plan.min_amount)}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(plan.status)}</td>
                    <td class="f14-regular" data-label="Actions">
                        <div class="dropdown default style-fill actions-dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item action-edit-holdlock-plan" data-id="${plan.id}">
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
     * Renders the Active X-Lock Savings table.
     */
    function renderActiveHoldlockTable(holdlocks) {
        const tableBody = $('#active-holdlock-body');
        tableBody.empty();

        if (!holdlocks || holdlocks.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No X-Lock savings found.</td></tr>');
            return;
        }

        holdlocks.forEach(h => {
            const row = `
                <tr data-holdlock-id="${h.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="User">
                        <span class="f14-bold">${h.user_name}</span>
                        <div class="f12-regular text-Gray">${h.user_email}</div>
                    </td>
                    <td class="f14-regular" data-label="Plan">${h.plan_name}</td>
                    <td class="f14-bold" data-label="Amount">$${window.formatCurrency(h.amount)}</td>
                    <td class="f14-regular text-Green" data-label="Interest">$${window.formatCurrency(h.roi_earned)} (${h.roi_percent}%)</td>
                    <td class="f14-regular text-Gray" data-label="Lock Until">${h.maturity_date}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(h.status)}</td>
                </tr>
            `;
            tableBody.append(row);
        });
        
        // Note: No actions column for active holdlocks based on the provided frontend HTML header structure.
    }
    
    /**
     * Renders the pagination for the Active X-Lock table.
     */
    function renderActiveHoldlockPagination(currentPage, totalPages) {
        const paginationEl = $('#active-holdlock-pagination');
        paginationEl.empty();
        if (totalPages <= 1) return;

        // Render Page Links
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'bg-Primary text-White' : 'bg-GrayLight text-Black';
            paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link ${activeClass}" data-page="${i}">${i}</button>`);
        }
        
        // Bind click events
        paginationEl.find('.page-link').on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('bg-Primary')) return; // Ignore click on active page
            const newPage = $(this).data('page');
            currentActivePage = newPage;
            loadHoldlockDashboard(); 
        });
    }

    // --- Modal Logic ---

    /** Prepares the modal for a new plan entry. */
    function setupAddPlanModal() {
        $('#holdlock-plan-title').text('Add New X-Lock Plan');
        $('#holdlock-plan-form')[0].reset();
        $('#holdlock-plan-id').val(''); 
        $('#holdlock-min-amount').val(0);
        $('#holdlock-plan-status').val('active');
        $('.modal-confirm-btn').text('Save Plan').removeClass('bg-Accent text-Black').addClass('bg-Primary text-White');
        window.showModal('#holdlock-plan-modal');
    }

    /** Fetches plan data and populates the Edit X-Lock Plan modal. */
    async function setupEditPlanModal(id) {
        try {
            window.showToast('Loading plan details...', 'info');
            const res = await fetchApi('/api/admin/funds_xlock.php', {
                fetch: 'plan_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const plan = res.data;
                
                $('#holdlock-plan-title').text(`Edit Plan: ${plan.name}`);
                $('#holdlock-plan-id').val(plan.id);
                $('#holdlock-plan-name').val(plan.name);
                $('#holdlock-min-amount').val(plan.min_amount);
                $('#holdlock-lock-days').val(plan.lock_days);
                $('#holdlock-interest-rate').val(plan.interest_rate);
                $('#holdlock-plan-status').val(plan.status.toLowerCase()); 
                
                $('.modal-confirm-btn').text('Update Plan').removeClass('bg-Primary text-White').addClass('bg-Accent text-Black');
                window.showModal('#holdlock-plan-modal');
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
        
        // 1. Add Plan Button
        $('#add-holdlock-plan-btn').on('click', function() {
            setupAddPlanModal();
        });
        
        // 2. Edit Plan Action Button (delegated click handler on plans table)
        $(document).on('click', '.action-edit-holdlock-plan', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            setupEditPlanModal(id);
        });
        
        // 3. Plan Form Submission (Add/Edit Plan)
        $('#holdlock-plan-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#holdlock-plan-id').val();
            const isEdit = !!id;
            const action = isEdit ? 'edit_plan' : 'add_plan';
            
            const payload = {
                action: action,
                id: id,
                name: $('#holdlock-plan-name').val(),
                duration: parseInt($('#holdlock-lock-days').val()),
                interest: parseFloat($('#holdlock-interest-rate').val()),
                min_amount: parseFloat($('#holdlock-min-amount').val()),
                // status: $('#holdlock-plan-status').val() // Omitted due to missing DB column
            };

            window.showToast(`${isEdit ? 'Updating' : 'Creating'} X-Lock plan...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds_xlock.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                window.closeModal('#holdlock-plan-modal');
                loadHoldlockDashboard(); 
            } else {
                window.showToast(res.message || `${isEdit ? 'Update' : 'Creation'} failed.`, 'error');
            }
        });

        // 4. Active Holdlock Pagination 
        $(document).on('click', '#active-holdlock-pagination .page-link', function(e) {
            e.preventDefault();
            const newPage = $(this).data('page');
            currentActivePage = newPage;
            loadHoldlockDashboard(); 
        });

    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        loadHoldlockDashboard(); 

        window.refreshHoldlockDashboard = loadHoldlockDashboard;
    });

})(jQuery);