/* =======================================================
   funds_xweekly.js — TitanXHoldings Admin X-Weekly Logic
   ======================================================= */

(function ($) {
    const ENDPOINT = '/api/admin/funds_xweekly.php';

    $(function () {
        loadAll();
        bindInteractions();
    });

    async function loadAll() {
        loadMetrics();
        loadPlans();
        loadPrograms();
    }

    function fmt(n) { return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    async function loadMetrics() {
        const res = await fetchApi(ENDPOINT, { action: 'get_metrics' });
        if (res.status !== 'success') return;
        const m = res.data?.metrics || {};
        $('#total-invested').text(fmt(m.total_invested));
        $('#total-paid').text(fmt(m.total_paid));
        $('#active-programs').text(m.active_programs ?? 0);
        $('#next-debit').text(m.next_debit ?? '—');
    }

    async function loadPlans() {
        const body = $('#xweekly-plans-body');
        body.html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading plans...</td></tr>');

        const res = await fetchApi(ENDPOINT, { action: 'get_plans' });
        if (res.status !== 'success') {
            body.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading plans.</td></tr>');
            return;
        }
        const plans = res.data?.plans || [];
        if (!plans.length) {
            body.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No X-Weekly plans defined.</td></tr>');
            return;
        }
        body.empty();
        plans.forEach(p => {
            const max = p.max_weekly === null || p.max_weekly === undefined ? 'Unlimited' : '$' + fmt(p.max_weekly);
            const statusBadge = p.status === 'active'
                ? '<div class="box-status bg-Green"><span>active</span></div>'
                : '<div class="box-status bg-Gray"><span>inactive</span></div>';
            body.append(`
                <tr data-plan-id="${p.id}" class="tf-table-item">
                    <td data-label="Plan Name"><span class="f14-bold text-Primary">${escapeHtml(p.plan_name)}</span></td>
                    <td data-label="ROI">${Number(p.roi_percent).toFixed(2)}%</td>
                    <td data-label="Min Weekly">$${fmt(p.min_weekly)}</td>
                    <td data-label="Max Weekly">${max}</td>
                    <td data-label="Status">${statusBadge}</td>
                    <td data-label="Actions">
                        <button class="tf-button bg-Accent text-Black f12-regular action-edit-plan" data-id="${p.id}">Edit</button>
                        <button class="tf-button ${p.status === 'active' ? 'bg-Yellow' : 'bg-Green'} text-White f12-regular action-toggle-plan" data-id="${p.id}" data-current="${p.status}">${p.status === 'active' ? 'Deactivate' : 'Activate'}</button>
                    </td>
                </tr>`);
        });
    }

    async function loadPrograms() {
        const body = $('#active-xweekly-body');
        body.html('<tr><td colspan="7" class="text-center text-Primary f14-regular">Loading programs...</td></tr>');

        const res = await fetchApi(ENDPOINT, { action: 'get_programs' });
        if (res.status !== 'success') {
            body.html('<tr><td colspan="7" class="text-center text-Red f14-regular">Error loading programs.</td></tr>');
            return;
        }
        const programs = res.data?.programs || [];
        if (!programs.length) {
            body.html('<tr><td colspan="7" class="text-center text-Gray f14-regular">No active programs yet.</td></tr>');
            return;
        }
        body.empty();
        programs.forEach(pr => {
            const statusColor = pr.status === 'active' ? 'bg-Green' : pr.status === 'paused' ? 'bg-Yellow' : 'bg-Red';
            const actions = pr.status === 'cancelled' ? '<span class="f12-regular text-Gray">—</span>' : `
                <button class="tf-button bg-Yellow text-White f12-regular action-pause-program" data-id="${pr.id}">Pause</button>
                <button class="tf-button bg-Red text-White f12-regular action-cancel-program" data-id="${pr.id}">Cancel</button>`;
            body.append(`
                <tr class="tf-table-item">
                    <td data-label="User">${escapeHtml(pr.user_name || '—')}<div class="f12-regular text-Gray">${escapeHtml(pr.user_email || '')}</div></td>
                    <td data-label="Weekly">$${fmt(pr.weekly_amount)}</td>
                    <td data-label="Total Invested">$${fmt(pr.total_invested)}</td>
                    <td data-label="ROI Earned" class="text-Green">$${fmt(pr.total_earned)}</td>
                    <td data-label="Next Debit">${pr.next_debit_date || '—'}</td>
                    <td data-label="Status"><div class="box-status ${statusColor}"><span>${escapeHtml(pr.status)}</span></div></td>
                    <td data-label="Actions">${actions}</td>
                </tr>`);
        });
    }

    // --- Modal helpers
    function openPlanModal(plan) {
        const isEdit = !!plan;
        $('#xweekly-plan-title').text(isEdit ? `Edit Plan: ${plan.plan_name}` : 'Add X-Weekly Plan');
        $('#xweekly-plan-id').val(isEdit ? plan.id : '');
        $('#xweekly-plan-name').val(isEdit ? plan.plan_name : '');
        $('#xweekly-roi-percent').val(isEdit ? plan.roi_percent : '');
        $('#xweekly-min-weekly').val(isEdit ? plan.min_weekly : 50);
        $('#xweekly-max-weekly').val(isEdit && plan.max_weekly !== null ? plan.max_weekly : '');
        $('#xweekly-description').val(isEdit ? (plan.description || '') : '');
        $('#xweekly-plan-status').val(isEdit ? plan.status : 'active');
        if (window.showModal) window.showModal('#xweekly-plan-modal');
        else $('#xweekly-plan-modal').addClass('show').show();
    }

    function closeModal() {
        if (window.closeModal) window.closeModal('#xweekly-plan-modal');
        else $('#xweekly-plan-modal').removeClass('show').hide();
    }

    // --- Bindings
    function bindInteractions() {
        $('#add-xweekly-plan-btn').on('click', () => openPlanModal(null));
        $('.button-close-modal').on('click', closeModal);

        $(document).on('click', '.action-edit-plan', async function () {
            const id = $(this).data('id');
            const res = await fetchApi(ENDPOINT, { action: 'get_plans' });
            if (res.status !== 'success') return;
            const plan = (res.data?.plans || []).find(p => parseInt(p.id) === parseInt(id));
            if (plan) openPlanModal(plan);
        });

        $(document).on('click', '.action-toggle-plan', async function () {
            const id = $(this).data('id');
            const current = $(this).data('current');
            const next = current === 'active' ? 'inactive' : 'active';
            const res = await fetchApi(ENDPOINT, { action: 'toggle_plan', id, status: next });
            if (res.status === 'success') {
                showToast('Plan status updated.', 'success');
                loadPlans();
            } else {
                showToast(res.message || 'Failed to update status.', 'error');
            }
        });

        $('#xweekly-plan-form').on('submit', async function (e) {
            e.preventDefault();
            const id = $('#xweekly-plan-id').val();
            const isEdit = !!id;
            const maxVal = $('#xweekly-max-weekly').val();
            const payload = {
                action: isEdit ? 'edit_plan' : 'add_plan',
                id: id,
                plan_name: $('#xweekly-plan-name').val(),
                roi_percent: parseFloat($('#xweekly-roi-percent').val()),
                min_weekly: parseFloat($('#xweekly-min-weekly').val()),
                max_weekly: maxVal === '' ? null : parseFloat(maxVal),
                description: $('#xweekly-description').val(),
                status: $('#xweekly-plan-status').val()
            };
            const res = await fetchApi(ENDPOINT, payload);
            if (res.status === 'success') {
                showToast(isEdit ? 'Plan updated.' : 'Plan added.', 'success');
                closeModal();
                loadPlans();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to save plan.', 'error');
            }
        });

        $(document).on('click', '.action-pause-program', async function () {
            const id = $(this).data('id');
            const reason = prompt('Reason for pausing (optional):', '') || '';
            const res = await fetchApi(ENDPOINT, { action: 'admin_pause_program', id, reason });
            if (res.status === 'success') {
                showToast('Program paused.', 'success');
                loadPrograms();
            } else {
                showToast(res.message || 'Failed to pause.', 'error');
            }
        });

        $(document).on('click', '.action-cancel-program', async function () {
            const id = $(this).data('id');
            if (!confirm('Cancel this program? Future debits will stop. Existing balance remains.')) return;
            const reason = prompt('Reason for cancelling (optional):', '') || '';
            const res = await fetchApi(ENDPOINT, { action: 'admin_cancel_program', id, reason });
            if (res.status === 'success') {
                showToast('Program cancelled.', 'success');
                loadPrograms();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to cancel.', 'error');
            }
        });
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"'`=\/]/g, s => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
            "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
        })[s]);
    }
})(jQuery);
