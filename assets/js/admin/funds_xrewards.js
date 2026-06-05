/* =======================================================
   funds_xrewards.js — TitanXHoldings Admin X-Rewards Logic
   ======================================================= */

(function ($) {
    const ENDPOINT = '/api/admin/rewards.php';

    $(function () {
        loadAll();
        bindInteractions();
    });

    async function loadAll() {
        loadMetrics();
        loadProducts();
        loadOrders();
    }

    function fmt(n) { return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

    async function loadMetrics() {
        const res = await fetchApi(ENDPOINT, { action: 'get_metrics' });
        if (res.status !== 'success') return;
        const m = res.data?.metrics || {};
        $('#total-revenue').text(fmt(m.total_revenue));
        $('#active-products').text(m.active_products ?? 0);
        $('#pending-orders').text(m.pending_orders ?? 0);
        $('#delivered-orders').text(m.delivered_orders ?? 0);
    }

    async function loadProducts() {
        const body = $('#xrewards-products-body');
        body.html('<tr><td colspan="6" class="text-center text-Primary f14-regular">Loading products...</td></tr>');

        const res = await fetchApi(ENDPOINT, { action: 'get_products' });
        if (res.status !== 'success') {
            body.html('<tr><td colspan="6" class="text-center text-Red f14-regular">Error loading products.</td></tr>');
            return;
        }
        const products = res.data?.products || [];
        if (!products.length) {
            body.html('<tr><td colspan="6" class="text-center text-Gray f14-regular">No products in catalog.</td></tr>');
            return;
        }
        body.empty();
        products.forEach(p => {
            const stock = p.stock === null || p.stock === undefined ? 'Unlimited' : p.stock;
            const statusColor = p.status === 'active' ? 'bg-Green' : p.status === 'out_of_stock' ? 'bg-Yellow' : 'bg-Gray';
            body.append(`
                <tr data-product-id="${p.id}" class="tf-table-item">
                    <td data-label="Product"><span class="f14-bold text-Primary">${escapeHtml(p.product_name)}</span></td>
                    <td data-label="Retail">$${fmt(p.retail_price)}</td>
                    <td data-label="Member" class="text-Green">$${fmt(p.reward_price)}</td>
                    <td data-label="Stock">${escapeHtml(String(stock))}</td>
                    <td data-label="Status"><div class="box-status ${statusColor}"><span>${escapeHtml(p.status)}</span></div></td>
                    <td data-label="Actions">
                        <button class="tf-button bg-Accent text-Black f12-regular action-edit-product" data-id="${p.id}">Edit</button>
                        <button class="tf-button ${p.status === 'active' ? 'bg-Yellow' : 'bg-Green'} text-White f12-regular action-toggle-product" data-id="${p.id}" data-current="${p.status}">${p.status === 'active' ? 'Deactivate' : 'Activate'}</button>
                    </td>
                </tr>`);
        });
    }

    async function loadOrders() {
        const body = $('#xrewards-orders-body');
        body.html('<tr><td colspan="7" class="text-center text-Primary f14-regular">Loading orders...</td></tr>');

        const res = await fetchApi(ENDPOINT, { action: 'get_orders' });
        if (res.status !== 'success') {
            body.html('<tr><td colspan="7" class="text-center text-Red f14-regular">Error loading orders.</td></tr>');
            return;
        }
        const orders = res.data?.orders || [];
        if (!orders.length) {
            body.html('<tr><td colspan="7" class="text-center text-Gray f14-regular">No orders yet.</td></tr>');
            return;
        }
        body.empty();
        orders.forEach(o => {
            const statusColor = o.status === 'delivered' ? 'bg-Green'
                : o.status === 'shipped' ? 'bg-Primary'
                : o.status === 'pending' ? 'bg-Yellow'
                : 'bg-Red';

            let actions = '';
            if (o.status === 'pending') {
                actions = `
                    <button class="tf-button bg-Primary text-White f12-regular action-update-status" data-id="${o.id}" data-status="shipped">Mark Shipped</button>
                    <button class="tf-button bg-Red text-White f12-regular action-cancel-order" data-id="${o.id}">Cancel</button>`;
            } else if (o.status === 'shipped') {
                actions = `<button class="tf-button bg-Green text-White f12-regular action-update-status" data-id="${o.id}" data-status="delivered">Mark Delivered</button>`;
            } else {
                actions = '<span class="f12-regular text-Gray">—</span>';
            }

            body.append(`
                <tr class="tf-table-item">
                    <td data-label="User">${escapeHtml(o.user_name || '—')}<div class="f12-regular text-Gray">${escapeHtml(o.user_email || '')}</div></td>
                    <td data-label="Product">${escapeHtml(o.product_name || '—')}</td>
                    <td data-label="Qty">${escapeHtml(o.quantity)}</td>
                    <td data-label="Total">$${fmt(o.total_price)}</td>
                    <td data-label="Reference"><span class="f12-regular">${escapeHtml(o.reference || '—')}</span></td>
                    <td data-label="Status"><div class="box-status ${statusColor}"><span>${escapeHtml(o.status)}</span></div></td>
                    <td data-label="Actions">${actions}</td>
                </tr>`);
        });
    }

    function openProductModal(product) {
        const isEdit = !!product;
        $('#xrewards-product-title').text(isEdit ? `Edit Product: ${product.product_name}` : 'Add Reward Product');
        $('#xrewards-product-id').val(isEdit ? product.id : '');
        $('#xrewards-product-name').val(isEdit ? product.product_name : '');
        $('#xrewards-description').val(isEdit ? (product.description || '') : '');
        $('#xrewards-retail-price').val(isEdit ? product.retail_price : '');
        $('#xrewards-stock').val(isEdit && product.stock !== null ? product.stock : '');
        const imgPath = isEdit ? (product.image_path || '') : '';
        $('#xrewards-image-path').val(imgPath);
        $('#xrewards-image-preview').attr('src', imgPath || '/assets/images/avatar/default.png');
        $('#xrewards-image-file').val('');
        $('#xrewards-product-status').val(isEdit ? product.status : 'active');
        if (window.showModal) window.showModal('#xrewards-product-modal');
        else $('#xrewards-product-modal').addClass('show').show();
    }

    function closeModal() {
        if (window.closeModal) window.closeModal('#xrewards-product-modal');
        else $('#xrewards-product-modal').removeClass('show').hide();
    }

    function bindInteractions() {
        $('#add-xrewards-product-btn').on('click', () => openProductModal(null));
        $('.button-close-modal').on('click', closeModal);

        // --- Product image upload ---
        $('#xrewards-image-choose').on('click', () => $('#xrewards-image-file').click());
        $('#xrewards-image-file').on('change', async function () {
            const file = this.files && this.files[0];
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                showToast('Image must be 2 MB or smaller.', 'error');
                this.value = '';
                return;
            }
            const btn = $('#xrewards-image-choose');
            btn.prop('disabled', true);
            try {
                const fd = new FormData();
                fd.append('image', file);
                const r = await fetch('/api/admin/upload_reward_image.php', {
                    method: 'POST', body: fd, credentials: 'include',
                });
                const data = await r.json();
                if (data.status === 'success' && data.data?.image_path) {
                    $('#xrewards-image-path').val(data.data.image_path);
                    $('#xrewards-image-preview').attr('src', data.data.image_path);
                    showToast('Image uploaded.', 'success');
                } else {
                    showToast(data.message || 'Upload failed.', 'error');
                }
            } catch (err) {
                console.error('Reward image upload error', err);
                showToast('Network error during upload.', 'error');
            } finally {
                btn.prop('disabled', false);
                this.value = '';
            }
        });

        $(document).on('click', '.action-edit-product', async function () {
            const id = $(this).data('id');
            const res = await fetchApi(ENDPOINT, { action: 'get_products' });
            if (res.status !== 'success') return;
            const product = (res.data?.products || []).find(p => parseInt(p.id) === parseInt(id));
            if (product) openProductModal(product);
        });

        $(document).on('click', '.action-toggle-product', async function () {
            const id = $(this).data('id');
            const current = $(this).data('current');
            const next = current === 'active' ? 'inactive' : 'active';
            const res = await fetchApi(ENDPOINT, { action: 'toggle_status', id, status: next });
            if (res.status === 'success') {
                showToast('Product status updated.', 'success');
                loadProducts();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to update status.', 'error');
            }
        });

        $('#xrewards-product-form').on('submit', async function (e) {
            e.preventDefault();
            const id = $('#xrewards-product-id').val();
            const isEdit = !!id;
            const stock = $('#xrewards-stock').val();
            const payload = {
                action: isEdit ? 'edit_product' : 'add_product',
                id: id,
                product_name: $('#xrewards-product-name').val(),
                description: $('#xrewards-description').val(),
                retail_price: parseFloat($('#xrewards-retail-price').val()),
                stock: stock === '' ? null : parseInt(stock),
                image_path: $('#xrewards-image-path').val(),
                status: $('#xrewards-product-status').val()
            };
            const res = await fetchApi(ENDPOINT, payload);
            if (res.status === 'success') {
                showToast(isEdit ? 'Product updated.' : 'Product added.', 'success');
                closeModal();
                loadProducts();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to save product.', 'error');
            }
        });

        $(document).on('click', '.action-update-status', async function () {
            const id = $(this).data('id');
            const status = $(this).data('status');
            const res = await fetchApi(ENDPOINT, { action: 'update_order_status', id, status });
            if (res.status === 'success') {
                showToast(`Order marked ${status}.`, 'success');
                loadOrders();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to update order.', 'error');
            }
        });

        $(document).on('click', '.action-cancel-order', async function () {
            const id = $(this).data('id');
            if (!confirm('Cancel this order and refund the user?')) return;
            const reason = prompt('Reason for cancelling (optional):', '') || '';
            const res = await fetchApi(ENDPOINT, { action: 'admin_cancel_order', id, reason });
            if (res.status === 'success') {
                showToast('Order cancelled and refunded.', 'success');
                loadOrders();
                loadMetrics();
            } else {
                showToast(res.message || 'Failed to cancel order.', 'error');
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
