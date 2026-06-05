/* =======================================================
   funds_xshares.js — TitanXHoldings Admin X-Shares Logic
   ======================================================= */

(function ($) {
    const ENDPOINT = '/api/admin/funds_xshares.php';

    $(function () {
        loadAll();
        bindInteractions();
    });

    async function loadAll() {
        loadMetrics();
        loadAssets();
        loadHoldings();
    }

    function fmt(n) { return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    async function loadMetrics() {
        const res = await fetchApi(ENDPOINT, { action: 'get_metrics' });
        if (res.status !== 'success') return;
        const m = res.data?.metrics || {};
        $('#total-invested').text(fmt(m.total_invested));
        $('#total-paid').text(fmt(m.total_paid));
        $('#active-holdings').text(m.active_holdings ?? 0);
        $('#next-maturity').text(m.next_maturity ?? '—');
    }

    async function loadAssets() {
        const body = $('#xshares-assets-body');
        body.html('<tr><td colspan="7" class="text-center text-Primary f14-regular">Loading assets...</td></tr>');

        const res = await fetchApi(ENDPOINT, { action: 'get_assets' });
        if (res.status !== 'success') {
            body.html('<tr><td colspan="7" class="text-center text-Red f14-regular">Error loading assets.</td></tr>');
            return;
        }
        const assets = res.data?.assets || [];
        if (!assets.length) {
            body.html('<tr><td colspan="7" class="text-center text-Gray f14-regular">No X-Shares assets defined.</td></tr>');
            return;
        }
        body.empty();
        assets.forEach(a => {
            const statusBadge = a.status === 'active'
                ? '<div class="box-status bg-Green"><span>active</span></div>'
                : '<div class="box-status bg-Gray"><span>inactive</span></div>';
            const price = a.current_price !== null && a.current_price !== undefined ? '$' + fmt(a.current_price) : '—';
            body.append(`
                <tr data-asset-id="${a.id}" class="tf-table-item">
                    <td data-label="Ticker"><span class="f14-bold text-Primary">${escapeHtml(a.ticker || '—')}</span></td>
                    <td data-label="Asset">${escapeHtml(a.asset_name || '')}<div class="f12-regular text-Gray">${escapeHtml(a.company || '')}</div></td>
                    <td data-label="Price">${price}</td>
                    <td data-label="ROI" class="text-Green">${Number(a.roi_percent).toFixed(2)}%</td>
                    <td data-label="Min">$${fmt(a.min_amount)}</td>
                    <td data-label="Status">${statusBadge}</td>
                    <td data-label="Actions">
                        <button class="tf-button bg-Accent text-Black f12-regular action-edit-asset" data-id="${a.id}">Edit</button>
                        <button class="tf-button ${a.status === 'active' ? 'bg-Yellow' : 'bg-Green'} text-White f12-regular action-toggle-asset" data-id="${a.id}" data-current="${a.status}">${a.status === 'active' ? 'Deactivate' : 'Activate'}</button>
                    </td>
                </tr>`);
        });
    }

    async function loadHoldings() {
        const body = $('#active-xshares-body');
        body.html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading holdings...</td></tr>');

        const res = await fetchApi(ENDPOINT, { action: 'get_holdings' });
        if (res.status !== 'success') {
            body.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading holdings.</td></tr>');
            return;
        }
        const holdings = (res.data?.holdings || []).filter(h => h.status === 'active');
        if (!holdings.length) {
            body.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No active holdings yet.</td></tr>');
            return;
        }
        body.empty();
        holdings.forEach(h => {
            body.append(`
                <tr class="tf-table-item">
                    <td data-label="User">${escapeHtml(h.user_name || '—')}<div class="f12-regular text-Gray">${escapeHtml(h.user_email || '')}</div></td>
                    <td data-label="Asset">${escapeHtml((h.ticker ? h.ticker + ' ' : '') + (h.asset_name || ''))}</td>
                    <td data-label="Amount">$${fmt(h.amount)}</td>
                    <td data-label="ROI Earned" class="text-Green">$${fmt(h.roi_earned)}</td>
                    <td data-label="Maturity">${h.maturity_date || '—'}</td>
                    <td data-label="Status"><div class="box-status bg-Green"><span>${escapeHtml(h.status)}</span></div></td>
                </tr>`);
        });
    }

    function openAssetModal(asset) {
        const isEdit = !!asset;
        $('#xshares-asset-title').text(isEdit ? `Edit Asset: ${asset.asset_name}` : 'Add X-Shares Asset');
        $('#xshares-asset-id').val(isEdit ? asset.id : '');
        $('#xshares-ticker').val(isEdit ? asset.ticker : '');
        $('#xshares-asset-name').val(isEdit ? asset.asset_name : '');
        $('#xshares-company').val(isEdit ? (asset.company || '') : '');
        $('#xshares-current-price').val(isEdit ? asset.current_price : '');
        $('#xshares-roi-percent').val(isEdit ? asset.roi_percent : '');
        $('#xshares-duration-days').val(isEdit && asset.duration_days !== null ? asset.duration_days : '');
        $('#xshares-min-amount').val(isEdit ? asset.min_amount : 100);
        $('#xshares-payout-schedule').val(isEdit ? (asset.payout_schedule || 'monthly') : 'monthly');
        $('#xshares-asset-status').val(isEdit ? asset.status : 'active');
        if (window.showModal) window.showModal('#xshares-asset-modal');
        else $('#xshares-asset-modal').addClass('show').show();
    }

    function closeModal() {
        if (window.closeModal) window.closeModal('#xshares-asset-modal');
        else $('#xshares-asset-modal').removeClass('show').hide();
    }

    function bindInteractions() {
        $('#add-xshares-asset-btn').on('click', () => openAssetModal(null));
        $('.button-close-modal').on('click', closeModal);

        $(document).on('click', '.action-edit-asset', async function () {
            const id = $(this).data('id');
            const res = await fetchApi(ENDPOINT, { action: 'get_assets' });
            if (res.status !== 'success') return;
            const asset = (res.data?.assets || []).find(a => parseInt(a.id) === parseInt(id));
            if (asset) openAssetModal(asset);
        });

        $(document).on('click', '.action-toggle-asset', async function () {
            const id = $(this).data('id');
            const current = $(this).data('current');
            const next = current === 'active' ? 'inactive' : 'active';
            const res = await fetchApi(ENDPOINT, { action: 'toggle_asset', id, status: next });
            if (res.status === 'success') {
                showToast('Asset status updated.', 'success');
                loadAssets();
            } else {
                showToast(res.message || 'Failed to update status.', 'error');
            }
        });

        $('#xshares-asset-form').on('submit', async function (e) {
            e.preventDefault();
            const id = $('#xshares-asset-id').val();
            const isEdit = !!id;
            const dur = $('#xshares-duration-days').val();
            const payload = {
                action: isEdit ? 'edit_asset' : 'add_asset',
                id: id,
                ticker: $('#xshares-ticker').val(),
                asset_name: $('#xshares-asset-name').val(),
                company: $('#xshares-company').val(),
                current_price: parseFloat($('#xshares-current-price').val()),
                roi_percent: parseFloat($('#xshares-roi-percent').val()),
                duration_days: dur === '' ? null : parseInt(dur),
                min_amount: parseFloat($('#xshares-min-amount').val()),
                payout_schedule: $('#xshares-payout-schedule').val(),
                status: $('#xshares-asset-status').val()
            };
            const res = await fetchApi(ENDPOINT, payload);
            if (res.status === 'success') {
                showToast(isEdit ? 'Asset updated.' : 'Asset added.', 'success');
                closeModal();
                loadAssets();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to save asset.', 'error');
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
