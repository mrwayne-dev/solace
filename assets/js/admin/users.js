/**
 * ============================================================
 * Solace Mining Admin Users.js
 * Purpose: Frontend logic for the Admin Users management page.
 * Handles: Data fetching, table rendering, pagination, search/filter, and user actions (Edit/Delete/Email).
 * ============================================================
 */
;(function ($) {
    // Fix Bootstrap dropdown blocking links
$(document).on('click', '.dropdown-menu .dropdown-item', function (e) {
    e.preventDefault();
    e.stopPropagation(); 
});


    "use strict";

    // Global state for pagination and filtering
    let currentPage = 1;
    let currentFilter = 'all';
    let currentSearch = '';
    const itemsPerPage = 10; // Must match the backend API's assumption

    // --- Core Data Fetcher & UI Renderer ---
    async function loadUsers(page = 1, filter = 'all', search = '') {
        const tableBody = $('#users-table-body');
        const paginationEl = $('#pagination');
        tableBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading users...</td></tr>');
        paginationEl.empty();

        currentPage = page;
        currentFilter = filter;
        currentSearch = search;
        
        // Show loader/disable controls if needed (optional)
        // ...

        try {
            const res = await fetchApi('/api/admin/users.php', {
                page: page,
                filter: filter,
                search: search,
                per_page: itemsPerPage // Pass this for clarity, even if backend defaults
            }, "GET");

            if (res.status !== 'success') {
                showToast(res.message || 'Failed to load user list.', 'error');
                tableBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading data.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderUsersTable(data.users);
            renderPagination(data.current_page, data.total_pages);

        } catch (error) {
            console.error('API Error loading users:', error);
            showToast('A network error occurred while fetching user data.', 'error');
            tableBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error. Check console.</td></tr>');
        }
    }

    // --- Metric Update ---
    function updateMetrics(m) {
        if (!m) return;
        $('#total-users').text(m.total_users ?? 0);
        $('#active-users').text(m.active_users ?? 0);
        $('#admin-count').text(m.admin_count ?? 0);
        $('#new-today').text(m.new_today ?? 0);
        // Recount animation if available (assuming countto.js is loaded later)
        if (typeof counter === 'function') {
            counter();
        }
    }

    // --- Table Renderer ---
    function renderUsersTable(users) {
        const tableBody = $('#users-table-body');
        tableBody.empty();

        if (!users || users.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No users found matching current criteria.</td></tr>');
            return;
        }

        users.forEach(user => {
            
            // Determine Role Badge Colors/Classes
            let roleBadgeClass;
            if (user.role === 'admin') {
                // Admin role uses Primary color badge
                roleBadgeClass = 'bg-Primary text-White';
            } else {
                // User role uses lighter, neutral background
                roleBadgeClass = 'bg-Primary text-White ';
            }
            
            // Determine Status Badge Colors/Classes
            let statusBadgeClass;
            if (user.status === 'active') {
                // Active status uses Green background
                statusBadgeClass = 'bg-Green text-White';
            } else { 
                // Disabled/Suspended uses Salmon/Red background
                statusBadgeClass = 'bg-Salmon text-White'; 
            }
            
            const actionDropdown = `
                <div class="dropdown default style-fill actions-dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="dropdown-item action-account"
                                data-id="${user.id}"
                                data-name="${user.display_name}">
                                View Account
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item action-edit"
                                data-id="${user.id}"
                                data-name="${user.display_name}"
                                data-email="${user.email}"
                                data-role="${user.role}"
                                data-status="${user.status}">
                                Edit User
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item action-email"
                                data-id="${user.id}"
                                data-email="${user.email}"
                                data-name="${user.display_name}">
                                Send Email
                            </button>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item action-delete text-Red"
                                data-id="${user.id}"
                                data-name="${user.display_name}">
                                Delete User
                            </button>
                        </li>
                    </ul>
                </div>
            `;
            
            // FIXED: Added tf-table-item class and data-label attributes for correct table rendering
            const row = `
                <tr data-id="${user.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Name">${user.display_name} (ID: ${user.id})</td>
                    <td class="f14-regular" data-label="Email">${user.email}</td>
                    
                    <td data-label="Role">
                        <div class="box-status ${roleBadgeClass}">
                            ${user.role.toUpperCase()}
                        </div>
                    </td>
                    
                    <td data-label="Status">
                        <div class="box-status ${statusBadgeClass}">
                            ${user.status.toUpperCase()}
                        </div>
                    </td>
                    
                    <td class="f14-regular text-Gray" data-label="Last Login">${user.last_login}</td>
                    <td class="f14-regular" data-label="Actions">${actionDropdown}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }

    // --- Pagination Renderer ---
    function renderPagination(currentPage, totalPages) {
        const paginationEl = $('#pagination');
        paginationEl.empty();
        if (totalPages <= 1) return;

        // Previous button
        paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">Previous</button>`);

        // Page buttons (show max 5 pages centered around current)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (currentPage <= 3) {
            endPage = Math.min(totalPages, 5);
            startPage = 1;
        } else if (currentPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
            endPage = totalPages;
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'bg-Primary text-White' : 'bg-GrayLight text-Black';
            paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link ${activeClass}" data-page="${i}">${i}</button>`);
        }
        
        // Next button
        paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}">Next</button>`);
        
        // Bind click events
        paginationEl.find('.page-link').on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) return;
            const newPage = $(this).data('page');
            loadUsers(newPage, currentFilter, currentSearch);
        });
    }

    // --- Action Handlers ---

    // 1. Edit User Modal & Form
    function bindEditUser() {
        $(document).on('click', '.action-edit', function(e) {
            e.preventDefault();
            e.stopPropagation(); // FIX: Stop event propagation to prevent Bootstrap dropdown from blocking modal
            
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const role = $(this).data('role');
            const status = $(this).data('status');

            $('#edit-user-id').val(id);
            $('#edit-name').val(name);
            $('#edit-email').val(email);
            $('#edit-role').val(role);
            
            // Map 'disabled' to 'suspended' for the modal dropdown (as used in PHP logic)
            $('#edit-status').val(status === 'disabled' ? 'suspended' : status);
            
            showModal('#edit-user-modal');
        });

        $('#edit-user-form').on('submit', async function(e) {
            e.preventDefault();

            const userId = $('#edit-user-id').val();
            const name = $('#edit-name').val();
            const email = $('#edit-email').val();
            const role = $('#edit-role').val();

            // Convert UI 'suspended' to backend 'disabled'
            let status = $('#edit-status').val() === 'suspended' ? 'disabled' : $('#edit-status').val();

            if (!userId || !name || !role || !status) {
                showToast('Missing required fields for update.', 'error');
                return;
            }

            showToast(`Updating user ID ${userId}...`, 'info', 5000);

            try {
                const res = await fetchApi('/api/admin/users.php', {
                    action: 'edit_user',
                    user_id: userId,
                    name: name,
                    email: email,
                    role: role,
                    status: status
                }, "POST");

                if (res.status === 'success') {
                    showToast(res.message, 'success');
                    closeModal('#edit-user-modal');
                    await loadUsers(currentPage, currentFilter, currentSearch); 
                } else {
                    showToast(res.message || 'Update failed.', 'error');
                }
            } catch (error) {
                console.error('Edit user error:', error);
                showToast('A network error occurred or the server failed to respond.', 'error');
            }
        });

    }

    // 2. Send Email Modal & Form
    function bindSendEmail() {
        $(document).on('click', '.action-email', function(e) {
            e.preventDefault();
            e.stopPropagation(); // FIX: Stop event propagation to prevent Bootstrap dropdown from blocking modal
            
            const id = $(this).data('id');
            const email = $(this).data('email');
            const name = $(this).data('name');

            $('#email-user-id').val(id);
            $('#email-to').val(`${name} <${email}>`);
            $('#send-email-modal h2').text(`Send Email to ${name}`);

            showModal('#send-email-modal');
        });

        $('#send-email-form').on('submit', async function(e) {
            e.preventDefault();

            const userId = $('#email-user-id').val();
            const subject = $('#email-subject').val();
            const body = $('#email-body').val();

            if (!userId || !subject || !body) {
                showToast('Email subject and body are required.', 'error');
                return;
            }

            showToast(`Queuing email for user ID ${userId}...`, 'info', 5000);

            try {
                // Note: We use the 'users.php' API for specific user emails from the table.
                const res = await fetchApi('/api/admin/users.php', {
                    action: 'send_email',
                    user_id: userId,
                    subject: subject,
                    body: body
                }, "POST");

                if (res.status === 'success') {
                    showToast(res.message, 'success');
                    closeModal('#send-email-modal');
                    $('#send-email-form')[0].reset(); // Reset form content
                } else {
                    showToast(res.message || 'Email sending failed.', 'error');
                }
            } catch (error) {
                console.error('Send email error:', error);
                showToast('A network error occurred or the server failed to respond.', 'error');
            }
        });
    }

    // 3. Delete User Modal & Action
    function bindDeleteUser() {
        let userToDeleteId = null;

        $(document).on('click', '.action-delete', function(e) {
            e.preventDefault();
            e.stopPropagation(); // FIX: Stop event propagation to prevent Bootstrap dropdown from blocking modal
            
            const id = $(this).data('id');
            const name = $(this).data('name');
            
            userToDeleteId = id;

            $('#delete-user-name').text(`${name} (ID: ${id})`);
            showModal('#delete-user-modal');
        });

        $('#confirm-delete').on('click', async function() {
            if (!userToDeleteId) {
                showToast('No user selected for deletion.', 'error');
                return;
            }

            // Disable button and show progress
            const originalText = $(this).text();
            $(this).text('Deleting...').prop('disabled', true);
            showToast(`Deleting user...`, 'warning');

            try {
                const res = await fetchApi('/api/admin/users.php', {
                    action: 'delete_user',
                    user_id: userToDeleteId
                }, "POST");

                if (res.status === 'success') {
                    showToast(res.message, 'success');
                    closeModal('#delete-user-modal');
                    // Refresh current page. If the page is now empty, go back one page.
                    await loadUsers(currentPage, currentFilter, currentSearch); 
                } else {
                    showToast(res.message || 'Deletion failed.', 'error');
                }
            } catch (error) {
                console.error('Delete user error:', error);
                showToast('A network error occurred or the server failed to respond.', 'error');
            } finally {
                $(this).text(originalText).prop('disabled', false);
                userToDeleteId = null;
            }
        });
    }
    
    // 4. Search and Filter Handlers
    function bindSearchAndFilter() {
        // Search form submission
        $('.form-search').on('submit', function(e) {
            e.preventDefault();
            const searchVal = $('#user-search').val().trim();
            loadUsers(1, currentFilter, searchVal);
        });

        // Filter dropdown click
        $('.dropdown-menu a[data-filter]').on('click', function(e) {
            e.preventDefault();
            const filterVal = $(this).data('filter');
            // Update the button text to show current filter
            $(this).closest('.dropdown').find('button').html(`<span class="iconify" data-icon="mdi:filter"></span> ${$(this).text()}`);
            loadUsers(1, filterVal, currentSearch);
        });
    }

    /* ===================== Account View (read + write) ===================== */
    const esc = (s) => $('<div>').text(s == null ? '' : s).html();
    const money = (n) => Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    let accountUserId = null;

    async function openAccount(userId) {
        accountUserId = userId;
        try {
            const res = await fetchApi('/api/admin/users.php', { action: 'get_user_detail', user_id: userId }, 'POST');
            if (res.status !== 'success') { showToast(res.message || 'Could not load account', 'error'); return; }
            const { user, wallet, investments, transactions } = res.data;
            const w = wallet || {};
            const locked = Number(user.withdrawals_locked) === 1;

            const invRows = (investments || []).map(i => `
                <tr><td>${esc(i.plan_name)}</td><td>$${money(i.amount)}</td>
                    <td>${esc(i.status)}</td><td>${i.days_paid}/${i.duration_days}</td>
                    <td>$${money(i.roi_earned)}</td></tr>`).join('') ||
                '<tr><td colspan="5" class="text-Gray">No investments</td></tr>';

            const txRows = (transactions || []).map(t => `
                <tr><td>${esc(t.type)}</td><td>$${money(t.amount)}</td>
                    <td>${esc(t.status)}</td><td class="text-Gray">${esc(t.created_at)}</td></tr>`).join('') ||
                '<tr><td colspan="4" class="text-Gray">No transactions</td></tr>';

            $('#account-modal-title').text('Account — ' + esc(user.full_name || user.name || user.email));
            $('#account-modal-body').html(`
                <div class="mb-16">
                    <p class="f14-regular"><strong>Email:</strong> ${esc(user.email)} &nbsp;·&nbsp; <strong>Role:</strong> ${esc(user.role)} &nbsp;·&nbsp; <strong>Status:</strong> ${esc(user.status)}</p>
                    <p class="f12-regular text-Gray">Joined ${esc(user.created_at)} · Referral code: ${esc(user.referral_code || '—')}</p>
                </div>

                <div class="wg-box mb-16" style="padding:16px;">
                    <div class="label-01 text-Primary mb-8">Wallet</div>
                    <div class="flex items-center gap-2 mb-8">
                        <label class="f14-regular" style="min-width:120px;">Balance ($)</label>
                        <input type="number" step="0.01" min="0" id="acct-balance" class="form-control" style="max-width:200px;" value="${Number(w.balance||0)}">
                        <button class="tf-button bg-Primary text-White" id="acct-save-balance">Save</button>
                    </div>
                    <p class="f12-regular text-Gray">Deposited $${money(w.total_deposited)} · Withdrawn $${money(w.total_withdrawn)} · Earnings $${money(w.total_earnings)} · Pending WD $${money(w.pending_withdrawals)}</p>
                </div>

                <div class="wg-box mb-16" style="padding:16px;">
                    <div class="label-01 text-Primary mb-8">Withdrawals</div>
                    <p class="f14-regular mb-8">Status:
                        <span class="box-status ${locked ? 'bg-Red' : 'bg-Green'} f12-medium">${locked ? 'LOCKED' : 'ALLOWED'}</span>
                    </p>
                    <div class="form-group mb-8">
                        <label class="f14-regular">Reason shown to user (when locked)</label>
                        <input type="text" id="acct-lock-reason" class="form-control" value="${esc(user.withdrawal_lock_reason || '')}" placeholder="e.g. Pending KYC verification">
                    </div>
                    <button class="tf-button ${locked ? 'bg-Green' : 'bg-Red'} text-White" id="acct-toggle-lock" data-locked="${locked ? 1 : 0}">
                        ${locked ? 'Unlock withdrawals' : 'Lock withdrawals'}
                    </button>
                </div>

                <div class="wg-box mb-16" style="padding:16px;">
                    <div class="label-01 text-Primary mb-8">Investments</div>
                    <table class="tab-sell-order"><thead><tr><th>Plan</th><th>Amount</th><th>Status</th><th>Days</th><th>ROI</th></tr></thead><tbody>${invRows}</tbody></table>
                </div>

                <div class="wg-box" style="padding:16px;">
                    <div class="label-01 text-Primary mb-8">Recent transactions</div>
                    <table class="tab-sell-order"><thead><tr><th>Type</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>${txRows}</tbody></table>
                </div>
            `);
            showModal('#account-modal');
        } catch (err) {
            console.error('openAccount error', err);
            showToast('Failed to load account', 'error');
        }
    }

    function bindAccountView() {
        $(document).on('click', '.action-account', function (e) {
            e.preventDefault();
            openAccount($(this).data('id'));
        });

        // Save balance (write)
        $(document).on('click', '#acct-save-balance', async function () {
            const value = $('#acct-balance').val();
            const res = await fetchApi('/api/admin/users.php',
                { action: 'adjust_balance', user_id: accountUserId, field: 'balance', value }, 'POST');
            showToast(res.message || (res.status === 'success' ? 'Saved' : 'Failed'), res.status === 'success' ? 'success' : 'error');
        });

        // Toggle withdrawal lock (write)
        $(document).on('click', '#acct-toggle-lock', async function () {
            const currentlyLocked = Number($(this).data('locked')) === 1;
            const reason = $('#acct-lock-reason').val();
            const res = await fetchApi('/api/admin/users.php',
                { action: 'toggle_withdrawal_lock', user_id: accountUserId, locked: currentlyLocked ? 0 : 1, reason }, 'POST');
            if (res.status === 'success') { showToast(res.message, 'success'); openAccount(accountUserId); }
            else showToast(res.message || 'Failed', 'error');
        });
    }

    // --- Initialization ---
    $(function () {
        // Ensure utility functions from admin.js (like showModal, closeModal, showToast) are available.
        // Assuming the admin.js script loads first, these functions should be available globally/via closure.

        // Bind all interactive elements
        bindEditUser();
        bindSendEmail();
        bindDeleteUser();
        bindAccountView();
        bindSearchAndFilter();

        // Initial load of the user list
        loadUsers(1);
    });

})(jQuery);