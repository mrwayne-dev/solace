/* =======================================================
   xshares.js — TitanXHoldings X-Shares Frontend Logic
   ======================================================= */

document.addEventListener('DOMContentLoaded', function () {
  // === DOM ELEMENTS ===
  const walletBalanceEl = document.getElementById('wallet-balance');
  const sharesAmountEl = document.getElementById('shares-amount');
  const sharesScheduleEl = document.getElementById('shares-schedule');
  const sharesRoiEl = document.getElementById('shares-roi');
  const investMinHint = document.getElementById('invest-min-hint');
  const investAssetIdEl = document.getElementById('invest-asset-id');
  const investAssetNameEl = document.getElementById('invest-asset-name');
  const investAssetTickerEl = document.getElementById('invest-asset-ticker');
  const startBtn = document.getElementById('start-shares-btn');
  const startForm = document.getElementById('xshares-form');

  const cardActiveShares = document.getElementById('card-active-shares');
  const cardSharesInvested = document.getElementById('card-shares-invested');
  const cardSharesEarned = document.getElementById('card-shares-earned');
  const cardNextPayout = document.getElementById('card-next-payout');

  const activeTbody = document.getElementById('active-xshares-table-body');
  const maturedTbody = document.getElementById('matured-xshares-table-body');

  window.__txh_xshares_assets = window.__txh_xshares_assets || [];

  // === INVEST MODAL OPEN/CLOSE ===
  const investModal     = document.getElementById('xshares-invest-modal');
  const closeInvestBtn  = document.getElementById('close-xshares-modal');
  const investOverlay   = document.getElementById('xshares-modal-overlay');

  function closeInvestModal() {
    if (!investModal) return;
    investModal.style.display = 'none';
    document.body.style.overflow = '';
  }
  function openInvestModal(asset) {
    if (!investModal || !asset) return;
    investAssetIdEl.value = asset.id;
    investAssetNameEl.textContent = asset.asset_name || '—';
    investAssetTickerEl.textContent = asset.ticker || '—';
    sharesScheduleEl.value = asset.payout_schedule || 'periodic';
    sharesRoiEl.value = (parseFloat(asset.roi_percent || 0).toFixed(2)) + '%';
    const minVal = parseFloat(asset.min_amount || 0) || 0;
    sharesAmountEl.min = minVal;
    sharesAmountEl.value = minVal;
    sharesAmountEl.placeholder = `Min: $${Number(minVal).toLocaleString()}`;
    if (investMinHint) investMinHint.textContent = `Min: $${Number(minVal).toLocaleString()}`;
    document.getElementById('payout-periodic').checked = true;
    investModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
  closeInvestBtn?.addEventListener('click', closeInvestModal);
  investOverlay?.addEventListener('click', closeInvestModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && investModal?.style.display === 'flex') closeInvestModal();
  });

  // Expose so card-click handler in loadAssets can call it
  window.__openXSharesInvestModal = openInvestModal;

  // === ASSET DROPDOWN SELECTION (inline) ===
  const assetSelect = document.getElementById('asset-select');
  function selectAsset() {
    const id = parseInt(assetSelect?.value || 0);
    const asset = window.__txh_xshares_assets.find(a => parseInt(a.id) === id);

    if (!asset) {
      if (investAssetIdEl) investAssetIdEl.value = '';
      if (sharesScheduleEl) sharesScheduleEl.value = '';
      if (sharesRoiEl) sharesRoiEl.value = '';
      if (sharesAmountEl) {
        sharesAmountEl.value = '';
        sharesAmountEl.placeholder = 'Enter amount';
        sharesAmountEl.removeAttribute('min');
      }
      if (investMinHint) investMinHint.textContent = 'Min: $0';
      if (startBtn) startBtn.disabled = true;
      window.txhRenderPlanPanel && window.txhRenderPlanPanel(null);
      return;
    }

    const minVal = parseFloat(asset.min_amount || 0) || 0;
    if (investAssetIdEl) investAssetIdEl.value = asset.id;
    if (investAssetNameEl) investAssetNameEl.textContent = asset.asset_name || '—';
    if (investAssetTickerEl) investAssetTickerEl.textContent = asset.ticker || '—';
    if (sharesScheduleEl) sharesScheduleEl.value = asset.payout_schedule || 'periodic';
    if (sharesRoiEl) sharesRoiEl.value = parseFloat(asset.roi_percent || 0).toFixed(2) + '%';
    if (sharesAmountEl) {
      sharesAmountEl.min = minVal;
      sharesAmountEl.value = minVal;
      sharesAmountEl.placeholder = `Min: $${Number(minVal).toLocaleString()}`;
    }
    if (investMinHint) investMinHint.textContent = `Min: $${Number(minVal).toLocaleString()}`;
    const pp = document.getElementById('payout-periodic');
    if (pp) pp.checked = true;
    if (startBtn) startBtn.disabled = false;

    window.txhRenderPlanPanel && window.txhRenderPlanPanel({
      name: asset.asset_name,
      roi: parseFloat(asset.roi_percent || 0).toFixed(2) + '%',
      roiLabel: 'Annualised ROI',
      risk: asset.ticker,
      meta: [
        ['Minimum', '$' + Number(minVal).toLocaleString()],
        ['Current price', (asset.current_price != null) ? '$' + Number(asset.current_price).toFixed(2) : '—'],
        ['Payout', asset.payout_schedule || 'periodic'],
        ['Term', asset.duration_days ? asset.duration_days + ' days' : 'Open-ended'],
      ],
      summary: asset.description || asset.company,
    });
  }
  assetSelect?.addEventListener('change', selectAsset);

  // === INITIAL LOAD ===
  loadWallet();
  loadSummary();
  loadAssets();
  loadActive();
  loadMatured();

  // === START SUBMIT ===
  if (startForm) {
    startForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      startBtn.disabled = true;

      const assetId = parseInt(investAssetIdEl.value || 0);
      const amount = parseFloat(sharesAmountEl?.value || 0);
      const payoutOption = document.querySelector('input[name="payout_option"]:checked')?.value || 'periodic';

      if (!assetId) {
        showToast('No asset selected.', 'error');
        startBtn.disabled = false; return;
      }
      if (!amount || amount <= 0) {
        showToast('Enter a valid investment amount.', 'error');
        startBtn.disabled = false; return;
      }

      const selected = window.__txh_xshares_assets.find(a => parseInt(a.id) === assetId);
      if (selected) {
        const min = parseFloat(selected.min_amount || 0);
        if (amount < min) {
          showToast(`Minimum investment for ${selected.asset_name} is $${min.toLocaleString()}.`, 'error');
          startBtn.disabled = false; return;
        }
      }

      toggleLoader(true);
      const res = await fetchApi('/api/backend/xshares.php', {
        action: 'start_xshares',
        asset_id: assetId,
        amount: amount,
        payout_option: payoutOption
      });
      toggleLoader(false);

      if (res.status === 'success') {
        showToast('X-Shares position opened successfully.', 'success');
        sharesAmountEl.value = '';
        closeInvestModal();
        loadWallet();
        loadSummary();
        loadActive();
      } else {
        showToast(res.message || 'Failed to start X-Shares position.', 'error');
      }

      startBtn.disabled = false;
    });
  }

  // === WALLET ===
  async function loadWallet() {
    const res = await fetchApiGet('/api/backend/wallet.php', { action: 'get_wallet_summary' });
    if (res.status === 'success' && walletBalanceEl) {
      walletBalanceEl.textContent = '$' + Number(res.data?.balance || 0).toFixed(2);
    }
  }

  // === SUMMARY ===
  async function loadSummary() {
    const res = await fetchApiGet('/api/backend/xshares.php', { action: 'get_summary' });
    if (res.status !== 'success') return;
    const s = res.data?.summary || {};
    cardActiveShares.textContent = s.active_count ?? 0;
    cardSharesInvested.textContent = '$' + Number(s.total_invested || 0).toFixed(2);
    cardSharesEarned.textContent = '$' + Number(s.total_earned || 0).toFixed(2);
  }

  // === ASSETS GRID (with Invest button per card) ===
  async function loadAssets() {
    const res = await fetchApiGet('/api/backend/xshares.php', { action: 'get_assets' });
    if (res.status !== 'success') return;

    const assets = res.data?.assets || [];
    window.__txh_xshares_assets = assets;

    // Populate inline asset dropdown
    const assetSelectEl = document.getElementById('asset-select');
    if (assetSelectEl) {
      assetSelectEl.innerHTML = '<option value="">Select an Asset</option>';
      assets.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.id;
        opt.textContent = `${a.ticker ? a.ticker + ' — ' : ''}${a.asset_name || ''}`;
        assetSelectEl.appendChild(opt);
      });
    }

    const grid = document.getElementById('xshares-assets-grid');
    if (!grid) return;
    grid.innerHTML = '';

    if (!assets.length) {
      grid.innerHTML = '<div class="col-12 text-center text-Gray f14-regular py-4">No assets available right now.</div>';
      return;
    }

    assets.forEach(a => {
      const min = parseFloat(a.min_amount || 0);
      const roi = parseFloat(a.roi_percent || 0).toFixed(2);
      const price = a.current_price !== null && a.current_price !== undefined
        ? '$' + Number(a.current_price).toFixed(2) : '—';
      const duration = a.duration_days ? a.duration_days + ' days' : 'Open-ended';

      const card = document.createElement('div');
      card.className = 'col-lg-3 col-md-6';
      card.innerHTML = `
        <div class="plan-card">
          <div class="plan-header flex justify-between items-center mb-12">
            <div class="flex items-center gap-2">
              <h6 class="plan-title">${escapeHtml(a.ticker || '')} ${escapeHtml(a.asset_name || '')}</h6>
            </div>
          </div>
          <p class="f12-regular text-Gray mb-12">${escapeHtml(a.company || a.description || 'Fractional equity exposure.')}</p>

          <table class="plan-features">
            <tr><td>Min Investment</td><td>$${Number(min).toLocaleString()}</td></tr>
            <tr><td>Current Price</td><td>${price}</td></tr>
            <tr><td>Target ROI</td><td class="text-Green fw-bold">${roi}%</td></tr>
            <tr><td>Term</td><td>${escapeHtml(duration)}</td></tr>
            <tr><td>Payout Schedule</td><td>${escapeHtml(a.payout_schedule || 'periodic')}</td></tr>
          </table>

          <button type="button" class="tf-button bg-Green text-White f12-bold w-100 mt-12 invest-btn" data-asset-id="${a.id}" style="margin-top: 12px;">
            Invest
          </button>
        </div>`;
      grid.appendChild(card);
    });

    // Bind per-card Invest buttons
    grid.querySelectorAll('.invest-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = parseInt(this.dataset.assetId);
        const asset = window.__txh_xshares_assets.find(a => parseInt(a.id) === id);
        if (asset) openInvestModal(asset);
      });
    });
  }

  // === ACTIVE HOLDINGS ===
  async function loadActive() {
    const res = await fetchApiGet('/api/backend/xshares.php', { action: 'get_active' });
    if (res.status !== 'success') return;

    const holdings = res.data?.holdings || [];
    activeTbody.innerHTML = '';

    const upcoming = holdings.map(h => h.maturity_date).filter(Boolean).sort();
    cardNextPayout.textContent = upcoming.length
      ? new Date(upcoming[0]).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
      : '—';

    if (!holdings.length) {
      document.getElementById('no-active-xshares')?.classList.remove('hidden');
      return;
    }
    document.getElementById('no-active-xshares')?.classList.add('hidden');

    holdings.forEach(h => {
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      tr.innerHTML = `
        <td data-label="Asset">${escapeHtml((h.ticker ? h.ticker + ' ' : '') + (h.asset_name || ''))}</td>
        <td data-label="Amount">$${Number(h.amount).toFixed(2)}</td>
        <td data-label="ROI Earned" class="text-Green">$${Number(h.roi_earned || 0).toFixed(2)}</td>
        <td data-label="Schedule">${escapeHtml(h.payout_schedule || h.payout_option || '—')}</td>
        <td data-label="Maturity">${h.maturity_date || '—'}</td>
        <td data-label="Status"><div class="box-status bg-Green"><span>${escapeHtml(h.status)}</span></div></td>
        <td data-label="Actions">
          <button class="tf-button bg-Primary text-White f12-regular" onclick="unlockXShares(${h.id})">Unlock</button>
        </td>`;
      activeTbody.appendChild(tr);
    });
  }

  // === MATURED HOLDINGS ===
  async function loadMatured() {
    const res = await fetchApiGet('/api/backend/xshares.php', { action: 'get_matured' });
    if (res.status !== 'success') return;
    const holdings = res.data?.holdings || [];
    maturedTbody.innerHTML = '';

    if (!holdings.length) {
      document.getElementById('matured-xshares-empty')?.classList.remove('hidden');
      return;
    }
    document.getElementById('matured-xshares-empty')?.classList.add('hidden');

    holdings.forEach(h => {
      const payout = (parseFloat(h.amount) + parseFloat(h.roi_earned || 0)).toFixed(2);
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      const action = h.status === 'unlocked'
        ? `<span class="f12-regular text-Gray">Settled</span>`
        : `<button class="tf-button bg-Green text-White f12-regular" onclick="unlockXShares(${h.id})">Unlock</button>`;
      tr.innerHTML = `
        <td data-label="Asset">${escapeHtml((h.ticker ? h.ticker + ' ' : '') + (h.asset_name || ''))}</td>
        <td data-label="Original Amount">$${Number(h.amount).toFixed(2)}</td>
        <td data-label="ROI Earned" class="text-Green">$${Number(h.roi_earned || 0).toFixed(2)}</td>
        <td data-label="Maturity Date">${h.maturity_date || '—'}</td>
        <td data-label="Total Payout">$${payout}</td>
        <td data-label="Actions">${action}</td>`;
      maturedTbody.appendChild(tr);
    });
  }

  // === UNLOCK ===
  window.unlockXShares = async function (holdingId) {
    if (!confirm('Unlock this position and credit principal + earnings to wallet?')) return;
    toggleLoader(true);
    const res = await fetchApi('/api/backend/xshares.php', {
      action: 'unlock',
      holding_id: holdingId
    });
    toggleLoader(false);
    if (res.status === 'success') {
      showToast('X-Shares position unlocked successfully.', 'success');
      loadWallet();
      loadSummary();
      loadActive();
      loadMatured();
    } else {
      showToast(res.message || 'Failed to unlock position.', 'error');
    }
  };

  // === UTILITIES ===
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
    loadSummary();
    loadActive();
    loadMatured();
  }, 60000);
});
