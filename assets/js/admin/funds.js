/**
 * FILE: /assets/js/admin/funds.js
 * ============================================================
 * Solace Mining Admin Funds.js
 * Purpose: Frontend logic for the Admin Fund Management (XYields) page.
 * Handles: Metrics, XYield Plan CRUD, Active XYield list/pagination/edit.
 * Assumes global utility functions (fetchApi, formatCurrency, showToast, showModal, closeModal) are available.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for tables and search
    let currentActivePage = 1;
    let currentSearchTerm = '';
    
    // --- UI Helpers ---

    /** Renders a status badge. */
    function renderStatusBadge(status) {
        status = status.toLowerCase();

        let badgeClass = 'bg-Gray';
        if (status === 'active') {
            badgeClass = 'bg-Green';
        } else if (status === 'completed') {
            badgeClass = 'bg-Primary';
        } else if (status === 'hidden' || status === 'cancelled') {
            badgeClass = 'bg-Orange';
        }

        return `<div class="box-status ${badgeClass}"><span class="font-poppins key-sort">${status.charAt(0).toUpperCase() + status.slice(1)}</span></div>`;
    }
    
    // --- Core Data Fetcher & UI Renderer ---

    /**
     * Loads all data for the funds dashboard (Metrics, Plans, Active XYields).
     */
    async function loadFundsDashboard() {
        // Show loading indicators
        const plansBody = $('#plans-table-body');
        const activeBody = $('#active-investments-body');
        plansBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading plans...</td></tr>');
        activeBody.empty().html('<tr><td colspan="9" class="text-center text-Primary f14-regular">Loading active investments...</td></tr>');
        $('#active-pagination').empty();

        try {
            const res = await fetchApi('/api/admin/funds.php', {
                search: currentSearchTerm,
                active_page: currentActivePage
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load dashboard data.', 'error');
                plansBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading plans.</td></tr>');
                activeBody.html('<tr><td colspan="9" class="text-center text-Red f14-regular">Error loading investments.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderPlansTable(data.plans);
            renderActiveXYieldsTable(data.active_investments);
            renderActivePagination(data.active_page, data.active_total_pages);
            
            if (typeof window.counter === 'function') {
                window.counter();
            }

        } catch (error) {
            console.error('API Error loading funds dashboard:', error);
            window.showToast('A network error occurred while fetching data.', 'error');
            plansBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            activeBody.html('<tr><td colspan="9" class="text-center text-Red f14-regular">Network error.</td></tr>');
        }
    }

    /**
     * Updates the summary cards.
     */
    function updateMetrics(m) {
        if (!m) return;
        const format = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        $('#total-active-invest').text(format(m.total_active_invest ?? 0));
        $('#total-roi-paid').text(format(m.total_roi_paid ?? 0));
        $('#ongoing-plans').text(m.ongoing_plans_count ?? 0); 
        $('#next-maturity').text(m.next_maturity ?? '—');
    }
    
    /**
     * Renders the XYield Plans table.
     */
    function renderPlansTable(plans) {
        const tableBody = $('#plans-table-body');
        tableBody.empty();

        if (!plans || plans.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No investment plans defined.</td></tr>');
            return;
        }

        plans.forEach(plan => {
            const maxTxt = plan.max_amount != null ? '$' + window.formatCurrency(plan.max_amount) : 'Unlimited';
            const row = `
                <tr data-plan-id="${plan.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Plan">
                        <span class="f14-bold text-Primary">${plan.name}</span>
                    </td>
                    <td class="f14-regular" data-label="Daily Profit"><span class="text-Green">${plan.daily_profit_percent}%</span></td>
                    <td class="f14-regular" data-label="Duration">${plan.duration_days} days</td>
                    <td class="f14-regular" data-label="Deposit Range">$${window.formatCurrency(plan.min_amount)} – ${maxTxt}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(plan.status)}</td>
                    <td class="f14-regular" data-label="Actions">
                        <div class="dropdown default style-fill actions-dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item action-edit-plan" data-id="${plan.id}">
                                        Edit Plan
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item action-toggle-status" data-id="${plan.id}" data-status="${plan.status === 'active' ? 'hidden' : 'active'}">
                                        ${plan.status === 'active' ? 'Hide Plan' : 'Activate Plan'}
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
     * Renders the Active XYields table.
     */
    function renderActiveXYieldsTable(investments) {
        const tableBody = $('#active-investments-body');
        tableBody.empty();

        if (!investments || investments.length === 0) {
            tableBody.html('<tr><td colspan="9" class="text-center text-Gray f14-regular">No active investments found.</td></tr>');
            return;
        }

        investments.forEach(inv => {
            const termDisplay = inv.duration_days > 360 ? `${inv.duration_days / 365} Year(s)` : `${inv.duration_days} days`;
            const row = `
                <tr data-inv-id="${inv.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="User">
                        <span class="f14-bold">${inv.user_name}</span>
                        <div class="f12-regular text-Gray">${inv.user_email}</div>
                    </td>
                    <td class="f14-regular" data-label="Plan">${inv.plan_name}</td>
                    <td class="f14-bold" data-label="Amount">$${window.formatCurrency(inv.amount)}</td>
                    <td class="f14-regular" data-label="Daily Profit">${inv.daily_profit_percent}%</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(inv.status)}</td>
                    <td class="f14-regular text-Gray" data-label="Start Date">${inv.date_started}</td>
                    <td class="f14-regular text-Gray" data-label="End Date">${inv.maturity_date}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }
    
    /**
     * Renders the pagination for the Active XYields table.
     */
    function renderActivePagination(currentPage, totalPages) {
        const paginationEl = $('#active-pagination');
        paginationEl.empty();
        if (totalPages <= 1) return;

        // Render Page Links (similar to donations.js logic)
        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'bg-Primary text-White' : 'bg-GrayLight text-Black';
            // Use buttons with class page-link to attach event
            paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link ${activeClass}" data-page="${i}">${i}</button>`);
        }
        
        // Bind click events
        paginationEl.find('.page-link').on('click', function(e) {
            e.preventDefault();
            const newPage = $(this).data('page');
            currentActivePage = newPage;
            loadFundsDashboard(); 
        });
    }

    // --- Modal Logic ---

    /** Prepares the modal for a new plan entry. */
    function setupAddPlanModal() {
        $('#plan-modal-title').text('Add New Plan');
        $('#plan-form')[0].reset();
        $('#plan-id').val(''); 
        $('#plan-risk').val('low');
        $('#plan-status').val('active');
        $('.modal-confirm-btn').text('Save Plan').removeClass('bg-Accent text-Black').addClass('bg-Primary text-White');
        window.showModal('#plan-modal');
    }

    /** Fetches plan data and populates the Edit Plan modal. */
    async function setupEditPlanModal(id) {
        try {
            window.showToast('Loading plan details...', 'info');
            const res = await fetchApi('/api/admin/funds.php', {
                fetch: 'plan_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const plan = res.data;
                
                $('#plan-modal-title').text(`Edit Plan: ${plan.name}`);
                $('#plan-id').val(plan.id);
                $('#plan-name').val(plan.name);
                $('#plan-min').val(plan.min_amount);
                $('#plan-max').val(plan.max_amount);
                $('#plan-daily').val(plan.daily_profit_percent);
                $('#plan-referral').val(plan.referral_commission_percent);
                $('#plan-duration').val(plan.duration_days);
                $('#plan-status').val(plan.status.toLowerCase());
                
                $('.modal-confirm-btn').text('Update Plan').removeClass('bg-Primary text-White').addClass('bg-Accent text-Black');
                window.showModal('#plan-modal');
                window.showToast('Plan details loaded.', 'success');
            } else {
                window.showToast(res.message || 'Failed to load plan details for editing.', 'error');
            }
        } catch (error) {
            console.error('Error loading plan details:', error);
            window.showToast('Network error while fetching details.', 'error');
        }
    }

    /** Fetches investment data and populates the Edit XYield modal. */
    async function setupEditXYieldModal(id) {
        try {
            window.showToast('Loading investment details...', 'info');
            const res = await fetchApi('/api/admin/funds.php', {
                fetch: 'investment_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const inv = res.data;
                
                $('#inv-id').val(inv.id);
                $('#inv-user').val(inv.user_display);
                $('#inv-plan').val(inv.plan_name);
                $('#inv-amount').val(inv.amount);
                $('#inv-roi').val(inv.daily_profit_percent);
                $('#inv-status').val(inv.status.toLowerCase());

                // Enable/disable fields based on status
                const isFinal = inv.status.toLowerCase() !== 'active';
                $('#inv-amount').prop('disabled', isFinal);
                $('#inv-roi').prop('disabled', isFinal);
                
                window.showModal('#edit-investment-modal');
                window.showToast('Contract details loaded.', 'success');
            } else {
                window.showToast(res.message || 'Failed to load investment details for editing.', 'error');
            }
        } catch (error) {
            console.error('Error loading investment details:', error);
            window.showToast('Network error while fetching details.', 'error');
        }
    }


    // --- Interaction Binding ---
    function bindInteractions() {
        
        // 1. Add Plan Button
        $('#add-plan-btn').on('click', function() {
            setupAddPlanModal();
        });
        
        // 2. Edit Plan Action Button (delegated click handler on plans table)
        $(document).on('click', '.action-edit-plan', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            setupEditPlanModal(id);
        });

        // 3. Edit XYield Action Button (delegated click handler on investments table)
        $(document).on('click', '.action-edit-investment', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            setupEditXYieldModal(id);
        });
        
        // 4. Toggle Plan Status (for 'active' or 'hidden') - uses edit_plan action
        $(document).on('click', '.action-toggle-status', async function(e) {
             e.preventDefault();
             e.stopPropagation();
             const id = $(this).data('id');
             const newStatus = $(this).data('status'); // 'active' or 'hidden'
             const title = $(this).closest('tr').find('.f14-bold').text();

             const c = await window.uiConfirm({
                 title: 'Change Plan Status',
                 message: `Change the status of plan "${title}" to "${newStatus.toUpperCase()}"?`,
                 confirmText: 'Update'
             });
             if (!c.confirmed) return;

             window.showToast(`Updating plan status...`, 'info', 5000);

             // Fetch current plan values so we preserve them while changing status
             const cur = await fetchApi('/api/admin/funds.php', { fetch: 'plan_details', id: id }, "GET");
             if (cur.status !== 'success') {
                 window.showToast(cur.message || 'Could not load plan to update.', 'error');
                 return;
             }
             const p = cur.data;
             const payload = {
                 action: 'edit_plan',
                 id: id,
                 name: p.name,
                 min_amount: p.min_amount,
                 max_amount: p.max_amount,
                 daily_profit_percent: p.daily_profit_percent,
                 duration_days: p.duration_days,
                 referral_commission_percent: p.referral_commission_percent,
                 summary: p.summary,
                 icon: p.icon,
                 color: p.color,
                 status: newStatus
             };

             const res = await fetchApi('/api/admin/funds.php', payload, "POST");

             if (res.status === 'success') {
                 window.showToast(res.message, 'success');
                 loadFundsDashboard(); 
             } else {
                 window.showToast(res.message || `Status update failed.`, 'error');
             }
         });


        // 5. Plan Form Submission (Add/Edit Plan)
        $('#plan-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#plan-id').val();
            const isEdit = !!id;
            const action = isEdit ? 'edit_plan' : 'add_plan';
            
            const payload = {
                action: action,
                id: id,
                name: $('#plan-name').val(),
                min_amount: parseFloat($('#plan-min').val()),
                max_amount: $('#plan-max').val() === '' ? '' : parseFloat($('#plan-max').val()),
                daily_profit_percent: parseFloat($('#plan-daily').val()),
                duration_days: parseInt($('#plan-duration').val()),
                referral_commission_percent: parseFloat($('#plan-referral').val()),
                status: $('#plan-status').val()
            };

            window.showToast(`${isEdit ? 'Updating' : 'Creating'} plan...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                window.closeModal('#plan-modal');
                loadFundsDashboard(); 
            } else {
                window.showToast(res.message || `${isEdit ? 'Update' : 'Creation'} failed.`, 'error');
            }
        });

        // 6. Edit XYield Form Submission
        $('#edit-investment-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#inv-id').val();
            const newStatus = $('#inv-status').val();
            
            const c = await window.uiConfirm({
                title: 'Confirm Investment Changes',
                message: `Apply changes to XYield #${id}? Status will be set to "${newStatus.toUpperCase()}". The wallet is adjusted if status becomes 'completed' or 'cancelled'.`,
                confirmText: 'Apply Changes'
            });
            if (!c.confirmed) return;

            const payload = {
                action: 'edit_investment',
                id: id,
                amount: parseFloat($('#inv-amount').val()),
                daily_profit_percent: parseFloat($('#inv-roi').val()),
                status: newStatus
            };

            window.showToast(`Updating investment ID ${id}...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                window.closeModal('#edit-investment-modal');
                currentActivePage = 1; 
                loadFundsDashboard(); 
            } else {
                window.showToast(res.message || `XYield update failed.`, 'error');
            }
        });
        
        // 7. General search handling (assuming it searches active investments)
        // Since there is no dedicated search bar in the provided HTML, 
        // this is commented out, but ready for implementation if a search input is added.
        /*
        $('#general-search-form').on('submit', function(e) {
            e.preventDefault();
            currentSearchTerm = $('#search-input').val().trim();
            currentActivePage = 1;
            loadFundsDashboard();
        });
        */

    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        loadFundsDashboard(); 

        // Expose refresh function globally if needed
        window.refreshFundsDashboard = loadFundsDashboard;
    });

})(jQuery);