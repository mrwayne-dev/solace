/**
 * FILE: /assets/js/admin/funds_infrastructure.js
 * ============================================================
 * HealthRunCare Admin Funds Infrastructure.js
 * Purpose: Frontend logic for the Admin Infrastructure Fund Management page.
 * Handles: Metrics, Plan CRUD (via infrastructure_plans), Active Fund Allocations.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Global state for tables and search
    let currentActivePage = 1;
    let currentSearchTerm = '';
    
    // --- UI Helpers ---

    /** Renders a status badge for Infrastructure Plans/Contributions. */
    function renderStatusBadge(status) {
        status = status.toLowerCase();

        let badgeClass = 'bg-Gray';
        if (status === 'open' || status === 'active' || status === 'planning') {
            badgeClass = 'bg-Primary';
        } else if (status === 'funded' || status === 'complete' || status === 'completed' || status === 'matured') {
            badgeClass = 'bg-Green';
        } else if (status === 'on-hold' || status === 'unlocked') {
            badgeClass = 'bg-Orange';
        }

        const displayStatus = status.replace('-', ' ');

        return `<div class="box-status ${badgeClass}"><span class="font-poppins key-sort">${displayStatus.charAt(0).toUpperCase() + displayStatus.slice(1)}</span></div>`;
    }

    /** Renders a progress bar. */
    function renderProgressBar(progress) {
        progress = Math.min(100, Math.max(0, Math.round(progress)));
        const color = progress >= 100 ? 'bg-Green' : 'bg-Primary';
        return `
            <div class="progress-bar-container">
                <div class="progress-bar ${color}" style="width: ${progress}%;"></div>
                <div class="progress-text f12-regular text-Black">${progress}%</div>
            </div>
        `;
    }
    
    // --- Core Data Fetcher & UI Renderer ---

    /**
     * Loads all data for the Infrastructure dashboard (Metrics, Plans, Allocations).
     */
    async function loadInfrastructureDashboard() {
        // Show loading indicators
        const projectsBody = $('#infra-projects-body');
        const activeBody = $('#active-infra-body');
        projectsBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading plans...</td></tr>');
        activeBody.empty().html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading allocations...</td></tr>');
        $('#active-infra-pagination').empty();

        try {
            const res = await fetchApi('/api/admin/funds_infrastructure.php', {
                search: currentSearchTerm,
                active_page: currentActivePage
            }, "GET");

            if (res.status !== 'success') {
                window.showToast(res.message || 'Failed to load dashboard data.', 'error');
                projectsBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading plans.</td></tr>');
                activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading allocations.</td></tr>');
                return;
            }

            const data = res.data;
            updateMetrics(data.metrics);
            renderInfrastructureProjectsTable(data.projects); // Table 1: Plans
            renderActiveFundAllocationsTable(data.active_allocations); // Table 2: Contributions
            renderActiveInfrastructurePagination(data.active_page, data.active_total_pages);
            
            // Re-run counter effects if available
            if (typeof window.counter === 'function') {
                window.counter();
            }

        } catch (error) {
            console.error('API Error loading infrastructure dashboard:', error);
            window.showToast('A network error occurred while fetching data.', 'error');
            projectsBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
            activeBody.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Network error.</td></tr>');
        }
    }

    /**
     * Updates the summary cards.
     */
    function updateMetrics(m) {
        if (!m) return;
        const format = window.formatCurrency || ((amount) => Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        
        $('#total-infra').text(format(m.total_infra ?? 0));
        $('#active-projects').text(m.active_projects ?? 0);
        $('#total-allocated').text(format(m.total_allocated ?? 0)); 
        $('#next-milestone').text(m.next_milestone ?? '—');
    }
    
    /**
     * Renders the Infrastructure Projects table (Plans from DB).
     */
    function renderInfrastructureProjectsTable(projects) {
        const tableBody = $('#infra-projects-body');
        tableBody.empty();

        if (!projects || projects.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No Infrastructure Plans defined.</td></tr>');
            return;
        }

        projects.forEach(project => {
            // Use mock data for progress and status, as this comes from infrastructure_plans
            const progress = project.budget > 0 ? Math.round((project.raised / project.budget) * 100) : 0;
            const projectStatus = project.status.toLowerCase();
            const toggleStatus = projectStatus === 'complete' ? 'open' : 'complete';

            const row = `
                <tr data-project-id="${project.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Project Name">
                        <span class="f14-bold text-Primary">${project.name}</span>
                    </td>
                    <td class="f14-regular" data-label="Budget">$${window.formatCurrency(project.budget)}</td>
                    <td class="f14-regular" data-label="Location">${project.location}</td>
                    <td class="f14-regular" data-label="Start Date">${project.start_date}</td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(projectStatus)}</td>
                    <td class="f14-regular" data-label="Actions">
                        <div class="dropdown default style-fill actions-dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item action-edit-infra-project" data-id="${project.id}">
                                        Edit Plan
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item action-toggle-infra-status" data-id="${project.id}" data-status="${toggleStatus}" data-name="${project.name}">
                                        Mark as ${toggleStatus === 'complete' ? 'Complete' : 'Open'}
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
     * Renders the Active Fund Allocations table (Contributions from DB).
     */
    function renderActiveFundAllocationsTable(allocations) {
        const tableBody = $('#active-infra-body');
        tableBody.empty();

        if (!allocations || allocations.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No active fund allocations found.</td></tr>');
            return;
        }

        allocations.forEach(a => {
            const row = `
                <tr data-allocation-id="${a.id}" class="tf-table-item">
                    <td class="f14-regular" data-label="Project">
                        <span class="f14-bold">${a.project_name}</span>
                    </td>
                    <td class="f14-bold" data-label="Allocated">$${window.formatCurrency(a.allocated)}</td>
                    <td class="f14-regular" data-label="Spent">$${window.formatCurrency(a.spent)}</td>
                    <td class="f14-regular text-Red" data-label="Remaining">$${window.formatCurrency(a.remaining)}</td>
                    <td class="f14-regular" data-label="Progress">
                        ${renderProgressBar(a.progress)}
                    </td>
                    <td class="f14-regular" data-label="Status">${renderStatusBadge(a.status)}</td>
                </tr>
            `;
            tableBody.append(row);
        });
    }
    
    /**
     * Renders the pagination for the Active Fund Allocations table.
     */
    function renderActiveInfrastructurePagination(currentPage, totalPages) {
        const paginationEl = $('#active-infra-pagination');
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
            if ($(this).hasClass('bg-Primary')) return; 
            const newPage = $(this).data('page');
            currentActivePage = newPage;
            loadInfrastructureDashboard(); 
        });
    }

    // --- Modal Logic ---

    /** Prepares the modal for a new plan entry. */
    function setupAddProjectModal() {
        $('#infra-project-title').text('Add New Infrastructure Plan');
        $('#infra-project-form')[0].reset();
        $('#infra-project-id').val(''); 
        $('#infra-budget').val(''); 
        $('#infra-location').val('Global'); 
        $('#infra-project-status').val('planning');
        $('#infra-start-date').val(new Date().toISOString().substring(0, 10)); 
        $('.modal-confirm-btn').text('Save Plan').removeClass('bg-Accent text-Black').addClass('bg-Primary text-White');
        
        window.showModal('#infra-project-modal');
    }

    /** Fetches plan data and populates the Edit Plan modal. */
    async function setupEditProjectModal(id) {
        try {
            window.showToast('Loading plan details...', 'info');
            // Query infrastructure_plans for editing modal data
            const res = await fetchApi('/api/admin/funds_infrastructure.php', {
                fetch: 'project_details',
                id: id
            }, "GET");

            if (res.status === 'success') {
                const plan = res.data;
                
                $('#infra-project-title').text(`Edit Plan: ${plan.name}`);
                $('#infra-project-id').val(plan.id);
                $('#infra-project-name').val(plan.name);
                $('#infra-budget').val(plan.budget);
                $('#infra-location').val(plan.location || 'Global');
                $('#infra-start-date').val(plan.start_date);
                $('#infra-project-status').val(plan.status.toLowerCase()); 
                
                $('.modal-confirm-btn').text('Update Plan').removeClass('bg-Primary text-White').addClass('bg-Accent text-Black');
                window.showModal('#infra-project-modal');
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
        $('#add-infra-project-btn').on('click', function() {
            setupAddProjectModal();
        });
        
        // 2. Edit Plan Action Button (delegated click handler on plans table)
        $(document).on('click', '.action-edit-infra-project', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            setupEditProjectModal(id);
        });
        
        // 3. Toggle Plan Status
        $(document).on('click', '.action-toggle-infra-status', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id');
            const newStatus = $(this).data('status'); 
            const title = $(this).data('name');

            if (!confirm(`Are you sure you want to toggle the status of plan "${title}"? (The status column does not exist in the database and will not be permanently saved.)`)) return;

            // Since the status column doesn't exist, we send a minimal update payload
            const payload = {
                action: 'edit_project', 
                id: id,
                name: title, 
                goal_amount: 1000, // Dummy required value for PHP validation
                status: newStatus
            };

            window.showToast(`Updating plan status...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds_infrastructure.php', payload, "POST");

            if (res.status === 'success') {
                // Since status isn't saved, we just confirm the action and refresh
                window.showToast(res.message, 'success');
                loadInfrastructureDashboard(); 
            } else {
                window.showToast(res.message || `Status update failed.`, 'error');
            }
        });
        
        // 4. Plan Form Submission (Add/Edit Plan)
        $('#infra-project-form').on('submit', async function(e) {
            e.preventDefault();
            
            const id = $('#infra-project-id').val();
            const isEdit = !!id;
            const action = isEdit ? 'edit_project' : 'add_project';
            
            const payload = {
                action: action,
                id: id,
                name: $('#infra-project-name').val(),
                goal_amount: parseFloat($('#infra-budget').val()), 
            };

            // Basic client-side validation
            if (!payload.name || payload.goal_amount <= 0) {
                 window.showToast('Please ensure Plan Name and Budget are valid.', 'error');
                 return;
            }

            window.showToast(`${isEdit ? 'Updating' : 'Creating'} Infrastructure Plan...`, 'info', 5000);
            
            const res = await fetchApi('/api/admin/funds_infrastructure.php', payload, "POST");

            if (res.status === 'success') {
                window.showToast(res.message, 'success');
                window.closeModal('#infra-project-modal');
                loadInfrastructureDashboard(); 
            } else {
                window.showToast(res.message || `${isEdit ? 'Update' : 'Creation'} failed.`, 'error');
            }
        });

        // 5. Active Allocations Pagination 
        $(document).on('click', '#active-infra-pagination .page-link', function(e) {
            e.preventDefault();
            const newPage = $(this).data('page');
            currentActivePage = newPage;
            loadInfrastructureDashboard(); 
        });
    }

    // --- Initialization ---
    $(function () {
        bindInteractions();
        loadInfrastructureDashboard(); 

        window.refreshInfrastructureDashboard = loadInfrastructureDashboard;
    });

})(jQuery);