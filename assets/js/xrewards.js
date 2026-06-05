/* =======================================================
   xrewards.js — TitanXHoldings X-Rewards Frontend Logic
   ======================================================= */

document.addEventListener('DOMContentLoaded', function () {
  // === DOM ELEMENTS ===
  const walletBalanceEl = document.getElementById('wallet-balance');
  const quantityEl = document.getElementById('reward-quantity');
  const shippingEl = document.getElementById('reward-shipping');
  const unitPriceEl = document.getElementById('reward-unit-price');
  const totalCostEl = document.getElementById('reward-total');
  const placeBtn = document.getElementById('place-order-btn');
  const placeForm = document.getElementById('xrewards-form');
  const orderProductIdEl = document.getElementById('order-product-id');
  const orderProductNameEl = document.getElementById('order-product-name');

  const cardActiveOrders = document.getElementById('card-active-orders');
  const cardTotalSpent = document.getElementById('card-total-spent');
  const cardLoyaltySaved = document.getElementById('card-loyalty-saved');
  const cardNextDelivery = document.getElementById('card-next-delivery');

  const activeTbody = document.getElementById('active-xrewards-table-body');
  const deliveredTbody = document.getElementById('delivered-xrewards-table-body');

  window.__txh_xrewards_products = window.__txh_xrewards_products || [];

  // === ORDER MODAL OPEN/CLOSE ===
  const orderModal     = document.getElementById('xrewards-order-modal');
  const closeOrderBtn  = document.getElementById('close-xrewards-modal');
  const orderOverlay   = document.getElementById('xrewards-modal-overlay');

  function closeOrderModal() {
    if (!orderModal) return;
    orderModal.style.display = 'none';
    document.body.style.overflow = '';
  }
  function openOrderModal(product) {
    if (!orderModal || !product) return;
    orderProductIdEl.value = product.id;
    orderProductNameEl.textContent = product.product_name || '—';
    quantityEl.value = 1;
    quantityEl.min = 1;
    if (product.stock !== null && product.stock !== undefined) {
      quantityEl.max = product.stock;
    } else {
      quantityEl.removeAttribute('max');
    }
    const unit = parseFloat(product.reward_price || 0);
    unitPriceEl.value = '$' + unit.toFixed(2);
    totalCostEl.value = '$' + unit.toFixed(2);
    shippingEl.value = '';
    orderModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
  closeOrderBtn?.addEventListener('click', closeOrderModal);
  orderOverlay?.addEventListener('click', closeOrderModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && orderModal?.style.display === 'flex') closeOrderModal();
  });

  // Recalculate total when quantity changes
  function updateTotal() {
    const id = parseInt(orderProductIdEl.value || 0);
    const product = window.__txh_xrewards_products.find(p => parseInt(p.id) === id);
    if (!product) { totalCostEl.value = ''; return; }
    const qty = parseInt(quantityEl.value) || 0;
    const unit = parseFloat(product.reward_price || 0);
    totalCostEl.value = '$' + (qty * unit).toFixed(2);
  }
  quantityEl?.addEventListener('input', updateTotal);

  // === INITIAL LOAD ===
  loadWallet();
  loadProducts();
  loadOrders();

  // === PLACE ORDER SUBMIT ===
  if (placeForm) {
    placeForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      placeBtn.disabled = true;

      const productId = parseInt(orderProductIdEl.value || 0);
      const qty = parseInt(quantityEl?.value || 0);
      const shipping = (shippingEl?.value || '').trim();

      if (!productId) {
        showToast('No product selected.', 'error');
        placeBtn.disabled = false; return;
      }
      if (!qty || qty <= 0) {
        showToast('Quantity must be at least 1.', 'error');
        placeBtn.disabled = false; return;
      }
      if (!shipping) {
        showToast('Shipping address is required.', 'error');
        placeBtn.disabled = false; return;
      }

      const product = window.__txh_xrewards_products.find(p => parseInt(p.id) === productId);
      if (product && product.stock !== null && product.stock !== undefined && qty > parseInt(product.stock)) {
        showToast(`Only ${product.stock} in stock.`, 'error');
        placeBtn.disabled = false; return;
      }

      toggleLoader(true);
      const res = await fetchApi('/api/backend/xrewards.php', {
        action: 'place_order',
        product_id: productId,
        quantity: qty,
        shipping_details: shipping
      });
      toggleLoader(false);

      if (res.status === 'success') {
        const ref = res.data?.reference || '';
        showToast(`Order placed! Reference: ${ref}`, 'success');
        closeOrderModal();
        loadWallet();
        loadOrders();
      } else {
        showToast(res.message || 'Failed to place order.', 'error');
      }
      placeBtn.disabled = false;
    });
  }

  // === WALLET ===
  async function loadWallet() {
    const res = await fetchApiGet('/api/backend/wallet.php', { action: 'get_wallet_summary' });
    if (res.status === 'success' && walletBalanceEl) {
      walletBalanceEl.textContent = '$' + Number(res.data?.balance || 0).toFixed(2);
    }
  }

  // === PRODUCTS GRID (with Order button per card) ===
  async function loadProducts() {
    const res = await fetchApiGet('/api/backend/xrewards.php', { action: 'get_products' });
    if (res.status !== 'success') return;

    const products = res.data?.products || [];
    window.__txh_xrewards_products = products;

    const grid = document.getElementById('xrewards-products-grid');
    if (!grid) return;

    if (!products.length) {
      grid.innerHTML = '<div class="reward-empty">No products available right now.</div>';
      return;
    }

    grid.innerHTML = products.map(p => {
      const retail = parseFloat(p.retail_price || 0);
      const reward = parseFloat(p.reward_price || 0);
      const discount = Math.round(parseFloat(p.discount_pct || 0));
      const saved = Math.max(0, retail - reward);

      const hasStock = p.stock !== null && p.stock !== undefined;
      const stockNum = hasStock ? parseInt(p.stock) : null;
      const isOut = hasStock && stockNum <= 0;
      let stockLabel = 'In stock';
      let stockLow = false;
      if (isOut) stockLabel = 'Out of stock';
      else if (hasStock && stockNum <= 5) { stockLabel = `Only ${stockNum} left`; stockLow = true; }
      else if (hasStock) stockLabel = `${stockNum} in stock`;

      const imgSrc = p.image_path ? escapeHtml(p.image_path) : '/assets/images/product-placeholder.png';
      const desc = escapeHtml(p.description || 'Reserved for TitanXHoldings members.');

      return `
        <div class="reward-card">
          <div class="reward-card__media">
            ${discount > 0 ? `<span class="reward-card__badge">-${discount}%</span>` : ''}
            <img src="${imgSrc}" alt="${escapeHtml(p.product_name)}" loading="lazy" onerror="this.style.display='none'">
          </div>
          <div class="reward-card__body">
            <h3 class="reward-card__name">${escapeHtml(p.product_name)}</h3>
            <p class="reward-card__desc">${desc}</p>
            <div class="reward-card__price">
              <span class="reward-card__price-now">$${reward.toFixed(2)}</span>
              ${retail > reward ? `<span class="reward-card__price-was">$${retail.toFixed(2)}</span>` : ''}
            </div>
            <div class="reward-card__meta">
              <span class="${stockLow ? 'reward-card__stock-low' : ''}">${stockLabel}</span>
              ${saved > 0 ? `<span>Save $${saved.toFixed(2)}</span>` : ''}
            </div>
            <button type="button" class="reward-card__btn order-btn" data-product-id="${p.id}" ${isOut ? 'disabled' : ''}>
              ${isOut ? 'Out of stock' : 'Order now'}
            </button>
          </div>
        </div>`;
    }).join('');

    // Bind per-card Order buttons
    grid.querySelectorAll('.order-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = parseInt(this.dataset.productId);
        const product = window.__txh_xrewards_products.find(p => parseInt(p.id) === id);
        if (product) openOrderModal(product);
      });
    });
  }

  // === ORDERS ===
  async function loadOrders() {
    const res = await fetchApiGet('/api/backend/xrewards.php', { action: 'get_orders' });
    if (res.status !== 'success') return;

    const orders = res.data?.orders || [];
    activeTbody.innerHTML = '';
    deliveredTbody.innerHTML = '';

    const active = orders.filter(o => o.status !== 'delivered' && o.status !== 'cancelled');
    const delivered = orders.filter(o => o.status === 'delivered');

    cardActiveOrders.textContent = active.length;
    const totalSpent = orders
      .filter(o => o.status !== 'cancelled')
      .reduce((sum, o) => sum + parseFloat(o.total_price || 0), 0);
    cardTotalSpent.textContent = '$' + totalSpent.toFixed(2);

    cardLoyaltySaved.textContent = '$' + orders
      .filter(o => o.status !== 'cancelled' && o.retail_price)
      .reduce((sum, o) => sum + (parseFloat(o.retail_price || 0) - parseFloat(o.unit_price || 0)) * parseInt(o.quantity || 1), 0)
      .toFixed(2);

    const upcoming = active.map(o => o.ordered_at).filter(Boolean).sort();
    cardNextDelivery.textContent = upcoming.length
      ? new Date(upcoming[0]).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
      : '—';

    if (!active.length) {
      document.getElementById('no-active-xrewards')?.classList.remove('hidden');
    } else {
      document.getElementById('no-active-xrewards')?.classList.add('hidden');
      active.forEach(o => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        const cancelBtn = o.status === 'pending'
          ? `<button class="tf-button bg-Red text-White f12-regular" onclick="cancelXRewardsOrder(${o.id})">Cancel</button>`
          : '';
        tr.innerHTML = `
          <td data-label="Reference"><span class="f12-regular">${escapeHtml(o.reference || '—')}</span></td>
          <td data-label="Product">${escapeHtml(o.product_name || '—')}</td>
          <td data-label="Quantity">${escapeHtml(o.quantity)}</td>
          <td data-label="Total">$${Number(o.total_price).toFixed(2)}</td>
          <td data-label="Status"><div class="box-status ${statusClass(o.status)}"><span>${escapeHtml(o.status)}</span></div></td>
          <td data-label="Actions">${cancelBtn}</td>
          <td data-label="Ordered">${o.ordered_at ? new Date(o.ordered_at).toLocaleDateString() : '—'}</td>`;
        activeTbody.appendChild(tr);
      });
    }

    if (!delivered.length) {
      document.getElementById('delivered-xrewards-empty')?.classList.remove('hidden');
      return;
    }
    document.getElementById('delivered-xrewards-empty')?.classList.add('hidden');
    delivered.forEach(o => {
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      tr.innerHTML = `
        <td data-label="Product">${escapeHtml(o.product_name || '—')}</td>
        <td data-label="Order Total">$${Number(o.total_price).toFixed(2)}</td>
        <td data-label="Loyalty Saved">—</td>
        <td data-label="Delivered On">${o.updated_at ? new Date(o.updated_at).toLocaleDateString() : '—'}</td>
        <td data-label="Reference">${escapeHtml(o.reference || '—')}</td>
        <td data-label="Actions"><span class="f12-regular text-Gray">Closed</span></td>`;
      deliveredTbody.appendChild(tr);
    });
  }

  // === CANCEL ORDER ===
  window.cancelXRewardsOrder = async function (orderId) {
    if (!confirm('Cancel this order? Your wallet will be refunded.')) return;
    toggleLoader(true);
    const res = await fetchApi('/api/backend/xrewards.php', {
      action: 'cancel_order',
      order_id: orderId
    });
    toggleLoader(false);
    if (res.status === 'success') {
      showToast('Order cancelled. Refund credited.', 'success');
      loadWallet();
      loadOrders();
    } else {
      showToast(res.message || 'Failed to cancel order.', 'error');
    }
  };

  // === UTILITIES ===
  function statusClass(status) {
    switch ((status || '').toLowerCase()) {
      case 'delivered': return 'bg-Green';
      case 'shipped':   return 'bg-Primary';
      case 'confirmed': return 'bg-Primary';
      case 'pending':   return 'bg-Yellow';
      case 'cancelled': return 'bg-Red';
      default:          return 'bg-Gray';
    }
  }

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"'`=\/]/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
      "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
    })[s]);
  }

  function toggleLoader(show = true) {
    const loader = document.getElementById('loader');
    if (!loader) return;
    loader.classList.toggle('hidden', !show);
  }

  // === AUTO REFRESH ===
  setInterval(() => {
    loadWallet();
    loadOrders();
  }, 60000);
});
