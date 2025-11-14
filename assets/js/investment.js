/* =======================================================
   investment.js — Final Production Version (Range Validation Added)
   HealthRunCare Investments Frontend Logic
   ======================================================= */

document.addEventListener('DOMContentLoaded', function () {
  // === DOM ELEMENTS ===
  const walletBalanceEl = document.getElementById('wallet-balance');
  const planSelect = document.getElementById('plan-select');
  const termDuration = document.getElementById('term-duration');
  const expectedRoi = document.getElementById('expected-roi');
  const investBtn = document.getElementById('invest-btn');
  const investForm = document.getElementById('investment-form');

  const cardActive = document.getElementById('card-active-investments');
  const cardROI = document.getElementById('card-total-roi');
  const cardOngoing = document.getElementById('card-ongoing-plans');
  const cardNextMaturity = document.getElementById('card-next-maturity');

  const activeTableBody = document.querySelector('.list-transaction-content tbody');
  const maturedTableBody = document.querySelector('.unlock-plans tbody');

  // === INITIAL LOAD ===
  loadSummary();
  loadPlans();
  loadActiveInvestments();
  loadMaturedInvestments();

  // === PLAN DETAILS (Update term, ROI, range hints) ===
  window.updatePlanDetails = function () {
    const selectedOption = planSelect?.options[planSelect.selectedIndex];
    const id = selectedOption ? parseInt(selectedOption.value) : null;

    const amountEl = document.getElementById('investment-amount');
    if (!id) {
      termDuration.value = '';
      expectedRoi.value = '';
      amountEl.placeholder = 'Enter amount';
      amountEl.removeAttribute('min');
      amountEl.removeAttribute('max');
      investBtn.disabled = true;
      return;
    }

    const cached = window.__hrc_plans?.find(p => p.id === id);
    if (cached) {
      termDuration.value = cached.duration_text || cached.term || (cached.duration_days + ' days');
      expectedRoi.value = (cached.roi_percent != null) ? (cached.roi_percent + '%') : '';

      // Dynamically show range limits
      if (cached.min && cached.max) {
        amountEl.min = cached.min;
        amountEl.max = cached.max;
        amountEl.placeholder = `$${cached.min.toLocaleString()} - $${cached.max.toLocaleString()}`;
      }
      investBtn.disabled = false;
      return;
    }

    // fallback if plan info is incomplete
    const dataTerm = selectedOption?.getAttribute('data-term') || '';
    const dataRoi = selectedOption?.getAttribute('data-roi') || '';
    termDuration.value = dataTerm;
    expectedRoi.value = dataRoi;
    investBtn.disabled = !!(!dataTerm || !dataRoi);
  };

  // === START INVESTMENT FORM SUBMIT ===
  if (investForm) {
    investForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      investBtn.disabled = true;
      toggleLoader(true);

      const planId = parseInt(planSelect.value);
      const amountEl = document.getElementById('investment-amount');
      const amount = parseFloat(amountEl?.value || 0);

      if (!planId) {
        showToast('Please select an investment plan.', 'error');
        investBtn.disabled = false;
        toggleLoader(false);
        return;
      }

      if (!amount || amount <= 0) {
        showToast('Enter a valid investment amount.', 'error');
        investBtn.disabled = false;
        toggleLoader(false);
        return;
      }

      // === New validation: enforce plan min/max range ===
      const selectedPlan = window.__hrc_plans?.find(p => p.id === planId);
      if (selectedPlan && selectedPlan.min && selectedPlan.max) {
        const min = parseFloat(selectedPlan.min);
        const max = parseFloat(selectedPlan.max);
        if (amount < min || amount > max) {
          showToast(`Amount must be between $${min.toLocaleString()} and $${max.toLocaleString()}.`, 'error');
          investBtn.disabled = false;
          toggleLoader(false);
          return;
        }
      }

      const res = await fetchApi('/api/backend/investment.php', {
        action: 'start_investment',
        plan_id: planId,
        amount: amount
      });

      toggleLoader(false);
      if (res.status === 'success') {
        showToast('Investment started successfully.', 'success');
        loadSummary();
        loadActiveInvestments();
        loadMaturedInvestments();
        amountEl.value = '';
        planSelect.selectedIndex = 0;
        updatePlanDetails();
      } else {
        showToast(res.message || 'Failed to start investment.', 'error');
      }
      investBtn.disabled = false;
    });
  }

  // === SUMMARY (CARDS + WALLET) ===
  async function loadSummary() {
    toggleLoader(true);
    const res = await fetchApi('/api/backend/investment.php', { action: 'get_summary' });
    toggleLoader(false);

    if (res.status === 'success') {
      const summary = res.data?.summary || {};
      const wallet = res.data?.wallet || {};

      if (cardActive) cardActive.textContent = '$' + (summary.active_investments_value ?? 0).toFixed(2);
      if (cardROI) cardROI.textContent = '$' + (summary.total_roi ?? 0).toFixed(2);
      if (cardOngoing) cardOngoing.textContent = summary.ongoing_plans_count ?? 0;
      if (cardNextMaturity) cardNextMaturity.textContent = summary.next_maturity ?? '—';
      if (walletBalanceEl) walletBalanceEl.textContent = '$' + (wallet.balance ?? 0).toFixed(2);
    } else {
      console.warn('Failed to load investment summary:', res);
    }
  }

  // === LOAD PLANS ===
  async function loadPlans() {
    const res = await fetchApi('/api/backend/investment.php', { action: 'get_plans' });
    if (res.status === 'success') {
      const plans = res.data?.plans || [];
      window.__hrc_plans = plans;

      if (planSelect) {
        const firstOption = planSelect.querySelector('option:first-child');
        planSelect.innerHTML = '';
        if (firstOption) planSelect.appendChild(firstOption);

        plans.forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.title || p.plan_name || ('Plan ' + p.id);
          const termText = (p.duration_days >= 365)
            ? Math.round(p.duration_days / 365) + ' year(s)'
            : Math.round((p.duration_days || 0) / 30) + ' months';
          opt.setAttribute('data-term', termText);
          opt.setAttribute('data-roi', (p.roi_percent != null) ? (p.roi_percent + '%') : '');
          planSelect.appendChild(opt);
        });

        planSelect.addEventListener('change', updatePlanDetails);
      }
    } else {
      console.warn('Failed to load plans', res);
    }
  }

  // === LOAD ACTIVE INVESTMENTS ===
  async function loadActiveInvestments() {
    toggleLoader(true);
    const res = await fetchApi('/api/backend/investment.php', { action: 'get_active' });
    toggleLoader(false);

    if (res.status === 'success') {
      const investments = res.data?.investments || [];
      if (!activeTableBody) return;
      activeTableBody.innerHTML = '';

      investments.forEach(inv => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td data-label="Plan Name"><div class="f12-medium key-sort">${escapeHtml(inv.plan)}</div></td>
          <td data-label="Amount Invested"><div class="f12-bold key-sort">$${(inv.amount || 0).toFixed(2)}</div></td>
          <td data-label="ROI (%)"><div class="f12-bold text-Green key-sort">${(inv.roi_percent || 0)}%</div></td>
          <td data-label="Term Duration"><div class="f12-medium key-sort">${inv.duration_days} days</div></td>
          <td data-label="Status"><div class="box-status ${inv.status === 'active' ? 'bg-Green' : 'bg-Gray'}"><span class="font-poppins key-sort">${inv.status}</span></div></td>
          <td data-label="Date Started"><div class="f12-medium key-sort">${inv.date_started}</div></td>
        `;
        activeTableBody.appendChild(tr);
      });
    } else {
      console.warn('Failed to load active investments', res);
    }
  }

  // === LOAD MATURED INVESTMENTS ===
  async function loadMaturedInvestments() {
    if (!maturedTableBody) return;
    const res = await fetchApi('/api/backend/investment.php', { action: 'get_matured' });
    if (res.status === 'success') {
      const matured = res.data?.matured || [];
      maturedTableBody.innerHTML = '';

      if (!matured.length) {
        maturedTableBody.innerHTML = `<tr><td colspan="6" class="text-center text-Gray py-3">No mature plans available for unlock at this time.</td></tr>`;
        return;
      }

      matured.forEach(m => {
        const payout = (parseFloat(m.amount) + parseFloat(m.roi_earned || 0)).toFixed(2);
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td>${escapeHtml(m.plan_name)}</td>
          <td>$${(m.amount || 0).toFixed(2)}</td>
          <td class="text-Green">$${(m.roi_earned || 0).toFixed(2)}</td>
          <td>${m.maturity_date}</td>
          <td>$${payout}</td>
          <td><button class="tf-button bg-Green text-White f12-regular hover:bg-Primary" onclick="initiateUnlock(${m.id})">Unlock</button></td>
        `;
        maturedTableBody.appendChild(tr);
      });
    } else {
      console.warn('Failed to load matured investments', res);
    }
  }

  // === UNLOCK INVESTMENT ===
  window.initiateUnlock = async function (investmentId) {
    if (!confirm('Unlock this investment?')) return;
    toggleLoader(true);
    const res = await fetchApi('/api/backend/investment.php', { action: 'unlock_investment', investment_id: investmentId });
    toggleLoader(false);

    if (res.status === 'success') {
      showToast('Investment unlocked successfully. Wallet credited.', 'success');
      loadSummary();
      loadActiveInvestments();
      loadMaturedInvestments();
    } else {
      showToast(res.message || 'Failed to unlock investment.', 'error');
    }
  };

  // === UTILITIES ===
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"'`=\/]/g, function (s) {
      return ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
        "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
      })[s];
    });
  }

  function toggleLoader(show = true) {
    const loader = document.getElementById('preload');
    if (!loader) return;
    loader.style.display = show ? 'flex' : 'none';
  }

  // expose refreshers globally
  window.hrc_loadSummary = loadSummary;
  window.hrc_loadActiveInvestments = loadActiveInvestments;
  window.hrc_loadMaturedInvestments = loadMaturedInvestments;
  window.hrc_loadPlans = loadPlans;
});
