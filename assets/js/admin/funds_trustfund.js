/**
 * FILE: /assets/js/admin/funds_trustfund.js
 * ============================================================
 * HealthRunCare Admin Funds TrustFund.js
 * Purpose: Frontend logic for the Admin TrustFund Management page.
 * Handles: Metrics, TrustFund Scheme CRUD, Active TrustFund list/pagination.
 * Assumes global utility functions (fetchApi, formatCurrency, showToast, showModal, closeModal) are available.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for tables and search
    let currentActivePage = 1;
    let currentSearchTerm = '';
    
    // --- UI Helpers ---

    /** Renders a status badge for TrustFunds. */
    function renderStatusBadge(status) {
        status = status.toLowerCase();

        let badgeClass = 'bg-Gray';
        if (status === 'active') {
            badgeClass = 'bg-Primary'; // Using Primary color for active TrustFund
        } else if (status === 'matured' || status === 'completed') {
            badgeClass = 'bg-Green';
        } else if (status === 'unlock_pending' || status === 'unlocked_early') {
            badgeClass = 'bg-Orange';
        }

        // Replace underscores for display
        const displayStatus = status.replace('_', ' ');

        return `<div class="box-status ${badgeClass}"><span class="font-poppins key-sort">${displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1)}</span></div>`;
    }
    
    // --- Core Data Fetcher & UI Renderer ---

    /**
     * Loads all data for the TrustFund dashboard (Metrics, Schemes, Active Accounts).
     */
    async function loadTrustFundDashboard() {
        // Show loading indicators
        const schemesBody = $('#trustfund-schemes-body');
        const activeBody = $('#active-trustfund-body');
        schemesBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading schemes...</td></tr>');
        activeBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading active accounts...</td></tr>');
        $('#active-trustfund-pagination').empty();

        try {
            const res = await fetchApi('/api/admin/funds_trustfund.php', {
                search: currentSearchTerm,
                active_page: currentActivePage
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load dashboard data.', 'error');
                schemesBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading schemes.</td></tr>');
                activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading accounts.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderTrustFundSchemesTable(data.plans);
            renderActiveTrustFundsTable(data.active_trustfund);
            renderActiveTrustFundPagination(data.active_page, data.active_total_pages);
            
            if (typeof window.counter === 'function') {
                window.counter();
            }

        } catch (error) {
            console.error('API Error loading TrustFund dashboard:', error);
            window.showToast('A network error occurred while fetching data.', 'error');
            schemesBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
        }
    }

    /**
     * Updates the summary cards.
     */
    function updateMetrics(m) {
        if (!m) return;
        const format = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        $('#total-trustfund').text(format(m.total_trustfund ?? 0));
        $('#trustfund-users').text(m.trustfund_users ?? 0);
        $('#total-contributions').text(format(m.total_contributions ?? 0)); 
        $('#next-payout').text(m.next_payout ?? '—');
    }
    
    /**
     * Renders the TrustFund Schemes table.
     */
    function renderTrustFundSchemesTable(plans) {
        const tableBody = $('#trustfund-schemes-body');
        tableBody.empty();

        if (!plans || plans.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No TrustFund schemes defined.</td></tr>');
            return;
        }

        plans.forEach(plan => {
            const maxAmountDisplay = plan.max_amount > 99999999 ? 'Unlimited' : `$${window.formatCurrency(plan.max_amount)}`;
            const row = `
                <tr data-plan-id="${plan.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Scheme Name">
                        <span class="f14-bold text-Primary">${plan.name}</span>
                    </td>
                    <td class="f14-regular" data-label="Target Amount"><span class="text-Green">${maxAmountDisplay}</span></td>
                    <td class="f14-regular" data-label="Duration">${plan.duration_display}</td>
                    <td class="f14-regular" data-label="Min Contribution">$${window.formatCurrency(plan.min_amount)}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(plan.status)}</td>
                    <td class="f14-regular" data-label="Actions">
                        <div class="dropdown default style-fill actions-dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item action-edit-trustfund-scheme" data-id="${plan.id}">
                                        Edit Scheme
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
     * Renders the Active TrustFund Accounts table.
     */
    function renderActiveTrustFundsTable(trustfunds) {
        const tableBody = $('#active-trustfund-body');
        tableBody.empty();

        if (!trustfunds || trustfunds.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No active TrustFund accounts found.</td></tr>');
            return;
        }

        trustfunds.forEach(tf => {
            // Note: tf.target is currently a placeholder (0.00) as the value is not in the 'trustfund' table.
            const row = `
                <tr data-trustfund-id="${tf.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="User">
                        <span class="f14-bold">${tf.user_name}</span>
                        <div class="f12-regular text-Gray">${tf.user_email}</div>
                    </td>
                    <td class="f14-regular" data-label="Scheme">${tf.plan_name}</td>
                    <td class="f14-bold" data-label="Contributed">$${window.formatCurrency(tf.amount)}</td>
                    <td class="f14-regular text-Gray" data-label="End Date">${tf.maturity_date}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(tf.status)}</td>
                </tr>
            `;
            tableBody.append(row);
        });
        // No actions column implemented based on the provided HTML header for this table.
    }
    
    /**
     * Renders the pagination for the Active TrustFund table.
     */
    function renderActiveTrustFundPagination(currentPage, totalPages) {
        const paginationEl = $('#active-trustfund-pagination');
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
            loadTrustFundDashboard(); 
        });
    }

    // --- Modal Logic ---

    /** Prepares the modal for a new scheme entry. */
    function setupAddSchemeModal() {
        $('#trustfund-scheme-title').text('Add New TrustFund Scheme');
        $('#trustfund-scheme-form')[0].reset();
        $('#trustfund-scheme-id').val(''); 
        $('#trustfund-min-contrib').val(0);
        $('#trustfund-target').val(0);
        $('#trustfund-scheme-status').val('active');
        $('.modal-confirm-btn').text('Save Scheme').removeClass('bg-Accent text-Black').addClass('bg-Primary text-White');
        window.showModal('#trustfund-scheme-modal');
    }

    /** Fetches scheme data and populates the Edit Scheme modal. */
    async function setupEditSchemeModal(id) {
        try {
            window.showToast('Loading scheme details...', 'info');
            const res = await fetchApi('/api/admin/funds_trustfund.php', {
                fetch: 'plan_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const plan = res.data;
                
                $('#trustfund-scheme-title').text(`Edit Scheme: ${plan.name}`);
                $('#trustfund-scheme-id').val(plan.id);
                $('#trustfund-scheme-name').val(plan.name);
                $('#trustfund-target').val(plan.max_amount); // Maps to max_amount
                $('#trustfund-duration').val(plan.duration_months);
                $('#trustfund-min-contrib').val(plan.min_amount); // Maps to min_amount
                $('#trustfund-scheme-status').val(plan.status.toLowerCase()); 
                
                $('.modal-confirm-btn').text('Update Scheme').removeClass('bg-Primary text-White').addClass('bg-Accent text-Black');
                window.showModal('#trustfund-scheme-modal');
                window.showToast('Scheme details loaded.', 'success');
            } else {
                window.showToast(res.message || 'Failed to load scheme details for editing.', 'error');
            }
        } catch (error) {
            console.error('Error loading scheme details:', error);
            window.showToast('Network error while fetching details.', 'error');
        }
    }


    // --- Interaction Binding ---
    function bindInteractions() {
        
        // 1. Add Scheme Button
        $('#add-trustfund-scheme-btn').on('click', function() {
            setupAddSchemeModal();
        });
        
        // 2. Edit Scheme Action Button (delegated click handler on schemes table)
        $(document).on('click', '.action-edit-trustfund-scheme', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            setupEditSchemeModal(id);
        });
        
        // 3. Scheme Form Submission (Add/Edit Plan)
        $('#trustfund-scheme-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#trustfund-scheme-id').val();
            const isEdit = !!id;
            const action = isEdit ? 'edit_plan' : 'add_plan';
            
            const payload = {
                action: action,
                id: id,
                name: $('#trustfund-scheme-name').val(),
                // Frontend uses 'Target Amount', backend uses max_amount
                max_amount: parseFloat($('#trustfund-target').val()), 
                // Frontend uses 'Min Monthly Contribution', backend uses min_amount
                min_amount: parseFloat($('#trustfund-min-contrib').val()), 
                // Frontend is in months, backend expects duration in months
                duration: parseInt($('#trustfund-duration').val()), 
                // status: $('#trustfund-scheme-status').val() // Omitted due to missing DB column
            };

            window.showToast(`${isEdit ? 'Updating' : 'Creating'} TrustFund scheme...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds_trustfund.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                window.closeModal('#trustfund-scheme-modal');
                loadTrustFundDashboard(); 
            } else {
                window.showToast(res.message || `${isEdit ? 'Update' : 'Creation'} failed.`, 'error');
            }
        });

        // 4. Active TrustFund Pagination 
        $(document).on('click', '#active-trustfund-pagination .page-link', function(e) {
            e.preventDefault();
            const newPage = $(this).data('page');
            currentActivePage = newPage;
            loadTrustFundDashboard(); 
        });

    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        loadTrustFundDashboard(); 

        window.refreshTrustFundDashboard = loadTrustFundDashboard;
    });

})(jQuery);