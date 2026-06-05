/* =======================================================
   xweekly.js — TitanXHoldings X-Weekly Frontend Logic
   ======================================================= */

document.addEventListener('DOMContentLoaded', function () {
  // === DOM ELEMENTS ===
  const walletBalanceEl = document.getElementById('wallet-balance');
  const planSelect = document.getElementById('plan-select');
  const weeklyAmountEl = document.getElementById('weekly-amount');
  const weeklyRoiEl = document.getElementById('weekly-roi');
  const weeklyRoiActualEl = document.getElementById('weekly-roi-actual');
  const enrolBtn = document.getElementById('enrol-btn');
  const enrolForm = document.getElementById('xweekly-form');

  const cardActivePrograms = document.getElementById('card-active-programs');
  const cardTotalInvested = document.getElementById('card-total-invested');
  const cardTotalEarned = document.getElementById('card-total-earned');
  const cardNextDebit = document.getElementById('card-next-debit');

  const activeTbody = document.getElementById('active-xweekly-table-body');
  const pausedTbody = document.getElementById('paused-xweekly-table-body');

  window.__txh_xweekly_plans = window.__txh_xweekly_plans || [];

  // === MODAL OPEN/CLOSE ===
  const startModal     = document.getElementById('xweekly-start-modal');
  const openModalBtn   = document.getElementById('open-xweekly-modal');
  const closeModalBtn  = document.getElementById('close-xweekly-modal');
  const modalOverlay   = document.getElementById('xweekly-modal-overlay');

  function openXWeeklyModal() {
    if (!startModal) return;
    startModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
  function closeXWeeklyModal() {
    if (!startModal) return;
    startModal.style.display = 'none';
    document.body.style.overflow = '';
  }
  openModalBtn?.addEventListener('click', openXWeeklyModal);
  closeModalBtn?.addEventListener('click', closeXWeeklyModal);
  modalOverlay?.addEventListener('click', closeXWeeklyModal);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && startModal?.style.display === 'flex') closeXWeeklyModal();
  });

  // Expose for post-submit auto-close
  window.__closeXWeeklyModal = closeXWeeklyModal;

  // === INITIAL LOAD ===
  loadWallet();
  loadSummary();
  loadPlans();
  loadPrograms();

  // === PLAN SELECTION HANDLER ===
  window.updateXWeeklyDetails = function () {
    const selectedOption = planSelect?.options[planSelect.selectedIndex];
    const planId = selectedOption ? parseInt(selectedOption.value) : null;

    if (!planId) {
      weeklyRoiEl.value = '';
      weeklyRoiActualEl.value = '';
      weeklyAmountEl.placeholder = 'Enter amount';
      weeklyAmountEl.value = '';
      weeklyAmountEl.removeAttribute('min');
      weeklyAmountEl.removeAttribute('max');
      enrolBtn.disabled = true;
      window.txhRenderPlanPanel && window.txhRenderPlanPanel(null);
      return;
    }

    const plan = window.__txh_xweekly_plans.find(p => parseInt(p.id) === planId);
    if (!plan) {
      enrolBtn.disabled = true;
      window.txhRenderPlanPanel && window.txhRenderPlanPanel(null);
      return;
    }

    const roiPct = parseFloat(plan.roi_percent) || 0;
    weeklyRoiEl.value = roiPct.toFixed(2) + '%';

    let minVal = parseFloat(plan.min_weekly || 0) || 0;
    let maxVal = plan.max_weekly === null || plan.max_weekly === undefined ? null : parseFloat(plan.max_weekly);

    weeklyAmountEl.min = minVal;
    if (maxVal && maxVal > 0) weeklyAmountEl.max = maxVal;

    const pmin = `$${Number(minVal).toLocaleString()}`;
    const pmax = maxVal ? `$${Number(maxVal).toLocaleString()}` : 'Unlimited';
    weeklyAmountEl.placeholder = maxVal ? `${pmin} - ${pmax}` : `Min: ${pmin}`;
    weeklyAmountEl.value = minVal;

    updateExpectedWeeklyRoi();
    enrolBtn.disabled = false;

    window.txhRenderPlanPanel && window.txhRenderPlanPanel({
      name: plan.plan_name,
      roi: roiPct.toFixed(2) + '%',
      roiLabel: 'Annualised ROI',
      risk: null,
      meta: [
        ['Weekly amount', maxVal ? `${pmin} – ${pmax}` : `${pmin}+`],
        ['Frequency', 'Every week'],
        ['Payout', plan.payout || 'Reinvested weekly'],
      ],
      summary: plan.summary || plan.description,
    });
  };

  function updateExpectedWeeklyRoi() {
    const plan = window.__txh_xweekly_plans.find(p => parseInt(p.id) === parseInt(planSelect.value));
    if (!plan) { weeklyRoiActualEl.value = ''; return; }
    const amount = parseFloat(weeklyAmountEl.value) || 0;
    const roiPct = parseFloat(plan.roi_percent) || 0;
    // Annualised ROI distributed weekly: amount * (roi/100) / 52
    const weekly = amount * (roiPct / 100) / 52;
    weeklyRoiActualEl.value = '$' + weekly.toFixed(2);
  }

  weeklyAmountEl?.addEventListener('input', updateExpectedWeeklyRoi);

  // === ENROL SUBMIT ===
  if (enrolForm) {
    enrolForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      enrolBtn.disabled = true;
      toggleLoader(true);

      const planId = parseInt(planSelect.value || 0);
      const amount = parseFloat(weeklyAmountEl?.value || 0);

      if (!planId) {
        showToast('Please select an X-Weekly plan.', 'error');
        toggleLoader(false);
        enrolBtn.disabled = false;
        return;
      }
      if (!amount || amount <= 0) {
        showToast('Enter a valid weekly amount.', 'error');
        toggleLoader(false);
        enrolBtn.disabled = false;
        return;
      }

      const selectedPlan = window.__txh_xweekly_plans.find(p => parseInt(p.id) === planId);
      if (selectedPlan) {
        const min = parseFloat(selectedPlan.min_weekly || 0);
        const max = selectedPlan.max_weekly === null || selectedPlan.max_weekly === undefined
          ? Infinity
          : parseFloat(selectedPlan.max_weekly);

        if (amount < min) {
          showToast(`Minimum weekly amount for this plan is $${min.toLocaleString()}.`, 'error');
          toggleLoader(false); enrolBtn.disabled = false; return;
        }
        if (amount > max) {
          showToast(`Maximum weekly amount for this plan is $${max.toLocaleString()}.`, 'error');
          toggleLoader(false); enrolBtn.disabled = false; return;
        }
      }

      const res = await fetchApi('/api/backend/xweekly.php', {
        action: 'enrol',
        plan_id: planId,
        weekly_amount: amount
      });

      toggleLoader(false);

      if (res.status === 'success') {
        showToast('X-Weekly program started successfully.', 'success');
        weeklyAmountEl.value = '';
        planSelect.selectedIndex = 0;
        updateXWeeklyDetails();
        loadWallet();
        loadSummary();
        loadPrograms();
        window.__closeXWeeklyModal?.();
      } else {
        showToast(res.message || 'Failed to enrol in X-Weekly.', 'error');
      }

      enrolBtn.disabled = false;
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
    const res = await fetchApiGet('/api/backend/xweekly.php', { action: 'get_summary' });
    if (res.status !== 'success') return;
    const s = res.data?.summary || {};
    cardActivePrograms.textContent = s.active_count ?? 0;
    cardTotalInvested.textContent = '$' + Number(s.total_invested || 0).toFixed(2);
    cardTotalEarned.textContent = '$' + Number(s.total_earned || 0).toFixed(2);
  }

  // === PLANS GRID ===
  async function loadPlans() {
    const res = await fetchApiGet('/api/backend/xweekly.php', { action: 'get_plans' });
    if (res.status !== 'success') return;

    const plans = res.data?.plans || [];
    window.__txh_xweekly_plans = plans;

    const firstOption = planSelect.querySelector('option:first-child');
    planSelect.innerHTML = '';
    if (firstOption) planSelect.appendChild(firstOption);

    plans.forEach(p => {
      const opt = document.createElement('option');
      opt.value = p.id;
      opt.textContent = p.plan_name || ('Plan ' + p.id);
      planSelect.appendChild(opt);
    });

    planSelect.addEventListener('change', updateXWeeklyDetails);
    planSelect.selectedIndex = 0;
    updateXWeeklyDetails();

    const grid = document.getElementById('xweekly-plans-grid');
    if (!grid) return;
    grid.innerHTML = '';

    plans.forEach(p => {
      const min = parseFloat(p.min_weekly || 0);
      const max = p.max_weekly === null || p.max_weekly === undefined ? null : parseFloat(p.max_weekly);
      const roi = parseFloat(p.roi_percent || 0).toFixed(2);

      grid.innerHTML += `
        <div class="col-lg-3 col-md-6">
          <div class="plan-card">
            <div class="plan-header flex justify-between items-center mb-12">
              <div class="flex items-center gap-2">
                <h6 class="plan-title">${escapeHtml(p.plan_name)}</h6>
              </div>
            </div>
            <p class="f12-regular text-Gray mb-12">${escapeHtml(p.description || 'Recurring weekly contribution plan.')}</p>

            <table class="plan-features">
              <tr><td>Min Weekly</td><td>$${Number(min).toLocaleString()}</td></tr>
              <tr><td>Max Weekly</td><td>${max ? '$' + Number(max).toLocaleString() : 'Unlimited'}</td></tr>
              <tr><td>ROI (Annual)</td><td class="text-Green fw-bold">${roi}%</td></tr>
              <tr><td>Schedule</td><td>Every 7 days</td></tr>
              <tr><td>Payout</td><td>${escapeHtml(p.payout || 'Weekly compound')}</td></tr>
            </table>

            <p class="f12-regular text-Gray italic mt-12">${escapeHtml(p.summary || 'Set it once, contribute on autopilot.')}</p>
          </div>
        </div>`;
    });
  }

  // === PROGRAMS ===
  async function loadPrograms() {
    const res = await fetchApiGet('/api/backend/xweekly.php', { action: 'get_active' });
    if (res.status !== 'success') return;

    const programs = res.data?.programs || [];
    activeTbody.innerHTML = '';
    pausedTbody.innerHTML = '';

    const active = programs.filter(p => p.status === 'active');
    const paused = programs.filter(p => p.status === 'paused');

    // Derive next debit
    const upcoming = active
      .map(p => p.next_debit_date)
      .filter(Boolean)
      .sort();
    cardNextDebit.textContent = upcoming.length
      ? new Date(upcoming[0]).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
      : '—';

    if (!active.length) {
      document.getElementById('no-active-xweekly')?.classList.remove('hidden');
    } else {
      document.getElementById('no-active-xweekly')?.classList.add('hidden');
      active.forEach(p => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td data-label="Plan Name">${escapeHtml(p.plan_name || '—')}</td>
          <td data-label="Weekly Amount">$${Number(p.weekly_amount).toFixed(2)}</td>
          <td data-label="ROI (%)" class="text-Green">${Number(p.roi_percent || 0).toFixed(2)}%</td>
          <td data-label="ROI Earned">$${Number(p.total_earned || 0).toFixed(2)}</td>
          <td data-label="Status"><div class="box-status bg-Green"><span>${escapeHtml(p.status)}</span></div></td>
          <td data-label="Actions">
            <button class="tf-button bg-Yellow text-White f12-regular" onclick="pauseXWeekly(${p.id})">Pause</button>
            <button class="tf-button bg-Red text-White f12-regular" onclick="cancelXWeekly(${p.id})">Cancel</button>
          </td>
          <td data-label="Start Date">${p.started_at ? new Date(p.started_at).toLocaleDateString() : '—'}</td>`;
        activeTbody.appendChild(tr);
      });
    }

    if (!paused.length) {
      document.getElementById('no-paused-xweekly')?.classList.remove('hidden');
      return;
    }
    document.getElementById('no-paused-xweekly')?.classList.add('hidden');
    paused.forEach(p => {
      const tr = document.createElement('tr');
      tr.className = 'tf-table-item';
      tr.innerHTML = `
        <td data-label="Plan Name">${escapeHtml(p.plan_name || '—')}</td>
        <td data-label="Total Invested">$${Number(p.total_invested || 0).toFixed(2)}</td>
        <td data-label="ROI Earned" class="text-Green">$${Number(p.total_earned || 0).toFixed(2)}</td>
        <td data-label="Paused On">${p.updated_at ? new Date(p.updated_at).toLocaleDateString() : '—'}</td>
        <td data-label="Resume Available">Anytime</td>
        <td data-label="Actions">
          <button class="tf-button bg-Green text-White f12-regular" onclick="resumeXWeekly(${p.id})">Resume</button>
          <button class="tf-button bg-Red text-White f12-regular" onclick="cancelXWeekly(${p.id})">Cancel</button>
        </td>`;
      pausedTbody.appendChild(tr);
    });
  }

  // === PROGRAM ACTIONS ===
  window.pauseXWeekly = async function (programId) {
    if (!confirm('Pause this X-Weekly program? Debits will stop until you resume.')) return;
    toggleLoader(true);
    const res = await fetchApi('/api/backend/xweekly.php', { action: 'pause', program_id: programId });
    toggleLoader(false);
    if (res.status === 'success') {
      showToast('Program paused.', 'success');
      loadPrograms();
    } else {
      showToast(res.message || 'Failed to pause program.', 'error');
    }
  };

  window.resumeXWeekly = async function (programId) {
    toggleLoader(true);
    const res = await fetchApi('/api/backend/xweekly.php', { action: 'resume', program_id: programId });
    toggleLoader(false);
    if (res.status === 'success') {
      showToast('Program resumed.', 'success');
      loadPrograms();
    } else {
      showToast(res.message || 'Failed to resume program.', 'error');
    }
  };

  window.cancelXWeekly = async function (programId) {
    if (!confirm('Cancel this X-Weekly program? Existing earnings remain in your wallet.')) return;
    toggleLoader(true);
    const res = await fetchApi('/api/backend/xweekly.php', { action: 'cancel', program_id: programId });
    toggleLoader(false);
    if (res.status === 'success') {
      showToast('Program cancelled.', 'success');
      loadWallet();
      loadSummary();
      loadPrograms();
    } else {
      showToast(res.message || 'Failed to cancel program.', 'error');
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

  // === AUTO REFRESH (60s) ===
  setInterval(() => {
    loadWallet();
    loadSummary();
    loadPrograms();
  }, 60000);
});
