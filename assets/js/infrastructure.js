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
      if (res.status === 'success' && res.data?.plans?.length) {
        planSelect.innerHTML = '<option value="">Select a Plan</option>';
        res.data.plans.forEach(plan => {
          const opt = document.createElement('option');
          opt.value = plan.id;
          opt.dataset.min = plan.min;
          opt.dataset.roi = plan.roi_percent;
          opt.dataset.duration = plan.duration_days;
          opt.textContent = plan.name;
          planSelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('Load Plans Error:', err);
    }
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
