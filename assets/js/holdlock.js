/* =======================================================
   holdlock.js — HealthRunCare HoldLock Frontend Logic (Final Fixed)
   ======================================================= */

document.addEventListener('DOMContentLoaded', function () {
  // === DOM ELEMENTS ===
  const walletBalanceEl = document.getElementById('wallet-balance');
  const planSelect = document.getElementById('plan-select');
  const lockAmountEl = document.getElementById('lock-amount');
  const lockTermEl = document.getElementById('lock-period');
  const lockRoiEl = document.getElementById('expected-roi');
  const lockBtn = document.getElementById('lock-btn');
  const lockForm = document.getElementById('holdlock-form');

  const cardActiveLocks = document.getElementById('card-active-locks');
  const cardTotalLocked = document.getElementById('card-total-locked');
  const cardRoiEarned = document.getElementById('card-roi-earned');
  const cardNextUnlock = document.getElementById('card-next-unlock');

  const activeLocksTbody = document.getElementById('active-holdlocks-table-body');
  const maturedLocksTbody = document.getElementById('matured-holdlocks-table-body');

  window.__hrc_holdlock_plans = window.__hrc_holdlock_plans || [];

  // === INITIAL LOAD ===
  loadSummary();
  loadPlans();
  loadActiveLocks();
  loadMaturedLocks();

  // === PLAN SELECTION HANDLER ===
  window.updateHoldlockDetails = function () {
    const selectedOption = planSelect?.options[planSelect.selectedIndex];
    const planId = selectedOption ? parseInt(selectedOption.value) : null;

    if (!planId) {
      lockTermEl.value = '';
      lockRoiEl.value = '';
      lockAmountEl.placeholder = 'Enter amount';
      lockAmountEl.value = '';
      lockAmountEl.removeAttribute('min');
      lockAmountEl.removeAttribute('max');
      lockBtn.disabled = true;
      return;
    }

    const plan = window.__hrc_holdlock_plans.find(p => p.id === planId);
    if (!plan) {
      const dataTerm = selectedOption?.getAttribute('data-lock') || '';
      const dataRoi = selectedOption?.getAttribute('data-roi') || '';
      lockTermEl.value = dataTerm;
      lockRoiEl.value = dataRoi;
      lockAmountEl.placeholder = 'Enter amount';
      lockBtn.disabled = !dataTerm || !dataRoi;
      return;
    }

    // Assign lock details
    lockTermEl.value = plan.lock_period || plan.duration_text || (plan.duration_days ? plan.duration_days + ' days' : '');
    lockRoiEl.value = plan.roi_percent ? `${plan.roi_percent}%` : (plan.roi || '');

    // --- CLEAN FIX FOR MIN/MAX PLACEHOLDER ---
    let minVal = plan.min || plan.min_deposit || 0;
    let maxVal = plan.max_deposit === 'Unlimited' ? null : (plan.max || plan.max_deposit || 0);

    // Strip formatting like "$10,000"
    if (typeof minVal === 'string') minVal = parseFloat(minVal.replace(/[$,]/g, '')) || 0;
    if (typeof maxVal === 'string' && maxVal !== 'Unlimited') maxVal = parseFloat(maxVal.replace(/[$,]/g, '')) || 0;

    // Set numeric input limits
    if (!isNaN(minVal)) lockAmountEl.min = minVal;
    if (!isNaN(maxVal) && maxVal > 0) lockAmountEl.max = maxVal;

    // Format placeholder display
    const pmin = minVal ? `$${Number(minVal).toLocaleString()}` : '';
    const pmax = maxVal ? `$${Number(maxVal).toLocaleString()}` : 'Unlimited';
    lockAmountEl.placeholder = pmax === 'Unlimited' ? `Min: ${pmin}` : `${pmin} - ${pmax}`;

    // ✅ Autofill the minimum value for convenience
    lockAmountEl.value = minVal || '';

    lockBtn.disabled = false;
  };

  // === START HOLDLOCK FORM SUBMIT ===
  if (lockForm) {
    lockForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (!lockBtn) return;

      lockBtn.disabled = true;
      toggleLoader(true);

      const planId = parseInt(planSelect.value || 0);
      const amount = parseFloat(lockAmountEl?.value || 0);

      if (!planId) {
        showToast('Please select a HoldLock plan.', 'error');
        toggleLoader(false);
        lockBtn.disabled = false;
        return;
      }
      if (!amount || amount <= 0) {
        showToast('Enter a valid amount to lock.', 'error');
        toggleLoader(false);
        lockBtn.disabled = false;
        return;
      }

      const selectedPlan = window.__hrc_holdlock_plans.find(p => p.id === planId);
      if (selectedPlan) {
        let min = selectedPlan.min || selectedPlan.min_deposit || 0;
        let max = selectedPlan.max_deposit === 'Unlimited' ? Infinity : (selectedPlan.max || selectedPlan.max_deposit || Infinity);

        if (typeof min === 'string') min = parseFloat(min.replace(/[$,]/g, '')) || 0;
        if (typeof max === 'string' && max !== 'Unlimited') max = parseFloat(max.replace(/[$,]/g, '')) || Infinity;

        if (amount < min) {
          showToast(`Minimum amount for this plan is $${min.toLocaleString()}.`, 'error');
          toggleLoader(false);
          lockBtn.disabled = false;
          return;
        }
        if (amount > max) {
          showToast(`Maximum amount for this plan is $${max.toLocaleString()}.`, 'error');
          toggleLoader(false);
          lockBtn.disabled = false;
          return;
        }
      }

      // API request
      const res = await fetchApi('/api/backend/holdlock.php', {
        action: 'start_holdlock',
        plan_id: planId,
        amount: amount
      });

      toggleLoader(false);

      if (res.status === 'success') {
        showToast('HoldLock started successfully.', 'success');
        lockAmountEl.value = '';
        planSelect.selectedIndex = 0;
        updateHoldlockDetails();
        loadSummary();
        loadActiveLocks();
        loadMaturedLocks();
      } else {
        showToast(res.message || 'Failed to start holdlock.', 'error');
      }

      lockBtn.disabled = false;
    });
  }

  // === SUMMARY (CARDS + WALLET) ===
async function loadSummary() {
  toggleLoader(true);
  const res = await fetchApi('/api/backend/holdlock.php', { action: 'get_summary' });
  toggleLoader(false);

  if (res.status === 'success') {
    const summary = res.data?.summary || {};
    const wallet = res.data?.wallet || {};

    cardActiveLocks.textContent = summary.active_locks_count ?? 0;
    cardTotalLocked.textContent = '$' + Number(summary.total_locked || 0).toFixed(2);
    cardRoiEarned.textContent = '$' + Number(summary.total_roi || 0).toFixed(2);

    // ✅ Format the next maturity date nicely
    if (summary.next_maturity) {
      const date = new Date(summary.next_maturity);
      const formattedDate = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
      cardNextUnlock.textContent = formattedDate;
    } else {
      cardNextUnlock.textContent = '—';
    }

    walletBalanceEl.textContent = '$' + Number(wallet.balance || 0).toFixed(2);
  } else {
    console.warn('Failed to load holdlock summary:', res);
  }
}


  // === LOAD PLANS ===
  async function loadPlans() {
    const res = await fetchApi('/api/backend/holdlock.php', { action: 'get_plans' });
    if (res.status !== 'success') {
      console.warn('Failed to load holdlock plans:', res);
      return;
    }

    const plans = res.data?.plans || [];
    window.__hrc_holdlock_plans = plans;

    const firstOption = planSelect.querySelector('option:first-child');
    planSelect.innerHTML = '';
    if (firstOption) planSelect.appendChild(firstOption);

    plans.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.name || ('Plan ' + p.id);
      opt.setAttribute('data-lock', p.lock_period || '');
      opt.setAttribute('data-roi', p.roi_percent ? p.roi_percent + '%' : (p.roi || ''));
      planSelect.appendChild(opt);
    });

    planSelect.addEventListener('change', updateHoldlockDetails);
    planSelect.selectedIndex = 0;
    updateHoldlockDetails();
  }

  // === LOAD ACTIVE LOCKS ===
  async function loadActiveLocks() {
    toggleLoader(true);
    const res = await fetchApi('/api/backend/holdlock.php', { action: 'get_active' });
    toggleLoader(false);

    activeLocksTbody.innerHTML = '';
    const locks = res.data?.locks || [];

    if (!locks.length) {
      document.getElementById('no-active-holdlocks')?.classList.remove('hidden');
      return;
    } else {
      document.getElementById('no-active-holdlocks')?.classList.add('hidden');
    }

    locks.forEach(l => {
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      tr.innerHTML = `
        <td data-label="Plan Name">${escapeHtml(l.plan_name)}</td>
        <td data-label="Amount Locked">$${Number(l.amount).toFixed(2)}</td>
        <td data-label="ROI (%)" class="text-Green">${Number(l.roi_percent || 0).toFixed(2)}%</td>
        <td data-label="Lock Period">${escapeHtml(l.duration_days ? l.duration_days + ' days' : l.lock_period || '—')}</td>
        <td data-label="Payout Option">${escapeHtml(l.payout_option || 'maturity')}</td>
        <td data-label="Status"><div class="box-status ${l.status === 'locked' ? 'bg-Green' : 'bg-Gray'}"><span>${l.status}</span></div></td>
        <td data-label="Start Date">${l.created_at || '—'}</td>
      `;
      activeLocksTbody.appendChild(tr);
    });
  }

  // === LOAD MATURED LOCKS ===
  async function loadMaturedLocks() {
    const res = await fetchApi('/api/backend/holdlock.php', { action: 'get_matured' });
    maturedLocksTbody.innerHTML = '';
    const matured = res.data?.matured || [];

    if (!matured.length) {
      document.getElementById('matured-holdlocks-empty')?.classList.remove('hidden');
      return;
    } else {
      document.getElementById('matured-holdlocks-empty')?.classList.add('hidden');
    }

    matured.forEach(m => {
      const payout = (parseFloat(m.amount) + parseFloat(m.roi_earned || 0)).toFixed(2);
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      tr.innerHTML = `
        <td data-label="Plan Name">${escapeHtml(m.plan_name)}</td>
        <td data-label="Original Amount">$${Number(m.amount).toFixed(2)}</td>
        <td data-label="ROI Earned" class="text-Green">$${Number(m.roi_earned).toFixed(2)}</td>
        <td data-label="Maturity Date">${m.maturity_date || '—'}</td>
        <td data-label="Total Payout">$${payout}</td>
        <td data-label="Actions">
          <button class="tf-button bg-Green text-White f12-regular hover:bg-Primary" onclick="initiateHoldlockUnlock(${m.id}, false)">Unlock</button>
        </td>
      `;
      maturedLocksTbody.appendChild(tr);
    });
  }

  // === UNLOCK FUNCTION ===
  window.initiateHoldlockUnlock = async function (holdlockId, earlyFlag = false) {
    const confirmMsg = earlyFlag
      ? 'This will perform an early unlock and apply penalty. Proceed?'
      : 'Unlock this matured HoldLock and credit your wallet?';
    if (!confirm(confirmMsg)) return;

    toggleLoader(true);
    const res = await fetchApi('/api/backend/holdlock.php', {
      action: 'unlock',
      holdlock_id: holdlockId,
      early: earlyFlag ? 1 : 0
    });
    toggleLoader(false);

    if (res.status === 'success') {
      showToast('HoldLock unlocked successfully.', 'success');
      loadSummary();
      loadActiveLocks();
      loadMaturedLocks();
    } else {
      showToast(res.message || 'Failed to unlock holdlock.', 'error');
    }
  };

  // === UTILITIES ===
  function escapeHtml(str) {
    if (!str && str !== 0) return '';
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

  // === AUTO REFRESH EVERY 60s ===
  setInterval(() => {
    loadSummary();
    loadActiveLocks();
    loadMaturedLocks();
  }, 60000);
});
