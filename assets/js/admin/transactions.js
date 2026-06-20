/**
 * ============================================================
 * Solace Mining Admin Transactions.js
 * Purpose: Frontend logic for the Admin Transactions management page.
 * Handles: Data fetching, metric rendering, table rendering, pagination, search/filter, and export.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for pagination and filtering
    let currentPage = 1;
    let currentFilter = 'all'; // Filter by type (deposit, withdrawal, etc.) or status (pending, completed)
    let currentSearch = '';
    const itemsPerPage = 10; 

    // --- Core Data Fetcher & UI Renderer ---
    async function loadTransactions(page = 1, filter = 'all', search = '') {
        const tableBody = $('#transactions-table-body');
        const paginationEl = $('#pagination');
        
        // Update state
        currentPage = page;
        currentFilter = filter;
        currentSearch = search;
        
        // Show loader
        tableBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading transactions...</td></tr>');
        paginationEl.empty();
        
        try {
            // Assumes fetchApi is a global utility available from admin.js
            const res = await fetchApi('/api/admin/transactions.php', {
                page: page,
                filter: filter,
                search: search,
                per_page: itemsPerPage 
            }, "GET");

            if (res.status !== 'success') {
                // Assumes showToast is a global utility
                showToast(res.message || 'Failed to load transaction list.', 'error');
                tableBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading data.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderTransactionsTable(data.transactions);
            renderPagination(data.current_page, data.total_pages);

        } catch (error) {
            console.error('API Error loading transactions:', error);
            showToast('A network error occurred while fetching transaction data.', 'error');
            tableBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error. Check console.</td></tr>');
        }
    }

    // --- Metric Update ---
    function updateMetrics(m) {
        if (!m) return;
        
        // Assumes formatCurrency is a global utility (if not, define simple format here)
        const formatCurrency = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        $('#total-transactions').text(m.total_transactions ?? 0);
        $('#total-volume').text(formatCurrency(m.total_volume ?? 0));
        $('#pending-count').text(m.pending_count ?? 0);
        $('#today-count').text(m.today_count ?? 0);

        // Recount animation if available (assumes counter is available globally)
        if (typeof counter === 'function') {
            counter();
        }
    }

    // --- Table Renderer ---
function renderTransactionsTable(transactions) {
        const tableBody = $('#transactions-table-body');
        tableBody.empty();

        if (!transactions || transactions.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No transactions found matching current criteria.</td></tr>');
            return;
        }

        transactions.forEach(tx => {
            let statusColorClass;
            let statusBadgeClass; // New variable for the badge background class

            // NOTE: tx.status and tx.type already come as 'Completed'/'Donation' due to ucfirst() in PHP
            
            if (tx.status.toLowerCase() === 'completed') {
                statusColorClass = 'text-Green';
                statusBadgeClass = 'bg-Green'; // Use the appropriate background class
            } else if (tx.status.toLowerCase() === 'pending') {
                statusColorClass = 'text-Orange';
                statusBadgeClass = 'bg-Orange';
            } else { // failed/cancelled/etc.
                statusColorClass = 'text-Red';
                statusBadgeClass = 'bg-Salmon';
            }
            
            // Assuming formatCurrency is available
            const formatCurrency = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            
            // **FIX: Added 'tf-table-item' class for card styling and 'data-label' for mobile responsive view.**
            const row = `
                <tr data-id="${tx.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Transaction ID">${tx.reference}</td>
                    
                    <td class="f14-regular text-Gray" data-label="Date">${tx.date}</td>
                    
                    <td class="f14-regular" data-label="User">
                        <a href="/admin.users?search=${tx.user_id}" class="text-Primary f14-bold">${tx.user_name} (ID: ${tx.user_id})</a>
                    </td>
                    
                    <td class="f14-regular text-Primary f14-bold" data-label="Type">${tx.type}</td> 
                    
                    <td class="f14-regular text-Black" data-label="Amount (USD)">$${formatCurrency(tx.amount)}</td>
                    
                    <td data-label="Status">
                        <div class="box-status ${statusBadgeClass}">
                            ${tx.status} 
                        </div>
                    </td> 
                </tr>
            `;
            tableBody.append(row);
        });
    }

    // --- Pagination Renderer (Borrowed from Users.js for consistency) ---
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
            loadTransactions(newPage, currentFilter, currentSearch);
        });
    }

    // --- Search, Filter, and Export Handlers ---
    function bindInteractions() {
        // Search form submission
        $('.form-search').on('submit', function(e) {
            e.preventDefault();
            const searchVal = $('#transaction-search').val().trim();
            loadTransactions(1, currentFilter, searchVal);
        });

        // Filter dropdown click
        $('.dropdown-menu a[data-filter]').on('click', function(e) {
            e.preventDefault();
            const filterVal = $(this).data('filter');
            
            // Update the button text to show current filter
            $(this).closest('.dropdown').find('button').html(`<span class="iconify" data-icon="mdi:filter"></span> ${$(this).text()}`);
            
            loadTransactions(1, filterVal, currentSearch);
        });
        
        // Export to CSV
        $('#export-csv').on('click', function (e) {
            e.preventDefault();
            // Build the URL with the current filters for accurate export
            let exportUrl = `/api/admin/transactions.php?export=true&filter=${currentFilter}&search=${currentSearch}`;
            window.location.href = exportUrl;
            showToast('Preparing and downloading CSV report...', 'info', 5000);
        });
    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        
        // Initial load of the transaction list
        loadTransactions(1); 
    });

})(jQuery);