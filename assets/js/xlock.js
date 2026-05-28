/* =======================================================
   holdlock.js — HealthRunCare HoldLock Frontend Logic (Final)
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
      lockBtn.disabled = true;
      return;
    }

    // Assign lock details
    lockTermEl.value = plan.lock_period_text || (plan.duration_days ? plan.duration_days + ' days' : '');
    lockRoiEl.value = plan.roi_range || '';

    // === MIN / MAX HANDLING ===
    let minVal = plan.min_amount || 0;
    let maxVal = plan.max_amount === null ? null : plan.max_amount;

    // Numeric conversion
    minVal = parseFloat(minVal) || 0;
    if (maxVal !== null) maxVal = parseFloat(maxVal) || 0;

    // Set numeric input constraints
    lockAmountEl.min = minVal;
    if (maxVal && maxVal > 0) lockAmountEl.max = maxVal;

    // Placeholder text formatting
    const pmin = `$${Number(minVal).toLocaleString()}`;
    const pmax = maxVal ? `$${Number(maxVal).toLocaleString()}` : 'Unlimited';
    lockAmountEl.placeholder = maxVal ? `${pmin} - ${pmax}` : `Min: ${pmin}`;

    // Autofill min amount
    lockAmountEl.value = minVal;
    lockBtn.disabled = false;
  };

  // === START HOLDLOCK SUBMIT ===
  if (lockForm) {
    lockForm.addEventListener('submit', async function (e) {
      e.preventDefault();
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

      // Range validation (frontend safety)
      const selectedPlan = window.__hrc_holdlock_plans.find(p => p.id === planId);
      if (selectedPlan) {
        const min = parseFloat(selectedPlan.min_amount || 0);
        const max = selectedPlan.max_amount === null ? Infinity : parseFloat(selectedPlan.max_amount);

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

  // === SUMMARY DATA (CARDS) ===
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

      // Format next maturity
      cardNextUnlock.textContent = summary.next_maturity
        ? new Date(summary.next_maturity).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
        : '—';

      walletBalanceEl.textContent = '$' + Number(wallet.balance || 0).toFixed(2);
    }
  }

  // === LOAD PLANS + GRID RENDER ===
  async function loadPlans() {
    const res = await fetchApi('/api/backend/holdlock.php', { action: 'get_plans' });
    if (res.status !== 'success') return;

    const plans = res.data?.plans || [];
    window.__hrc_holdlock_plans = plans;

    // Populate select
    const firstOption = planSelect.querySelector('option:first-child');
    planSelect.innerHTML = '';
    if (firstOption) planSelect.appendChild(firstOption);

    plans.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.name || ('Plan ' + p.id);
      planSelect.appendChild(opt);
    });

    planSelect.addEventListener('change', updateHoldlockDetails);
    planSelect.selectedIndex = 0;
    updateHoldlockDetails();

    // === RENDER PLAN CARDS GRID ===
    const grid = document.getElementById('holdlock-plans-grid');
    grid.innerHTML = '';

    plans.forEach(p => {
      grid.innerHTML += `
        <div class="col-lg-3 col-md-6">
          <div class="plan-card">
            <div class="plan-header flex justify-between items-center mb-12">
              <div class="flex items-center gap-2">
                <h6 class="plan-title">${escapeHtml(p.name)}</h6>
              </div>
            </div>
            <p class="f12-regular text-Gray mb-12">${escapeHtml(p.purpose)}</p>

            <table class="plan-features">
              <tr><td>Min Deposit</td><td>$${Number(p.min_amount).toLocaleString()}</td></tr>
              <tr><td>Lock Period</td><td>${escapeHtml(p.lock_period_text)}</td></tr>
              <tr><td>ROI</td><td class="text-Green fw-bold">${escapeHtml(p.roi_range)}</td></tr>
              <tr><td>Max Deposit</td><td>${p.max_amount ? '$' + Number(p.max_amount).toLocaleString() : 'Unlimited'}</td></tr>
              <tr><td>Payout</td><td>${escapeHtml(p.payout)}</td></tr>
              <tr><td>Income Source</td><td>${escapeHtml(p.income_source)}</td></tr>
            </table>

            <p class="f12-regular text-Gray italic mt-12">${escapeHtml(p.summary)}</p>
          </div>
        </div>`;
    });
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
    }

    document.getElementById('no-active-holdlocks')?.classList.add('hidden');

    locks.forEach(l => {
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      tr.innerHTML = `
        <td data-label="Plan Name">${escapeHtml(l.plan_name)}</td>
        <td data-label="Amount Locked">$${Number(l.amount).toFixed(2)}</td>
        <td data-label="ROI (%)" class="text-Green">${Number(l.roi_percent || 0).toFixed(2)}%</td>
        <td data-label="Lock Period">${escapeHtml(l.duration_days ? l.duration_days + ' days' : '—')}</td>
        <td data-label="Payout Option">${escapeHtml(l.payout_option || 'maturity')}</td>
        <td data-label="Status"><div class="box-status ${l.status === 'locked' ? 'bg-Green' : 'bg-Gray'}"><span>${l.status}</span></div></td>
        <td data-label="Start Date">${l.created_at || '—'}</td>`;
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
    }

    document.getElementById('matured-holdlocks-empty')?.classList.add('hidden');

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
          <button class="tf-button bg-Green text-White f12-regular hover:bg-Primary"
            onclick="initiateHoldlockUnlock(${m.id}, false)">Unlock</button>
        </td>`;
      maturedLocksTbody.appendChild(tr);
    });
  }

  // === UNLOCK HOLDLOCK ===
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

  // === AUTO REFRESH (60s) ===
  setInterval(() => {
    loadSummary();
    loadActiveLocks();
    loadMaturedLocks();
  }, 60000);
});
