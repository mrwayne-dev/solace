/* =======================================================
   investment.js — Mining Contract plans (tiered, daily profit)
   Solace Mining Frontend Logic
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

  const activeTableBody = document.getElementById('active-investments-table-body');
  const maturedTableBody = document.querySelector('.unlock-plans tbody');
  const plansGrid = document.getElementById('plans-grid');

  const dailyLabel = (p) => (p.daily_profit_percent != null ? p.daily_profit_percent + '% daily' : '—');
  const termLabel = (p) => ((p.duration_days || 0) + ' days');

  // === INITIAL LOAD ===
  loadSummary();
  loadPlans();
  loadActive();
  loadMatured();

  // === PLAN DETAILS UPDATE ===
  window.updatePlanDetails = function () {
    const selectedOption = planSelect?.options[planSelect.selectedIndex];
    const id = selectedOption ? parseInt(selectedOption.value) : null;

    const amountEl = document.getElementById('investment-amount');
    if (!id) {
      if (termDuration) termDuration.value = '';
      if (expectedRoi) expectedRoi.value = '';
      if (amountEl) {
        amountEl.placeholder = 'Enter amount';
        amountEl.removeAttribute('min');
        amountEl.removeAttribute('max');
      }
      if (investBtn) investBtn.disabled = true;
      window.txhRenderPlanPanel && window.txhRenderPlanPanel(null);
      return;
    }

    const cached = window.__slm_plans?.find(p => p.id === id);
    if (cached) {
      if (termDuration) termDuration.value = termLabel(cached);
      if (expectedRoi) expectedRoi.value = dailyLabel(cached);

      if (amountEl && cached.min) {
        amountEl.min = cached.min;
        if (cached.max != null) amountEl.max = cached.max; else amountEl.removeAttribute('max');
        const maxTxt = cached.max != null ? `$${(+cached.max).toLocaleString()}` : 'unlimited';
        amountEl.placeholder = `$${(+cached.min).toLocaleString()} – ${maxTxt}`;
      }
      if (investBtn) investBtn.disabled = false;

      window.txhRenderPlanPanel && window.txhRenderPlanPanel({
        name: cached.name,
        roi: dailyLabel(cached),
        roiLabel: 'Daily Profit',
        risk: cached.name,
        meta: [
          ['Duration', termLabel(cached)],
          ['Total Return', (cached.total_return_percent != null ? cached.total_return_percent + '%' : '—')],
          ['Min – Max', `$${(+cached.min).toLocaleString()} – ${cached.max != null ? '$' + (+cached.max).toLocaleString() : 'unlimited'}`],
          ['Referral', (cached.referral_commission_percent != null ? cached.referral_commission_percent + '%' : '—')],
        ],
        summary: cached.summary,
      });
      return;
    }
  };

  // === SUBMIT INVESTMENT FORM ===
  if (investForm) {
    investForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      investBtn.disabled = true;
      toggleLoader(true);

      const planId = parseInt(planSelect.value);
      const amountEl = document.getElementById('investment-amount');
      const amount = parseFloat(amountEl?.value || 0);

      if (!planId) {
        showToast('Please select a mining plan.', 'error');
        investBtn.disabled = false; toggleLoader(false); return;
      }
      if (!amount || amount <= 0) {
        showToast('Enter a valid amount.', 'error');
        investBtn.disabled = false; toggleLoader(false); return;
      }

      const selectedPlan = window.__slm_plans?.find(p => p.id === planId);
      if (selectedPlan && selectedPlan.min) {
        const min = parseFloat(selectedPlan.min);
        const max = selectedPlan.max != null ? parseFloat(selectedPlan.max) : null;
        if (amount < min || (max != null && amount > max)) {
          const maxTxt = max != null ? `$${max.toLocaleString()}` : 'unlimited';
          showToast(`Amount must be between $${min.toLocaleString()} and ${maxTxt}.`, 'error');
          investBtn.disabled = false; toggleLoader(false); return;
        }
      }

      const res = await fetchApi('/api/backend/investment.php', {
        action: 'start_investment',
        plan_id: planId,
        amount: amount
      });

      toggleLoader(false);
      if (res.status === 'success') {
        showToast('Mining contract started successfully.', 'success');
        loadSummary();
        loadActive();
        loadMatured();
        // Refresh the shared wallet widgets (Main Wallet / Total Balance, invested,
        // earnings) so the debited balance updates live without a page reload.
        if (typeof window.refreshDashboard === 'function') window.refreshDashboard();
        amountEl.value = '';
        planSelect.selectedIndex = 0;
        updatePlanDetails();
      } else {
        showToast(res.message || 'Failed to start contract.', 'error');
      }
      investBtn.disabled = false;
    });
  }

  // === SUMMARY CARDS ===
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
    }
  }

  // === LOAD PLANS ===
  async function loadPlans() {
    const res = await fetchApi('/api/backend/investment.php', { action: 'get_plans' });
    if (res.status === 'success') {
      const plans = res.data?.plans || [];
      window.__slm_plans = plans;

      // Populate SELECT
      if (planSelect) {
        const firstOption = planSelect.querySelector('option:first-child');
        planSelect.innerHTML = '';
        if (firstOption) planSelect.appendChild(firstOption);

        plans.forEach(p => {
          const opt = document.createElement('option');
          opt.value = p.id;
          opt.textContent = p.name || ('Plan ' + p.id);
          opt.setAttribute('data-term', termLabel(p));
          opt.setAttribute('data-roi', dailyLabel(p));
          planSelect.appendChild(opt);
        });

        planSelect.addEventListener('change', updatePlanDetails);
      }

      // Render Dynamic Cards
      if (plansGrid) {
        plansGrid.innerHTML = '';
        plans.forEach(p => {
          const min = parseFloat(p.min).toLocaleString();
          const max = p.max != null ? '$' + parseFloat(p.max).toLocaleString() : 'Unlimited';
          const amountRange = `$${min} – ${max}`;

          const card = document.createElement('div');
          card.className = 'col-lg-3 col-md-6';
          card.innerHTML = `
            <div class="plan-card">
              <div class="plan-header flex justify-between items-center mb-12">
                <div class="flex items-center gap-2">
                  <span class="iconify" data-icon="${p.icon || 'mdi:pickaxe'}"></span>
                  <h6 class="plan-title">${escapeHtml(p.name)}</h6>
                </div>
              </div>

              <p class="f12-regular text-Gray mb-16">${escapeHtml(p.summary || '')}</p>

              <table class="plan-features">
                <tr><td>Deposit Range</td><td>${amountRange}</td></tr>
                <tr><td>Daily Profit</td><td class="text-Green fw-bold">${p.daily_profit_percent}%</td></tr>
                <tr><td>Duration</td><td>${termLabel(p)}</td></tr>
                <tr><td>Total Return</td><td class="fw-bold">${p.total_return_percent}%</td></tr>
                <tr><td>Referral</td><td>${p.referral_commission_percent}%</td></tr>
              </table>

              <button class="tf-button bg-Primary text-White w-full mt-16" onclick="selectPlanFromCard(${p.id})">Select ${escapeHtml(p.name)}</button>
            </div>
          `;
          plansGrid.appendChild(card);
        });
      }
    } else {
      console.warn('Failed to load plans', res);
    }
  }

  // === LOAD ACTIVE CONTRACTS ===
  async function loadActive() {
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
          <td data-label="Plan"><div class="f12-medium key-sort">${escapeHtml(inv.plan)}</div></td>
          <td data-label="Amount"><div class="f12-bold key-sort">$${(inv.amount || 0).toFixed(2)}</div></td>
          <td data-label="Daily Profit"><div class="f12-bold text-Green key-sort">${(inv.daily_profit_percent || 0)}%</div></td>
          <td data-label="Progress"><div class="f12-medium key-sort">${inv.days_paid}/${inv.duration_days} days</div></td>
          <td data-label="Earned"><div class="f12-bold text-Green key-sort">$${(inv.roi_earned || 0).toFixed(2)}</div></td>
          <td data-label="Status"><div class="box-status ${inv.status === 'active' ? 'bg-Green' : 'bg-Gray'}"><span class="font-poppins key-sort">${inv.status}</span></div></td>
          <td data-label="Date Started"><div class="f12-medium key-sort">${inv.date_started}</div></td>
        `;
        activeTableBody.appendChild(tr);
      });
    }
  }

  // === LOAD COMPLETED CONTRACTS (history) ===
  async function loadMatured() {
    if (!maturedTableBody) return;
    const res = await fetchApi('/api/backend/investment.php', { action: 'get_matured' });
    if (res.status === 'success') {
      const matured = res.data?.matured || [];
      maturedTableBody.innerHTML = '';

      if (!matured.length) {
        maturedTableBody.innerHTML = `<tr><td colspan="5" class="text-center text-Gray py-3">No completed contracts yet.</td></tr>`;
        return;
      }

      matured.forEach(m => {
        const payout = (parseFloat(m.amount) + parseFloat(m.roi_earned || 0)).toFixed(2);
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td>${escapeHtml(m.plan_name)}</td>
          <td>$${(parseFloat(m.amount) || 0).toFixed(2)}</td>
          <td class="text-Green">$${(parseFloat(m.roi_earned) || 0).toFixed(2)}</td>
          <td>${m.maturity_date}</td>
          <td class="fw-bold">$${payout}</td>
        `;
        maturedTableBody.appendChild(tr);
      });
    }
  }

  // === SELECT FROM CARD ===
  window.selectPlanFromCard = function (planId) {
    if (!planSelect) return;
    planSelect.value = planId;
    updatePlanDetails();
    planSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
    showToast('Plan selected. Enter your amount to continue.', 'info');
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

  // Expose refresh functions globally
  window.slm_loadSummary = loadSummary;
  window.slm_loadActive = loadActive;
  window.slm_loadMatured = loadMatured;
  window.slm_loadPlans = loadPlans;
});
