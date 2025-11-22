/**
 * ============================================================
 * HealthRunCare Admin.js (Consolidated and Updated for Email Action)
 * Purpose: Provides Admin Dashboard data loading, UI binding, and quick action logic.
 * ============================================================
 */
;(function ($) {
    "use strict";

    // Make formatCurrency globally available
window.formatCurrency = function(amount) {
    if (amount == null || isNaN(Number(amount))) return '0.00';
    return Number(amount).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

    /* ===================== Core UI Behaviors (Essential Helpers) ===================== */

    var selectImages = function () {
        if ($(".image-select").length > 0) {
            const selectIMG = $(".image-select");
            selectIMG.find("option").each((idx, elem) => {
                const selectOption = $(elem);
                const imgURL = selectOption.attr("data-thumbnail");
                if (imgURL) {
                    selectOption.attr(
                        "data-content",
                        "<img src='%i'/> %s"
                            .replace(/%i/, imgURL)
                            .replace(/%s/, selectOption.text())
                    );
                }
            });
            selectIMG.selectpicker();
        }
    };
    var menuleft = function () {
        if ($('div').hasClass('section-menu-left')) {
            var bt = $(".section-menu-left").find(".has-children");
            bt.on("click", function () {
                var args = { duration: 200 };
                if ($(this).hasClass("active")) {
                    $(this).children(".sub-menu").slideUp(args);
                    $(this).removeClass("active");
                } else {
                    $(".sub-menu").slideUp(args);
                    $(this).children(".sub-menu").slideDown(args);
                    $(".menu-item.has-children").removeClass("active");
                    $(this).addClass("active");
                }
            });
            $('.sub-menu-item').on('click', function(event){
                event.stopPropagation();
            });
        }
    };
    var tabs = function(){
        $('.widget-tabs').each(function(){
            $(this).find('.widget-content-tab').children().hide();
            $(this).find('.widget-content-tab').children(".active").show();
            $(this).find('.widget-menu-tab').find('li').on('click',function(){
                var liActive = $(this).index();
                var contentActive=$(this).siblings().removeClass('active')
                    .parents('.widget-tabs').find('.widget-content-tab')
                    .children().eq(liActive);
                contentActive.addClass('active').fadeIn("slow");
                contentActive.siblings().removeClass('active');
                $(this).addClass('active').parents('.widget-tabs')
                    .find('.widget-content-tab').children().eq(liActive).siblings().hide();
            });
        });
    };
    var collapse_menu = function () {
        $(".button-show-hide").on("click", function () {
            $('.layout-wrap').toggleClass('full-width');
        });
    };
    var showpass = function() {
        $(".show-pass").on("click", function () {
            $(this).toggleClass("active");
            var input = $(this).parents(".password").find(".password-input");
            if (input.attr("type") === "password") {
                input.attr("type", "text");
            } else if (input.attr("type") === "text") {
                input.attr("type", "password");
            }
        });
    };
    var counter = function () {
        var $counter = $(".counter-scroll"); 
        if ($counter.length === 0) return;
        
        if ($().countTo) {
            $('.wallet-card-balance span').each(function () {
                const targetText = $(this).text().replace(/[^0-9.]/g, '');
                const targetValue = Number(targetText);
                if (!isNaN(targetValue) && targetValue > 0) {
                    $(this).countTo({
                        to: targetValue,
                        speed: 1500,
                        decimals: targetText.includes('.') ? 2 : 0 
                    });
                }
            });
        }
    };
    
    // --- Utility Functions ---
    let adminActivityChart = null; 
    
    function formatCurrency(amount) {
        if (amount == null || isNaN(Number(amount))) return '0.00';
        return Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    
    function showToast(message, type = 'info', timeout = 4000) {
        const container = $('#toast-container');
        if (!container.length) {
            console.error('Toast container #toast-container not found in the DOM.');
            return;
        }

        if (container.children().length > 3) {
            container.children().first().remove();
        }

        let icon = (type === 'success') ? 'mdi:check-circle-outline' : (type === 'error' ? 'mdi:alert-circle-outline' : 'mdi:information-outline');

        const toastEl = $(`
            <div class="toast toast-${type}">
                <span class="iconify" data-icon="${icon}" data-width="22" data-height="22"></span>
                <div class="toast-message">${message}</div>
            </div>
        `);

        container.append(toastEl);

        if (timeout > 0) {
            setTimeout(() => {
                toastEl.remove();
            }, timeout);
        }
    }
    
    /**
     * Shows a modal. Uses 'is-open' class to match provided CSS.
     */
    function showModal(selector) {
        const modal = $(selector);
        if (!modal.length) return;
        // FIX: Change 'open' to 'is-open' to match CSS.
        modal.addClass('is-open').attr('aria-hidden', 'false');
        modal.find('[data-modal-close], .button-close-modal, .modal-overlay').off('click').on('click', () => closeModal(selector));
        setTimeout(() => { modal.find('input, button, select, textarea').first().focus(); }, 10);
        $('body').css('overflow', 'hidden');
    }
    
    
    /**
     * Closes a modal and resets the form if it's the email modal.
     */
    function closeModal(selector) {
        const modal = $(selector);
        if (!modal.length) return;
        modal.attr('aria-hidden', 'true');
        setTimeout(() => {
            // FIX: Change 'open' to 'is-open' to match CSS.
            modal.removeClass('is-open');
            // Reset form for good UX
            if (selector === '#email-modal') {
                 $('#email-form')[0]?.reset();
                 $('#email-user-id-group').hide();
                 $('#email-user-id').prop('required', false);
            }
        }, 300);
        $('body').css('overflow', 'auto');

    }
    
    // --- Admin Dashboard Core Functions (Data Loading) ---
    
    var loadAdminDashboardData = async function () {
        try {
            // Calls the correct Admin Endpoint
            const res = await fetchApi('/api/admin/dashboard.php');

            if (res.status !== 'success') {
                showToast(res.message || 'Failed to load Admin Dashboard data.', 'error');
                return;
            }

            const m = res.data.metrics;
            const a = res.data.pending_alerts;
            const txns = res.data.recent_activity;
            const chartData = res.data.chart_data;

            // 1. Update Main Cards
            $('#total-revenue').text(formatCurrency(m.total_revenue ?? 0));
            $('#total-donations').text(formatCurrency(m.total_donations ?? 0));
            $('#active-investments').text(m.active_investments ?? 0);
            $('#total-users').text(m.total_users ?? 0);
            
            // 2. Update Quick Action Alerts/Counts
            const depositBtn = $('a[href="/admin/transactions/pending"]');
            depositBtn.html(`
                <span class="iconify" data-icon="mdi:cash-plus"></span>
                Pending Deposits (${a.deposits ?? 0})
            `);
            const withdrawalBtn = $('a[href="/admin/withdrawals/pending"]');
            withdrawalBtn.html(`
                <span class="iconify" data-icon="mdi:bank-transfer-out"></span>
                Pending Withdrawals (${a.withdrawals ?? 0})
            `);
            
            // Placeholder: Update Activity Info Panel
            $('#peak-activity').text((a.deposits > 0 || a.withdrawals > 0) ? 'Pending Actions' : 'Normal');
            $('#avg-daily-users').text(Math.round((m.total_users ?? 0) / 30) || 0);

            // 3. Update Recent Activity Table
            const tableBody = $('#recent-activity');
            tableBody.empty();

            if (txns && txns.length > 0) {
                txns.forEach(tx => {
                    const statusClass = (tx.status.toLowerCase() === 'completed') ? 'text-green' : (tx.status.toLowerCase() === 'pending' ? 'text-orange' : 'text-red');
                    const row = `
                        <tr>
                            <td class="f14-regular">${tx.date}</td>
                            <td class="f14-regular">${tx.user}</td>
                            <td class="f14-regular">${tx.type}</td>
                            <td class="f14-regular text-Primary">$${formatCurrency(tx.amount)}</td>
                            <td class="f14-regular ${statusClass}">${tx.status ? tx.status.charAt(0).toUpperCase() + tx.status.slice(1).toLowerCase() : ''}
                            </td>
                        </tr>`;
                    tableBody.append(row);
                });
            } else {
                tableBody.append('<tr><td colspan="5" class="text-center text-Gray">No recent system activity.</td></tr>');
            }
            
            // 4. Render Doughnut Chart
            renderActivityChart(chartData);
            
            // 5. Rerun the counter animation for a fresh count
            counter();

        } catch (error) {
            console.error('Error loading Admin Dashboard:', error);
            showToast('An error occurred while loading dashboard data.', 'error');
        }
    };

    function renderActivityChart(data) {
        const ctx = document.getElementById("activityChart");
        if (!ctx) return;
        
        const datasetValues = [
            data.revenue || 0,
            data.donations || 0,
            data.investments || 0,
            data.users || 0 
        ];
        
        const colors = [
            getComputedStyle(document.documentElement).getPropertyValue("--Primary").trim() || '#3F51B5',
            '#4CAF50', 
            '#FFC107', 
            '#9C27B0' 
        ];
        
        if (adminActivityChart instanceof Chart) {
            adminActivityChart.destroy();
        }

        adminActivityChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Revenue", "Donations", "Investments", "Users"],
                datasets: [{
                    data: datasetValues,
                    backgroundColor: colors,
                    borderWidth: 0,
                    cutout: "70%"
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        const chartLabels = ['chart-revenue', 'chart-donations', 'chart-investments', 'chart-users'];
        
        chartLabels.forEach((id, i) => {
            $(`#${id}`).text(`${(datasetValues[i] ?? 0).toFixed(1)}%`);
        });
    }

    // --- Quick Action Bindings ---
    function bindQuickActions() {
        // Only bind the email button now
        $('#send-email-btn').on('click', function() {
            showModal('#email-modal');
        });
        
        // Toggle User ID field based on selection
        $('#email-recipients').on('change', function() {
            const isSpecific = $(this).val() === 'specific';
            if (isSpecific) {
                $('#email-user-id-group').slideDown(200);
                $('#email-user-id').prop('required', true); // Enforce requirement
            } else {
                $('#email-user-id-group').slideUp(200);
                $('#email-user-id').prop('required', false).val(''); // Remove requirement and clear value
            }
        });
        
        // Close modals via ESC key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal('#email-modal');
            }
        });
    }
    
    // --- Send Email Form Handler ---
    function bindEmailForm() {
        $('#email-form').on('submit', async function(e) {
            e.preventDefault();
            
            // Show loader/progress toast immediately
            showToast('Preparing and sending emails. This may take a moment...', 'info', 5000);
            
            const recipient_group = $('#email-recipients').val();
            const user_id = $('#email-user-id').val();
            const subject = $('#email-subject').val();
            const body = $('#email-body').val();
            const priority = $('#email-priority').val();

            if (!recipient_group || recipient_group === '') {
                showToast('Please select a recipient group.', 'warning');
                return;
            }
            if (recipient_group === 'specific' && (!user_id || user_id.trim() === '' || isNaN(user_id))) {
                showToast('Please enter a valid numeric User ID for specific recipients.', 'warning');
                return;
            }
            if (!subject || !body) {
                showToast('Subject and Message Body are required.', 'warning');
                return;
            }

            try {
                
                const payload = {
                    recipient_group: recipient_group,
                    subject: subject,
                    body: body,
                    priority: priority
                };

                if (recipient_group === 'specific') {
                    payload.user_id = user_id.trim();
                }

                // Assuming fetchApi is available globally
                const res = await fetchApi('/api/admin/email.php', payload, 'POST'); 

                if (res.status === 'success') {
                    showToast(res.message, 'success');
                    closeModal('#email-modal');
                } else {
                    showToast(res.message || 'Failed to send email.', 'error');
                }
            } catch (error) {
                console.error('Email submission error:', error);
                showToast('A network error occurred or the server failed to respond.', 'error');
            }
        });
    }

    // --- Initialization ---
    $(function () {
        // Core UI Bindings 
        selectImages();
        menuleft();
        tabs();
        collapse_menu();
        showpass();
        
        // Admin-specific bindings
        bindQuickActions(); 
        bindEmailForm();


        // Open Pending Deposits Modal
        $('a[href="/admin/transactions/pending"]').on('click', function (e) {
            e.preventDefault();
            showModal('#pending-deposits-modal');
        });

        // WHEN MODAL OPENS — Load Pending Deposit Requests
        $('a[href="/admin/transactions/pending"]').on('click', async function (e) {
            e.preventDefault();
            showModal('#pending-deposits-modal');

            try {
                const res = await fetchApi('/api/admin/get_pending_deposits.php', {}, "GET");

                const listEl = $('#pending-deposits-list');
                const emptyEl = $('#no-pending-deposits');

                listEl.empty(); // Clear old rows

                if (res.status === 'success' && res.data.length > 0) {
                    emptyEl.hide();

                    res.data.forEach(dep => {
                        listEl.append(`
                            <tr>
                                <td>${dep.user}</td>
                                <td>$${Number(dep.amount).toLocaleString()}</td>
                                <td>${dep.date}</td>
                                <td>
                                    <button class="complete-deposit-btn bg-Green text-White" data-id="${dep.id}">Complete</button>
                                    <button class="cancel-deposit-btn bg-Accent text-Black" data-id="${dep.id}">Cancel</button>
                                </td>
                            </tr>
                        `);
                    });

                } else {
                    emptyEl.show();
                }

            } catch (err) {
                showToast("Failed to load pending deposits", "error");
            }
        });
        $(document).on('click', '.complete-deposit-btn', function () {
            const id = $(this).data('id');
            showToast(`Deposit #${id} marked for completion (backend TBD)`, 'info');
        });

        $(document).on('click', '.cancel-deposit-btn', function () {
            const id = $(this).data('id');
            showToast(`Deposit #${id} marked for cancellation (backend TBD)`, 'warning');
        });



        // SUBMIT — Save Deposit Address
        $('#set-deposit-address-form').on('submit', async function(e){
            e.preventDefault();

            const method = $('#deposit-method').val();
            const value = $('#deposit-value').val().trim();

            if(!method || !value){
                showToast("Please complete all fields.", "warning");
                return;
            }

            try {
                const res = await fetchApi('/api/admin/save_deposit_address.php', {
                    method: method,
                    value: value
                }, "POST");

                if(res.status === 'success'){
                    showToast(res.message, "success");
                    closeModal('#set-deposit-address-modal');
                } else {
                    showToast(res.message || "Failed to save address.", "error");
                }

            } catch(err){
                console.error(err);
                showToast("Network/server error occurred.", "error");
            }
        });

        // OPEN — View Deposit Addresses Modal
        $('#view-deposit-address-btn').on('click', async function (e) {
            e.preventDefault();

            try {
                const res = await fetchApi('/api/admin/get_deposit_address.php', {}, "GET");

                if (res.status === 'success') {
                    $('#view-cash-mailing').text(res.data.cash_mailing || 'Not set yet');
                    $('#view-wallet-address').text(res.data.wallet_address || 'Not set yet');

                    showModal('#view-deposit-address-modal');

                } else {
                    showToast(res.message || "Could not load addresses", "error");
                }

            } catch (err) {
                showToast("Server error occurred", "error");
            }
        });

        // Initial data load 
        loadAdminDashboardData(); 

        window.refreshDashboard = loadAdminDashboardData;

        // ------- Expose Global Modal Functions -------
        window.showModal = showModal;
        window.closeModal = closeModal;

    });

})(jQuery);