/**
 * ============================================================
 * TitanXHoldings Admin Wallet.js
 * Purpose: Frontend logic for the Admin Wallet Management page.
 * Handles: Data fetching, metric rendering, table rendering, pagination, search/filter, and balance updates.
 * ============================================================
 */
;(function ($) {

    // Fix Bootstrap dropdown blocking buttons
$(document).on('click', '.dropdown-menu .dropdown-item', function (e) {
    e.preventDefault();
    e.stopPropagation();
});

    "use strict";

    // Global state
    let currentPage = 1;
    let currentFilter = 'all'; 
    let currentSearch = '';
    const itemsPerPage = 10; 

    // --- Core Data Fetcher & UI Renderer ---
    async function loadWallets(page = 1, filter = 'all', search = '') {
        const tableBody = $('#wallets-table-body');
        const paginationEl = $('#pagination');
        
        // Update state
        currentPage = page;
        currentFilter = filter;
        currentSearch = search;
        
        // Show loader
        tableBody.empty().html('<tr><td colspan="4" class="text-center text-Primary f14-regular">Loading wallet data...</td></tr>');
        paginationEl.empty();
        
        try {
            // Assumes fetchApi is a global utility
            const res = await fetchApi('/api/admin/wallets.php', {
                page: page,
                filter: filter,
                search: search,
                per_page: itemsPerPage 
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load wallet list.', 'error');
                tableBody.html('<tr><td colspan="4" class="text-center text-Red f14-regular">Error loading data.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderWalletsTable(data.wallets);
            renderPagination(data.current_page, data.total_pages);

        } catch (error) {
            console.error('API Error loading wallets:', error);
            window.showToast('A network error occurred while fetching wallet data.', 'error');
            tableBody.html('<tr><td colspan="4" class="text-center text-Red f14-regular">Network error. Check console.</td></tr>');
        }
    }

    // --- Metric Update ---
    function updateMetrics(m) {
        if (!m) return;
        
        // Assumes formatCurrency is a global utility from admin.js
        const formatCurrency = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        $('#total-wallets').text(m.total_wallets ?? 0);
        $('#total-balance').text(formatCurrency(m.total_balance ?? 0));
        
        // These are counts, not currency
        $('#pending-deposits').text(m.pending_deposits_count ?? 0);
        $('#pending-withdrawals').text(m.pending_withdrawals_count ?? 0);

        // Rerun counter animation
        if (typeof counter === 'function') {
            counter();
        }
    }

    // --- Table Renderer ---
    function renderWalletsTable(wallets) {
        const tableBody = $('#wallets-table-body');
        tableBody.empty();

        if (!wallets || wallets.length === 0) {
            tableBody.html('<tr><td colspan="4" class="text-center text-Gray f14-regular">No wallets found matching current criteria.</td></tr>');
            return;
        }
        
        // Assumes formatCurrency is a global utility
        const formatCurrency = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        wallets.forEach(wallet => {
            // Apply text color based on balance value
            const balanceColor = Number(wallet.balance) > 0 ? 'text-Green' : 'text-Red';
            
            const actionDropdown = `
                <div class="dropdown default style-fill actions-dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button type="button" class="dropdown-item action-edit-balance"
                                data-wallet-id="${wallet.wallet_id}"
                                data-user-name="${wallet.user_name} (ID: ${wallet.user_id})"
                                data-current-balance="${wallet.balance}">
                                Edit Balance
                            </button>
                        </li>
                    </ul>
                </div>
            `;
            
            // NOTE: Added 'tf-table-item' class and 'data-label' attributes for proper styling
            const row = `
                <tr data-wallet-id="${wallet.wallet_id}" class="tf-table-item">
                    <td class="f14-regular" data-label="User">
                        <a href="/admin.users?search=${wallet.user_id}" class="text-Primary f14-bold">${wallet.user_name}</a>
                        <div class="f12-regular text-Gray">${wallet.user_email}</div>
                    </td>
                    <td class="f14-regular" data-label="Wallet ID">${wallet.wallet_id}</td>
                    <td class="f14-bold ${balanceColor}" data-label="Balance">$${formatCurrency(wallet.balance)}</td>
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
            loadWallets(newPage, currentFilter, currentSearch);
        });
    }

    // --- Search, Filter, and Export Handlers ---
    function bindInteractions() {
        
        // Ensure dropdown links inside actions-dropdown don't block parent clicks
        $(document).on('click', '.actions-dropdown .dropdown-item', function(e) {
            e.stopPropagation();
        });

        // 1. Search form submission
        $('.form-search').on('submit', function(e) {
            e.preventDefault();
            const searchVal = $('#wallet-search').val().trim();
            loadWallets(1, currentFilter, searchVal);
        });

        // 2. Filter dropdown click
        $('.dropdown-menu a[data-filter]').on('click', function(e) {
            e.preventDefault();
            const filterVal = $(this).data('filter');
            
            // Update the button text to show current filter
            $(this).closest('.dropdown').find('button').html(`<span class="iconify" data-icon="mdi:filter"></span> ${$(this).text()}`);
            
            loadWallets(1, filterVal, currentSearch);
        });
        
        // 3. Export to CSV 
        $('#export-csv').on('click', function (e) {
            e.preventDefault();
            let exportUrl = `/api/admin/wallets.php?export=true&filter=${currentFilter}&search=${currentSearch}`;
            window.location.href = exportUrl;
            window.showToast('Preparing and downloading CSV report...', 'info', 5000);
        });

        // 4. Edit Balance Modal Trigger
        $(document).on('click', '.action-edit-balance', function(e) {
            e.preventDefault();
            e.stopPropagation(); // 🔥 This was missing!

            const walletId = $(this).data('wallet-id');
            const userName = $(this).data('user-name');
            const currentBalance = $(this).data('current-balance');

            $('#edit-wallet-id').val(walletId);
            $('#edit-wallet-user').val(userName);
            $('#edit-current-balance').val(`$${formatCurrency(currentBalance)}`);
            $('#edit-new-balance').val(currentBalance);

            window.showModal('#edit-balance-modal');
        });


        // 5. Edit Balance Form Submission
        $('#edit-balance-form').on('submit', async function(e) {
            e.preventDefault();

            const walletId = $('#edit-wallet-id').val();
            const newBalance = $('#edit-new-balance').val();
            
            if (isNaN(newBalance) || Number(newBalance) < 0) {
                window.showToast('Please enter a valid non-negative number for the balance.', 'error');
                return;
            }

            window.showToast(`Updating balance for Wallet ID ${walletId}...`, 'info', 5000);

            try {
                const res = await fetchApi('/api/admin/wallets.php', {
                    action: 'update_balance',
                    wallet_id: walletId,
                    new_balance: newBalance
                }, "POST");

                if (res.status === 'success') {
                    window.showToast(res.message, 'success');
                    window.closeModal('#edit-balance-modal');
                    // Refresh current list view
                    await loadWallets(currentPage, currentFilter, currentSearch); 
                } else {
                    window.showToast(res.message || 'Balance update failed.', 'error');
                }
            } catch (error) {
                console.error('Update balance error:', error);
                window.showToast('A network error occurred or the server failed to respond.', 'error');
            }
        });
        
        // 6. Handle Clicks on Pending Deposit/Withdrawal Cards (to open respective modals)
        $('#pending-deposits').closest('.wallet-card').on('click', function() {
            window.showModal('#pending-deposits-modal');
        });
        
        $('#pending-withdrawals').closest('.wallet-card').on('click', function() {
            window.showModal('#pending-withdrawals-modal');
        });
    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        // Initial load of the wallet list
        loadWallets(1); 
    });

})(jQuery);