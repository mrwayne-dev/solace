  /* =======================================================
    HealthRunCare - Infrastructure.js (Final Version)
    Purpose: UI + API logic for the Infrastructure page
    ======================================================= */

  document.addEventListener('DOMContentLoaded', () => {
    loadInfrastructureSummary();
    loadActiveInvestments();
    loadMaturedInvestments();
    loadInfrastructurePlans();

    const form = document.getElementById('infrastructure-form');
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await startInfrastructure();
      });
    }

    const planSelect = document.getElementById('plan-select');
    if (planSelect) planSelect.addEventListener('change', updatePlanDetails);
  });

  /* =======================================================
    API WRAPPER
    ======================================================= */
  async function callInfrastructureAPI(action, data = {}) {
    return await fetchApi('/api/backend/infrastructure.php', { ...data, action });
  }

  /* =======================================================
    LOAD SUMMARY
    ======================================================= */
  async function loadInfrastructureSummary() {
    try {
      const res = await callInfrastructureAPI('get_summary');
      if (res.status === 'success') {
        const { summary, wallet } = res.data;
        updateSummaryUI(summary, wallet);
      } else {
        showToast(res.message || 'Failed to load summary', 'error');
      }
    } catch (err) {
      console.error('Infrastructure Summary Error:', err);
      showToast('Network error while loading summary', 'error');
    }
  }

  function updateSummaryUI(summary, wallet) {
    const set = (id, v) => {
      const el = document.getElementById(id);
      if (el) el.textContent = v;
    };

    set('infra-active', summary.active_projects ?? 0);
    set('infra-funded', `$${parseFloat(summary.total_funded || 0).toFixed(2)}`);
    set('infra-completed', summary.completed_projects ?? 0);
    set('infra-next', summary.next_inspection || '—');

    const walletEl = document.getElementById('wallet-balance');
    if (walletEl) walletEl.value = `$${parseFloat(wallet.balance || 0).toFixed(2)}`;
  }

  /* =======================================================
    LOAD PLANS (optional; for dynamic UI)
    ======================================================= */
async function loadInfrastructurePlans() {
  const planSelect = document.getElementById('plan-select');
  if (!planSelect) return;

  try {
    const res = await callInfrastructureAPI('get_plans');
    const plans = res.data?.plans || [];

    // Populate dropdown
    planSelect.innerHTML = '<option value="">Select a Plan</option>';
    plans.forEach(plan => {
      const opt = document.createElement('option');
      opt.value = plan.id;
      opt.dataset.min = plan.min_amount;
      opt.dataset.roi = plan.roi_percent;
      opt.dataset.duration = plan.duration_days;
      opt.textContent = plan.name;
      planSelect.appendChild(opt);
    });

    // Render cards dynamically
    renderInfraPlanCards(plans);

  } catch (err) {
    console.error('Load Plans Error:', err);
  }
}


function renderInfraPlanCards(plans) {
  const grid = document.getElementById('infra-plans-grid');
  if (!grid) return;
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
            <tr><td>Min Investment</td><td>$${Number(p.min_amount).toLocaleString()}</td></tr>
            <tr><td>Pay-Off Period</td><td>${Math.round(p.duration_days / 30)} months</td></tr>
            <tr><td>ROI</td><td class="text-Green fw-bold">${p.roi_percent}%</td></tr>
            <tr><td>Risk Level</td><td class="text-${p.color} fw-bold">${escapeHtml(p.risk_level ?? '')}</td></tr>
            <tr><td>Repayment Mode</td><td>${escapeHtml(p.repayment_mode ?? '')}</td></tr>
          </table>

          <p class="f12-regular text-Gray italic mt-12">${escapeHtml(p.summary)}</p>
        </div>
      </div>`;
  });
}


  /* =======================================================
    UPDATE PLAN DETAILS
    ======================================================= */
  function updatePlanDetails() {
    const planSelect = document.getElementById('plan-select');
    if (!planSelect) return;

    const selected = planSelect.options[planSelect.selectedIndex];
    const min = parseFloat(selected.dataset.min || 0);
    const roi = selected.dataset.roi || '';
    const payoff = selected.dataset.duration
      ? `${Math.round(selected.dataset.duration / 30)} months`
      : '';

    const payoffEl = document.getElementById('payoff-period');
    const roiEl = document.getElementById('expected-roi');
    const amountInput = document.getElementById('invest-amount');

    if (payoffEl) payoffEl.value = payoff;
    if (roiEl) roiEl.value = `${roi}%`;

    if (amountInput) {
      amountInput.min = min || 1;
      amountInput.placeholder = `Min: $${Number(min).toLocaleString()}`;
    }

    const btn = document.getElementById('invest-btn');
    if (btn) btn.disabled = !selected.value;
  }

  /* =======================================================
    START INFRASTRUCTURE CONTRIBUTION
    ======================================================= */
  async function startInfrastructure() {
    const planSelect = document.getElementById('plan-select');
    const amountInput = document.getElementById('invest-amount');

    if (!planSelect || !amountInput) return;

    const plan_id = parseInt(planSelect.value || 0);
    const amount = parseFloat(amountInput.value || 0);

    if (!plan_id || amount <= 0) {
      showToast('Please select a valid plan and amount', 'error');
      return;
    }

    try {
      toggleLoader(true);
      const res = await callInfrastructureAPI('start_infrastructure', { plan_id, amount });
      toggleLoader(false);

      if (res.status === 'success') {
        showToast('Infrastructure investment started successfully', 'success');
        document.getElementById('infrastructure-form').reset();
        document.getElementById('invest-btn').disabled = true;
        loadInfrastructureSummary();
        loadActiveInvestments();
        loadMaturedInvestments();
      } else {
        showToast(res.message || 'Failed to start investment', 'error');
      }
    } catch (err) {
      console.error('Start Infrastructure Error:', err);
      toggleLoader(false);
      showToast('Network error. Please try again.', 'error');
    }
  }

  /* =======================================================
    LOAD ACTIVE INVESTMENTS
    ======================================================= */
  async function loadActiveInvestments() {
    const tbody = document.getElementById('active-infra-tbody');
    if (!tbody) return;

    try {
      toggleLoader(true);
      const res = await callInfrastructureAPI('get_active');
      toggleLoader(false);

      const investments = res.data?.investments || [];
      tbody.innerHTML = '';

      if (!investments.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">No active Infrastructure investments.</td></tr>`;
        return;
      }

      investments.forEach(inv => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td>${escapeHtml(inv.plan_name || '—')}</td>
          <td>$${Number(inv.amount).toFixed(2)}</td>
          <td class="text-Green">${Number(inv.roi_percent || 0).toFixed(2)}%</td>
          <td>${inv.duration_days ? `${inv.duration_days} days` : '—'}</td>
          <td>${inv.maturity_date || '—'}</td>
          <td><div class="box-status ${inv.status === 'active' ? 'bg-Green' : 'bg-Gray'}"><span>${inv.status || 'Active'}</span></div></td>
          <td>${inv.created_at || '—'}</td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error('Load Active Investments Error:', err);
      toggleLoader(false);
    }
  }

  /* =======================================================
    LOAD MATURED INVESTMENTS
    ======================================================= */
  async function loadMaturedInvestments() {
    const tbody = document.getElementById('matured-infra-tbody');
    if (!tbody) return;

    try {
      const res = await callInfrastructureAPI('get_matured');
      const investments = res.data?.investments || [];
      tbody.innerHTML = '';

      if (!investments.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No matured plans available for unlock.</td></tr>`;
        return;
      }

      investments.forEach(inv => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td>${escapeHtml(inv.plan_name)}</td>
          <td>$${Number(inv.amount).toFixed(2)}</td>
          <td class="text-Green">$${Number(inv.roi_earned || 0).toFixed(2)}</td>
          <td>${inv.maturity_date || '—'}</td>
          <td>$${Number(inv.total_payout || 0).toFixed(2)}</td>
          <td>
            <button class="tf-button bg-Green text-White f12-regular" 
              onclick="unlockInvestment(${inv.id}, false)">
              Unlock
            </button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error('Load Matured Investments Error:', err);
    }
  }

  /* =======================================================
    UNLOCK INVESTMENT
    ======================================================= */
  async function unlockInvestment(investment_id, early = false) {
    const confirmMsg = early
      ? 'This will perform an early unlock and apply a penalty. Proceed?'
      : 'Unlock this matured investment and credit your wallet?';
    if (!confirm(confirmMsg)) return;

    try {
      toggleLoader(true);
      const res = await callInfrastructureAPI('unlock', { investment_id, early: early ? 1 : 0 });
      toggleLoader(false);

      if (res.status === 'success') {
        showToast('Unlock processed successfully', 'success');
        loadInfrastructureSummary();
        loadActiveInvestments();
        loadMaturedInvestments();
      } else {
        showToast(res.message || 'Failed to process unlock', 'error');
      }
    } catch (err) {
      console.error('Unlock Error:', err);
      toggleLoader(false);
      showToast('Network error. Please try again.', 'error');
    }
  }

  /* =======================================================
    UTILITIES
    ======================================================= */
  function toggleLoader(show = true) {
    const loader = document.getElementById('loader');
    if (loader) loader.classList.toggle('hidden', !show);
  }

  function escapeHtml(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/[&<>"'`=\/]/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
      "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
    })[s]);
  }

  /* =======================================================
    AUTO REFRESH (1 min)
    ======================================================= */
  setInterval(() => {
    loadInfrastructureSummary();
    loadActiveInvestments();
    loadMaturedInvestments();
  }, 60000);
