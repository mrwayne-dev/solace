/**
 * Hardened charity.js
 * - Robust selectors
 * - Defensive network handling
 * - Accepts multiple backend response shapes
 * - Caches campaigns to show details without extra calls
 */

;(function ($) {
  "use strict";

  const API_URL = '/api/backend/charity.php';
  const DASHBOARD_API = '/api/backend/dashboard.php';
  let currentLimit = 10;
  let currentBalance = 0;
  let campaignsCache = {}; // id -> campaign object

  // tolerant selectors: try multiple fallbacks
  const $cards = {
    totalDonated: $('.total-donated').first(),
    donationsMade: $('.donations-made').first(),
    topCharity: $('.top-charity').first(),
    lastDate: $('.last-date').first()
  };
  const $campaignsList = $('#campaigns-list').length ? $('#campaigns-list') : $('#charity-campaigns-container').length ? $('#charity-campaigns-container') : $();
  // history table could be tbody id 'donation-history-body' or table id 'history-table' tbody
  const $historyBody = $('#donation-history-body').length ? $('#donation-history-body') : ($('#history-table tbody').length ? $('#history-table tbody') : $());
  const $donationForm = $('#donation-form').length ? $('#donation-form') : $();
  const $charitySelect = $('#charity-select').length ? $('#charity-select') : $();
  const $amountInput = $('#donation-amount').length ? $('#donation-amount') : $();
  const $walletBalance = $('#wallet-balance').length ? $('#wallet-balance') : $('#wallet-balance-display').length ? $('#wallet-balance-display') : $();
  const $noteInput = $('#donation-note'); // may not exist
  const $searchInput = $('#history-search').length ? $('#history-search') : $();
  const $paginationInfo = $('.pagination-info').length ? $('.pagination-info') : $();
  const $donateBtn = $('#donate-btn').length ? $('#donate-btn') : $();

  // Simple fetch wrapper that returns parsed JSON or throws
  function fetchApi(url, payload) {
    return $.ajax({
      url: url,
      method: 'POST',
      data: JSON.stringify(payload),
      contentType: 'application/json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      dataType: 'json'
    }).then(res => res).catch(err => { throw err; });
  }

  // Minimal loader/toast fallbacks if global ones are absent
  if (typeof window.showLoader === 'undefined') {
    window.showLoader = function() {
    const $l = $('#loader');
    if ($l.length) {
      // Ensure all hiding classes are removed
      $l.removeClass('hidden fade-out');
      // Force visible styling (just in case)
      $l.css({
        display: 'flex',
        opacity: 1,
        pointerEvents: 'auto'
      });
    } else {
      // Fallback if loader is missing (adds same visual style as your CSS)
      $('body').append(`
        <div id="loader" style="
          position:fixed;inset:0;display:flex;
          align-items:center;justify-content:center;
          background:rgba(12,12,22,0.6);
          backdrop-filter:blur(6px);
          z-index:12000;
        ">
          <div class="line-loader">
            <div></div><div></div><div></div><div></div><div></div>
          </div>
        </div>
      `);
    }
  };
  }
  if (typeof window.hideLoader === 'undefined') {
    window.hideLoader = function() {
  const $l = $('#loader');
  if ($l.length) {
    // Trigger smooth fade out (uses your .fade-out CSS)
    $l.addClass('fade-out');
    // After fade animation ends, hide it fully
    setTimeout(() => $l.addClass('hidden'), 300);
  }
};
  }
  if (typeof window.showToast === 'undefined') {
    window.showToast = function(msg, type='info') {
      const container = $('#toast-container');
      if (!container.length) $('body').append('<div id="toast-container" />');
      const $t = $(`<div class="toast ${type}"><span class="iconify" data-icon="mdi:information"></span><div class="toast-message">${msg}</div></div>`);
      $('#toast-container').append($t);
      setTimeout(() => $t.remove(), 4200);
    };
  }

  // Safe note getter
  function getNote() {
    if ($noteInput && $noteInput.length) return ($noteInput.val() || '').trim();
    return '';
  }

  // Toggle donate button with clear messaging
  function toggleDonateBtn() {
    if (!$donateBtn.length) return;
    const charityId = parseInt($charitySelect.val() || '', 10);
    const amount = parseFloat($amountInput.val() || '0') || 0;
    const enabled = Number.isInteger(charityId) && charityId > 0 && amount > 0 && amount <= Number(currentBalance);
    $donateBtn.prop('disabled', !enabled);
    if (!enabled) {
      $donateBtn.attr('title', `Select charity and enter amount ≤ $${Number(currentBalance).toFixed(2)}`);
    } else {
      $donateBtn.removeAttr('title');
    }
  }

  // ---------------------------
  // Load summary cards (get_summary)
  // Accepts various response shapes
  // ---------------------------
async function loadSummary() {
  try {
    showLoader();
    const res = await fetchApi(API_URL, { action: 'get_summary' });
    if (res && res.status === 'success' && res.data) {
      const d = res.data;

      if ($cards.totalDonated.length) {
        const cleanTotal = String(d.total_donated).replace(/,/g, '');
        const totalValue = parseFloat(cleanTotal) || 0;
        $cards.totalDonated.text(`$${totalValue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
      }

      if ($cards.donationsMade.length) $cards.donationsMade.text(d.donations_made ?? 0);
      if ($cards.topCharity.length) $cards.topCharity.text(d.top_charity ?? '—');
      if ($cards.lastDate.length) $cards.lastDate.text(d.last_donation_date ?? '—');
    } else {
      console.warn('Summary response unexpected', res);
    }
  } catch (err) {
    console.error('loadSummary error', err);
    showToast('Failed to load summary', 'error');
  } finally {
    hideLoader();
  }
}


  // ---------------------------
  // Load campaigns (get_campaigns OR get_charities)
  // Accepts multiple shapes and computes progress if missing
  // ---------------------------
  async function loadCampaigns() {
    try {
      showLoader();
      const res = await fetchApi(API_URL, { action: 'get_campaigns' }).catch(() => fetchApi(API_URL, { action: 'get_charities' }));
      const dataList = (res && res.data && (res.data.campaigns || res.data.charities)) ? (res.data.campaigns || res.data.charities) : (Array.isArray(res.data) ? res.data : []);
      campaignsCache = {}; // reset
      if (!$campaignsList.length) {
        // nothing to render
        return;
      }
      $campaignsList.empty();
      // populate select safely
      if ($charitySelect.length) {
        $charitySelect.empty().append('<option value="">Select Charity</option>');
      }

      if (!dataList || dataList.length === 0) {
        $campaignsList.append('<div class="col-12"><p class="f14-regular text-Gray text-center">No active charity campaigns found.</p></div>');
        return;
      }

      dataList.forEach(item => {
        // normalize fields
        const id = item.id || item.charity_id || item.campaign_id || 0;
        const name = item.name || item.title || 'Untitled Campaign';
        const description = item.description || item.desc || '';
        const organization = item.organization || item.org || item.partner || '';
        const goal = parseFloat(item.goal || item.goal_amount || item.goal_amount_value || 0) || 0;
        const raised = parseFloat(item.raised || item.raised_amount || 0) || 0;
        const progress = goal > 0 ? Math.min(100, (raised / goal) * 100) : (item.progress || 0);
        const raised_formatted = (typeof item.raised_formatted !== 'undefined') ? item.raised_formatted : raised.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        const goal_formatted = (typeof item.goal_formatted !== 'undefined') ? item.goal_formatted : goal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

        // cache
        campaignsCache[id] = {
          id, name, description, organization, goal, raised, progress: parseFloat(progress.toFixed(2)),
          raised_formatted, goal_formatted, raw: item
        };

        // render card
        const cardHtml = `
          <div class="col-lg-4 col-md-6">
            <div class="plan-card charity-card">
              <div class="plan-header flex justify-between items-center mb-12">
                <h6 class="plan-title">${escapeHtml(name)}</h6>
                <span class="f12-regular text-Gray">${escapeHtml(organization)}</span>
              </div>
              <p class="f12-regular text-Gray mb-16 line-clamp-3">${escapeHtml(description)}</p>
              <div class="progress-section mb-12">
                <div class="flex justify-between items-center mb-6">
                  <span class="f12-medium text-Black">Raised</span>
                  <span class="f12-bold text-Green">$${raised_formatted}</span>
                </div>
                <div class="progress-container mb-6">
                  <div class="progress-bar"><span style="width:${progress}%"></span></div>
                </div>
                <div class="flex justify-between items-center">
                  <span class="f12-medium text-Gray">Goal: $${goal_formatted}</span>
                  <span class="f12-regular text-Primary">${progress.toFixed(1)}% funded</span>
                </div>
              </div>
              <div class="organization-credit mt-12 f12-regular text-GrayDark italic">Powered by <span class="text-Primary fw-bold">${escapeHtml(organization)}</span></div>
            </div>
          </div>
        `;
        $campaignsList.append(cardHtml);

        // option in select
        if ($charitySelect.length) {
          const $opt = $(`<option value="${id}">${escapeHtml(name)} (${progress.toFixed(1)}% funded)</option>`);
          // store data attrs to be used by details show
          $opt.data('campaign', campaignsCache[id]);
          $charitySelect.append($opt);
        }
      });
    } catch (err) {
      console.error('loadCampaigns error', err);
      showToast('Failed to load campaigns', 'error');
    } finally {
      hideLoader();
      toggleDonateBtn();
    }
  }

  // ---------------------------
  // Load wallet balance (tolerant)
  // ---------------------------
  async function loadWalletBalance() {
    try {
      // try common action names
      let res = null;
      try { res = await fetchApi(DASHBOARD_API, { action: 'get_wallet' }); } catch (_) {}
      if (!res) {
        try { res = await fetchApi(DASHBOARD_API, { action: 'get_data' }); } catch (_) {}
      }
      if (!res) {
        // fallback: try charity API for a wallet payload (rare)
        try { res = await fetchApi(API_URL, { action: 'get_wallet' }); } catch (_) {}
      }
      // now interpret response
      if (res && res.status === 'success' && res.data) {
        // attempt different shapes
        let balance = null;
        if (typeof res.data.balance !== 'undefined') balance = parseFloat(res.data.balance);
        else if (res.data.wallet && typeof res.data.wallet.balance !== 'undefined') balance = parseFloat(res.data.wallet.balance);
        else if (res.data.wallet_balance) balance = parseFloat(res.data.wallet_balance);
        if (balance === null || isNaN(balance)) {
          currentBalance = 0;
        } else {
          currentBalance = balance;
        }
        if ($walletBalance.length) {
          // if it's an <input> keep value, if span use text()
          if ($walletBalance.is('input,textarea')) $walletBalance.val(`$${Number(currentBalance).toFixed(2)}`);
          else $walletBalance.text(`$${Number(currentBalance).toFixed(2)}`);
        }
      } else {
        currentBalance = 0;
        if ($walletBalance.length) {
          if ($walletBalance.is('input,textarea')) $walletBalance.val(`$0.00`);
          else $walletBalance.text(`$0.00`);
        }
        console.warn('Wallet fetch returned unexpected result', res);
      }
    } catch (err) {
      console.error('loadWalletBalance error', err);
      currentBalance = 0;
      if ($walletBalance.length) {
        if ($walletBalance.is('input,textarea')) $walletBalance.val(`$0.00`);
        else $walletBalance.text(`$0.00`);
      }
      showToast('Unable to load wallet balance', 'warning');
    } finally {
      toggleDonateBtn();
    }
  }

  // ---------------------------
  // Donation handler
  // ---------------------------
  async function handleDonation() {
    try {
      const charityId = parseInt($charitySelect.val() || '', 10);
      const amount = parseFloat($amountInput.val() || '0') || 0;
      const note = getNote();

      if (!Number.isInteger(charityId) || charityId <= 0 || amount <= 0) {
        showToast('Please select a charity and enter a valid amount.', 'error');
        return;
      }
      if (amount > Number(currentBalance)) {
        showToast('Amount exceeds available balance.', 'error');
        return;
      }

    // show loader animation for 5 seconds for UX realism
    showLoader();
    await new Promise(resolve => setTimeout(resolve, 5000));

    let res = null;
    try {
      res = await fetchApi(API_URL, { action: 'make_donation', charity_id: charityId, amount: amount, note: note });
    } catch (e1) {
      try { res = await fetchApi(API_URL, { action: 'donate', charity_id: charityId, amount: amount, note: note }); }
      catch (e2) { throw e2; }
    }


      if (res && res.status === 'success') {
        showToast(`Your $${amount.toFixed(2)} donation was successful! A confirmation email has been sent.`, 'success');
        // reset form safely
        if ($donationForm.length) $donationForm[0].reset();
        // update cached values: increment raised locally if present
        if (campaignsCache[charityId]) {
          campaignsCache[charityId].raised = Number(campaignsCache[charityId].raised || 0) + Number(amount);
          campaignsCache[charityId].progress = campaignsCache[charityId].goal > 0 ? (campaignsCache[charityId].raised / campaignsCache[charityId].goal) * 100 : campaignsCache[charityId].progress;
        }
        // refresh lists & balances
        await Promise.all([loadSummary(), loadCampaigns(), loadWalletBalance(), loadHistory(1)]);
      } else {
        console.warn('donation failed response', res);
        showToast((res && res.message) ? res.message : 'Donation failed. Try again.', 'error');
      }

    } catch (err) {
      console.error('handleDonation error', err);
      showToast('Network or server error while donating.', 'error');
    } finally {
      hideLoader();
    }
  }

  // ---------------------------
  // Load history (get_history OR history)
  // Accept and map multiple pagination schemas
  // ---------------------------
  async function loadHistory(page = 1, search = '') {
    try {
      showLoader();
      let res = null;
      try { res = await fetchApi(API_URL, { action: 'get_history', page: page, limit: currentLimit, search: search }); }
      catch (_) {
        // fallbacks
        try { res = await fetchApi(API_URL, { action: 'history', offset: (page - 1) * currentLimit, limit: currentLimit, search }); } catch (_) { res = null; }
      }
      if (!res) {
        showToast('Failed to fetch donation history', 'error');
        return;
      }
      if (res.status !== 'success') {
        console.warn('history response not success', res);
        showToast(res.message || 'History load failed', 'error');
        return;
      }
      const history = res.data.history || res.data.items || res.data.donations || [];
      renderHistory(history);

      // pagination handling: try several shapes
      let pag = null;
      if (res.data.pagination) pag = res.data.pagination;
      else if (res.data.total && typeof res.data.total === 'number') {
        const total = res.data.total;
        const current_page = page;
        const total_pages = Math.ceil(total / currentLimit) || 1;
        pag = { current_page, total_pages, total_items: total };
      } else if (res.data.offset !== undefined && res.data.limit !== undefined && res.data.total !== undefined) {
        const current_page = Math.floor(res.data.offset / res.data.limit) + 1;
        pag = { current_page, total_pages: Math.ceil(res.data.total / res.data.limit), total_items: res.data.total };
      }
      if (pag && $paginationInfo.length) renderPagination(pag);
    } catch (err) {
      console.error('loadHistory error', err);
      showToast('Failed to load donation history', 'error');
    } finally {
      hideLoader();
    }
  }

  // ---------------------------
  // Render history table rows
  // ---------------------------
function renderHistory(history) {
  if (!$historyBody || !$historyBody.length) return;
  $historyBody.empty();

  if (!history || history.length === 0) {
    $historyBody.html('<tr><td colspan="5" class="text-center py-4">No donations yet.</td></tr>');
    return;
  }

  history.forEach(item => {
    const reference = item.reference || item.ref || (`HRC-DON-${String(item.id || '').padStart(3, '0')}`);
    const date = item.date || item.created_at || item.created || '';
    const charity = item.charity || item.charity_name || item.charity_title || '';

    // ✅ Safely clean and parse amount to avoid NaN
    const rawAmount = String(item.amount || '').replace(/,/g, '').trim();
    const amountNum = parseFloat(rawAmount) || 0;
    const amount = amountNum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const statusKey = (item.status || 'completed').toString().toLowerCase();
    const statusClass = statusKey === 'pending' ? 'bg-Orange' : (statusKey === 'failed' ? 'bg-Salmon' : 'bg-Green');

    const row = `
      <tr class="tf-table-item">
        <td><div class="f12-medium text-break">${escapeHtml(reference)}</div></td>
        <td><div class="f12-bold">${escapeHtml(date)}</div></td>
        <td><div class="f12-medium">${escapeHtml(charity)}</div></td>
        <td><div class="f12-medium">$${amount}</div></td>
        <td><div class="box-status ${statusClass}">
          <span class="iconify" data-icon="mdi:check-circle" style="color: var(--White);"></span>
          <span class="font-poppins">${escapeHtml(item.status || 'Completed')}</span>
        </div></td>
      </tr>
    `;
    $historyBody.append(row);
  });
}


  // ---------------------------
  // Render pagination info
  // ---------------------------
function renderPagination(pag) {
  if (!$paginationInfo || !$paginationInfo.length) return;
  const current = pag.current_page || 1;
  const total_items = pag.total_items || pag.total || 0;
  const total_pages = pag.total_pages || Math.max(1, Math.ceil(total_items / currentLimit));
  const start = (current - 1) * currentLimit + 1;
  const end = Math.min(current * currentLimit, total_items);

  // Build text + buttons using your existing .pagination + .page-btn styles
  let html = `
    <div class="pagination">
      
  `;

  if (total_pages > 1) {
    for (let i = 1; i <= total_pages; i++) {
      html += `<button class="page-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
    }
  }

  html += `</div>`;
  $paginationInfo.html(html);

  // Attach click listeners
  $paginationInfo.find('.page-btn').off('click').on('click', function () {
    const p = parseInt($(this).data('page'), 10);
    if (p && p !== current) loadHistory(p);
  });
}


  // ---------------------------
  // Show charity details using local cache (faster + reliable)
  // ---------------------------
  function showCharityDetailsLocal(id) {
    const detailsDiv = $('#charity-details');
    const noSel = $('#no-charity-selected');
    if (!detailsDiv.length || !noSel.length) return;
    const c = campaignsCache[id];
    if (!c) {
      // no cached campaign — fallback to API single fetch if necessary
      detailsDiv.hide();
      noSel.show();
      return;
    }
    const imgSrc = (c.raw && (c.raw.image || c.raw.img)) ? (c.raw.image || c.raw.img) : '/assets/images/charity/placeholder.jpg';
    const raisedFmt = c.raised_formatted || Number(c.raised).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    detailsDiv.show();
    noSel.hide();
    $('#charity-image').attr('src', imgSrc).attr('alt', c.name).show();
    $('#charity-name').text(c.name);
    $('#charity-desc').text(c.description);
    $('#charity-raised').text(`$${raisedFmt}`);
  }

  // ---------------------------
  // Helpers
  // ---------------------------
  function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"'`=\/]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[s]));
  }

  // ---------------------------
  // Init interactions and loads
  // ---------------------------
  $(document).ready(function() {
    // search input
    if ($searchInput.length) {
      let t;
      $searchInput.on('input', function() {
        clearTimeout(t);
        const v = $(this).val().trim();
        t = setTimeout(() => loadHistory(1, v), 450);
      });
    }

    // charity change: show details from local cache
    if ($charitySelect.length) {
      $charitySelect.on('change', function() {
        const id = parseInt($(this).val() || '', 10);
        if (id && campaignsCache[id]) showCharityDetailsLocal(id);
        else {
          $('#charity-details').hide();
          $('#no-charity-selected').show();
        }
        toggleDonateBtn();
      });
    }

    // donation amount input toggles donate
    if ($amountInput.length) $amountInput.on('input', toggleDonateBtn);

    // form submit
    if ($donationForm.length) {
      $donationForm.on('submit', function(e) {
        e.preventDefault();
        handleDonation();
      });
    }

    // initial loads (do not block UI)
    loadWalletBalance();
    Promise.all([loadSummary(), loadCampaigns(), loadHistory(1)]).catch(err => console.warn('Initial load error', err));
    // periodic refresh to reflect cron-driven updates
    setInterval(loadCampaigns, 5 * 60 * 1000);
    setInterval(loadSummary, 5 * 60 * 1000);
    setInterval(() => loadHistory(1), 10 * 60 * 1000);
  });

})(jQuery);
