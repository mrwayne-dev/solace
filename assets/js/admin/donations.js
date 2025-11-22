// D:\mrwayne\web_dev\healthruncare\assets\js\admin\donations.js
/**
 * ============================================================
 * HealthRunCare Admin Donations.js
 * Purpose: Frontend logic for the Admin Donations Management page.
 * Handles: Metrics, Campaign management (CRUD, table, search, filter), Donation history (table, pagination, search).
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for tables and search
    let currentDonationsPage = 1;
    let currentCampaignFilter = 'all'; 
    let currentSearchTerm = '';
    
    // Assumes global utility functions (fetchApi, formatCurrency, showToast, showModal, closeModal) are available from admin.js
    
    // --- UI Helpers ---
    
/** Renders a status badge. */
function renderStatusBadge(status) {
    status = status.toLowerCase();

    let badgeClass = 'bg-Gray';
    if (status === 'active') {
        badgeClass = 'bg-Green';
    } else if (status === 'inactive') {
        badgeClass = 'bg-Orange';
    } else if (status === 'archived') {
        badgeClass = 'bg-Salmon';
    }

    return `<div class="box-status ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</div>`;
}

    
    /** Renders a progress bar. */
    function renderProgressBar(progress) {
        const cappedProgress = Math.min(100, Math.max(0, progress));
        const color = cappedProgress >= 100 ? 'bg-Green' : 'bg-Primary';
        return `
            <div class="d-flex items-center gap-2">
                <div class="progress" style="width: 100px; height: 8px; background: var(--LightGray); border-radius: 4px;">
                    <div class="progress-bar ${color}" role="progressbar" style="width: ${cappedProgress}%;" aria-valuenow="${cappedProgress}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <span class="f12-regular text-Gray">${cappedProgress}%</span>
            </div>
        `;
    }

    // --- Core Data Fetcher & UI Renderer ---

    /**
     * Loads all data for the donations dashboard (Metrics, Campaigns, Donations).
     */
    async function loadDonationsDashboard() {
        // Show loading indicator in tables
        const campaignsBody = $('#campaigns-table-body');
        const donationsBody = $('#donations-table-body');
        campaignsBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading campaigns...</td></tr>');
        donationsBody.empty().html('<tr><td colspan="5" class="text-center text-Primary f14-regular">Loading donations...</td></tr>');
        $('#donations-pagination').empty();

        try {
            const res = await fetchApi('/api/admin/donations.php', {
                search: currentSearchTerm,
                filter: currentCampaignFilter, // Campaign filter only applies to campaigns
                donations_page: currentDonationsPage
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load dashboard data.', 'error');
                campaignsBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading campaigns.</td></tr>');
                donationsBody.html('<tr><td colspan="5" class="text-center text-Red f14-regular">Error loading donations.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderCampaignsTable(data.campaigns);
            renderDonationsTable(data.donations);
            renderDonationsPagination(data.donations_page, data.donations_total_pages);
            
            // Rerun counter animation for a fresh count
            if (typeof window.counter === 'function') {
                window.counter();
            }

        } catch (error) {
            console.error('API Error loading donations dashboard:', error);
            window.showToast('A network error occurred while fetching data.', 'error');
            campaignsBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            donationsBody.html('<tr><td colspan="5" class="text-center text-Red f14-regular">Network error.</td></tr>');
        }
    }

    /**
     * Updates the four metric cards.
     */
    function updateMetrics(m) {
        if (!m) return;
        
        const formatCurrency = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        $('#total-campaigns').text(m.total_campaigns ?? 0);
        $('#active-campaigns').text(m.active_campaigns ?? 0);
        $('#total-raised').text(formatCurrency(m.total_raised ?? 0));
        $('#top-campaign-name').text(m.top_campaign_name ?? 'None');
        $('#top-campaign-amount').text(formatCurrency(m.top_campaign_amount ?? 0));
    }
    
    /**
     * Renders the Campaigns table.
     */
    function renderCampaignsTable(campaigns) {
        const tableBody = $('#campaigns-table-body');
        tableBody.empty();

        if (!campaigns || campaigns.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No campaigns found matching current criteria.</td></tr>');
            return;
        }

        campaigns.forEach(camp => {
            const row = `
                <tr data-campaign-id="${camp.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Campaign">
                        <span class="f14-bold text-Primary">${camp.name}</span>
                    </td>
                    <td class="f14-regular" data-label="Goal">$${window.formatCurrency(camp.goal_amount)}</td>
                    <td class="f14-regular" data-label="Raised">$${window.formatCurrency(camp.raised_amount)}</td>
                    <td class="f14-regular" data-label="Progress">
                        ${renderProgressBar(camp.progress)}
                    </td>
                    <td class="f14-regular " data-label="Status">${renderStatusBadge(camp.status)}</td>
                    <td class="f14-regular" data-label="Actions">
                        <div class="dropdown default style-fill actions-dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item action-edit-campaign" data-id="${camp.id}">
                                        Edit Campaign
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
     * Renders the Donations History table.
     */
    function renderDonationsTable(donations) {
        const tableBody = $('#donations-table-body');
        tableBody.empty();

        if (!donations || donations.length === 0) {
            tableBody.html('<tr><td colspan="5" class="text-center text-Gray f14-regular">No donations recorded.</td></tr>');
            return;
        }

        donations.forEach(don => {
            const row = `
                <tr class="tf-table-item">
                    <td class="f14-regular" data-label="Donor">
                        <span class="f14-bold">${don.donor_name}</span>
                        <div class="f12-regular text-Gray">${don.donor_email}</div>
                    </td>
                    <td class="f14-regular" data-label="Campaign">${don.campaign_name}</td>
                    <td class="f14-bold text-Green" data-label="Amount">$${window.formatCurrency(don.amount)}</td>
                    <td class="f14-regular text-Gray" data-label="Date">${don.date.split(' ')[0]}</td>
                    <td class="f14-regular" data-label="Status">
                        ${renderStatusBadge(don.status)}
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });
    }
    
    /**
     * Renders the pagination for the Donations table.
     */
    function renderDonationsPagination(currentPage, totalPages) {
        const paginationEl = $('#donations-pagination');
        paginationEl.empty();
        if (totalPages <= 1) return;

        // Note: Logic copied from wallets.js for consistency
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
        
        paginationEl.append(`<button class="tf-button style-1 f12-bold px-3 py-1 page-link ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}">Next</button>`);
        
        // Bind click events
        paginationEl.find('.page-link').on('click', function(e) {
            e.preventDefault();
            if ($(this).hasClass('disabled')) return;
            const newPage = $(this).data('page');
            currentDonationsPage = newPage;
            loadDonationsDashboard(); // Reload data with new page
        });
    }

    // --- Modal Logic ---

    /** Prepares the modal for a new campaign entry. */
    function setupAddCampaignModal() {
        $('#modal-title').text('Add New Campaign');
        $('#campaign-form')[0].reset();
        $('#campaign-id').val(''); // Clear hidden ID
        $('#campaign-raised').val('0'); // Default raised to 0
        $('#campaign-status').val('active'); // Default status
        $('#current-image-preview').attr('src', '').hide();
        // Remove file input value explicitly for Chrome/Firefox
        $('#campaign-image').val(''); 
        $('.modal-confirm-btn').text('Save Campaign').removeClass('bg-Accent text-Black').addClass('bg-Primary text-White');
        window.showModal('#campaign-modal');
    }

    /** Fetches campaign data and populates the Edit Campaign modal. */
    async function setupEditCampaignModal(id) {
        try {
            window.showToast('Loading campaign details...', 'info');
            const res = await fetchApi('/api/admin/donations.php', {
                fetch: 'campaign_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const camp = res.data;
                
                $('#modal-title').text(`Edit Campaign: ${camp.name}`);
                $('#campaign-id').val(camp.id);
                $('#campaign-name').val(camp.name);
                $('#campaign-desc').val(camp.description);
                $('#campaign-goal').val(camp.goal_amount);
                $('#campaign-raised').val(camp.raised_amount);
                $('#campaign-status').val(camp.status);
                
                // Set image preview
                if (camp.image && camp.image !== '/assets/images/charity/placeholder.jpg') {
                    $('#current-image-preview').attr('src', camp.image).show();
                } else {
                    $('#current-image-preview').attr('src', '').hide();
                }
                
                // Clear the file input when opening for edit
                $('#campaign-image').val(''); 

                $('.modal-confirm-btn').text('Update Campaign').removeClass('bg-Primary text-White').addClass('bg-Accent text-Black');
                window.showModal('#campaign-modal');
                window.showToast('Campaign details loaded.', 'success');
            } else {
                window.showToast(res.message || 'Failed to load campaign details for editing.', 'error');
            }
        } catch (error) {
            console.error('Error loading campaign details:', error);
            window.showToast('Network error while fetching details.', 'error');
        }
    }

    // --- Interaction Binding ---
    function bindInteractions() {
        
        // 1. Add Campaign Button
        $('#add-campaign-btn').on('click', function() {
            setupAddCampaignModal();
        });
        
        // 2. Edit Campaign Action Button (delegated click handler)
        $(document).on('click', '.action-edit-campaign', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            setupEditCampaignModal(id);
        });

        // 3. Campaign/Donation Search form submission (Applies to both)
        $('.form-search').on('submit', function(e) {
            e.preventDefault();
            
            // Determine which search box was used
            const searchInput = $(this).find('input[type="text"]').attr('id');
            currentSearchTerm = $(`#${searchInput}`).val().trim();
            
            currentDonationsPage = 1; // Reset donations page on new search
            loadDonationsDashboard();
        });
        
        // 4. Campaign Filter dropdown
        $('.dropdown-menu a[data-filter]').on('click', function(e) {
            e.preventDefault();
            const filterVal = $(this).data('filter');
            
            // Update the button text to show current filter
            $(this).closest('.dropdown').find('button').html(`<span class="iconify" data-icon="mdi:filter"></span> ${$(this).text()}`);
            
            currentCampaignFilter = filterVal;
            loadDonationsDashboard();
        });


        // 5. Add/Edit Campaign Form Submission
        $('#campaign-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#campaign-id').val();
            const isEdit = !!id;
            const action = isEdit ? 'edit_campaign' : 'add_campaign';
            
            // Use FormData for file upload capability
            const formData = new FormData();
            formData.append('action', action);
            if (isEdit) {
                 formData.append('id', id);
            }
            formData.append('name', $('#campaign-name').val());
            formData.append('description', $('#campaign-desc').val());
            formData.append('goal', $('#campaign-goal').val());
            formData.append('raised', $('#campaign-raised').val());
            formData.append('status', $('#campaign-status').val());
            
            // Append file if selected
            const imageFile = $('#campaign-image')[0].files[0];
            if (imageFile) {
                formData.append('campaign_image', imageFile);
            }

            window.showToast(`${isEdit ? 'Updating' : 'Creating'} campaign...`, 'info', 5000);

            try {
                // Use vanilla fetch for file upload (must use FormData and omit Content-Type header)
                const apiUrl = '/api/admin/donations.php';

                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData 
                });

                const res = await response.json();

                if (res.status === 'success') {
                    window.showToast(res.message, 'success');
                    window.closeModal('#campaign-modal');
                    loadDonationsDashboard(); // Refresh data
                } else {
                    window.showToast(res.message || `${isEdit ? 'Update' : 'Creation'} failed.`, 'error');
                }
            } catch (error) {
                console.error('Campaign form submission error:', error);
                window.showToast('A network error occurred or the server failed to respond.', 'error');
            }
        });

        // 6. Handle image preview
        $('#campaign-image').on('change', function() {
            const [file] = this.files;
            if (file) {
                $('#current-image-preview').attr('src', URL.createObjectURL(file)).show();
            } else if (!$('#campaign-id').val()) {
                // Hide if no file selected and it's a new campaign
                $('#current-image-preview').attr('src', '').hide();
            }
        });
    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        
        // Initial load of the dashboard data
        loadDonationsDashboard(); 

        // Expose refresh function globally if needed
        window.refreshDonationsDashboard = loadDonationsDashboard;
    });

})(jQuery);