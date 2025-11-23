  /* =======================================================
    HealthRunCare - TrustFund.js (Dynamic Final Version)
    Purpose: Handles TrustFund page UI + API logic
    ======================================================= */

  document.addEventListener('DOMContentLoaded', () => {
    loadTrustFundSummary();
    loadPlans();              // 👈 NEW
    loadActiveTrusts();
    loadMaturedTrusts();

    const form = document.getElementById('trustfundForm');
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await startTrustFund();
      });
    }

    const planSelect = document.getElementById('plan-select');
    if (planSelect) planSelect.addEventListener('change', updatePlanDetails);
  });

  /* =======================================================
    1️⃣ API WRAPPER
    ======================================================= */
  async function callTrustFundAPI(action, data = {}) {
    return await fetchApi('/api/backend/trustfund.php', { ...(data), action });
  }


  /* =======================================================
    2️⃣ LOAD PLANS (Dynamic Rendering)
    ======================================================= */
  async function loadPlans() {
    try {
      const res = await callTrustFundAPI('get_plans');
      if (res.status !== 'success') return showToast('Failed to load plans', 'error');

      const plans = res.data.plans || [];
      renderPlans(plans);
      updatePlanSelect(plans);

    } catch (err) {
      console.error('Load Plans Error:', err);
      showToast('Network error while loading plans', 'error');
    }
  }

  function renderPlans(plans) {
    const container = document.getElementById('trustfund-plans-grid');
    if (!container) return;

    container.innerHTML = '';

    plans.forEach(plan => {
      container.innerHTML += `
        <div class="col-lg-3 col-md-6">
          <div class="plan-card">
            <div class="plan-header flex justify-between items-center mb-12">
              <div class="flex items-center gap-2">
                <h6 class="plan-title">${escapeHtml(plan.name)}</h6>
              </div>
            </div>

            <p class="f12-regular text-Gray mb-12">${escapeHtml(plan.purpose)}</p>

            <table class="plan-features">
              <tr><td>Min Investment</td><td>$${Number(plan.min_amount).toLocaleString()}</td></tr>
              <tr><td>Term</td><td>${plan.duration_days} days</td></tr>
              <tr><td>ROI</td><td class="text-Green fw-bold">${plan.roi_percent}%</td></tr>
              <tr><td>Risk Level</td><td class="text-${plan.color} fw-bold">${escapeHtml(plan.risk)}</td></tr>
              <tr><td>Payout Option</td><td>${escapeHtml(plan.payout_option)}</td></tr>
              <tr><td>Income Source</td><td>${escapeHtml(plan.income_source)}</td></tr>

            </table>

            <p class="f12-regular text-Gray italic mt-12">${escapeHtml(plan.summary)}</p>
          </div>
        </div>`;
    });
  }

  function updatePlanSelect(plans) {
    const select = document.getElementById('plan-select');
    if (!select) return;

    select.innerHTML = `<option value="">Select a Plan</option>`;
    plans.forEach(p => {
      select.innerHTML += `
        <option
          value="${p.id}"
          data-min="${p.min_amount}"
          data-term="${p.duration_days}"
          data-roi="${p.roi_percent}"
        >${escapeHtml(p.name)}</option>`;
    });
  }

  /* =======================================================
    3️⃣ SUMMARY
    ======================================================= */
  async function loadTrustFundSummary() {
    try {
      const res = await callTrustFundAPI('get_summary');
      if (res.status === 'success') {
        const { summary, wallet } = res.data;
        updateSummaryUI(summary, wallet);
      } else {
        showToast(res.message || 'Failed to load summary', 'error');
      }
    } catch (err) {
      console.error('TrustFund Summary Error:', err);
      showToast('Network error while loading summary', 'error');
    }
  }

  function updateSummaryUI(summary, wallet) {
    const el = (id, val) => {
      const node = document.getElementById(id);
      if (node) node.textContent = val;
    };

    el('trust_active', summary.active_trusts || 0);
    el('trust_total_invested', `$${parseFloat(summary.total_invested || 0).toFixed(2)}`);
    el('trust_total_roi', `$${parseFloat(summary.total_roi || 0).toFixed(2)}`);
    el('trust_next_payout', summary.next_payout || '—');
    el('wallet-balance', `$${parseFloat(wallet.balance || 0).toFixed(2)}`);
  }

  /* =======================================================
    4️⃣ PLAN SELECT HANDLER
    ======================================================= */
  function updatePlanDetails() {
    const planSelect = document.getElementById('plan-select');
    if (!planSelect) return;

    const selectedOption = planSelect.options[planSelect.selectedIndex];
    const id = parseInt(selectedOption.value);
    const planName = selectedOption.textContent.trim();

    const rawMin = selectedOption.dataset.min || "0";
    const min = parseFloat(rawMin.replace(/[^0-9.]/g, "")) || 0;
    const term = selectedOption.dataset.term || '';
    const roi = selectedOption.dataset.roi || '';

    const form = document.getElementById('trustfundForm');
    if (!form) return;

    form.querySelector('[name="plan_id"]').value = id || '';
    form.querySelector('[name="plan_name"]').value = planName || '';

    document.getElementById('term-duration').value = `${term} days`;
    document.getElementById('expected-roi').value = `${roi}%`;

    const amountInput = document.getElementById('invest-amount');
    if (amountInput) {
      amountInput.min = min || 1;
      amountInput.placeholder = `Minimum: $${min.toLocaleString()}`;
    }

    document.getElementById('invest-btn').disabled = !id;
  }

  /* =======================================================
    5️⃣ START TRUSTFUND
    ======================================================= */
  async function startTrustFund() {
    const form = document.getElementById('trustfundForm');
    if (!form) return;

    const plan_id = parseInt(form.querySelector('[name="plan_id"]').value || 0);
    const amount = parseFloat(form.querySelector('[name="amount"]').value || 0);

    if (!plan_id || amount <= 0) {
      showToast('Please select a plan and enter a valid amount', 'error');
      return;
    }

    try {
      toggleLoader(true);
      const res = await callTrustFundAPI('start_trustfund', { plan_id, amount });
      toggleLoader(false);

      if (res.status === 'success') {
        showToast('TrustFund plan started successfully', 'success');
        form.reset();
        form.querySelector('[name="plan_id"]').value = '';
        form.querySelector('[name="plan_name"]').value = '';
        document.getElementById('invest-btn').disabled = true;
        loadTrustFundSummary();
        loadActiveTrusts();
        loadMaturedTrusts();
      } else {
        showToast(res.message || 'Failed to start TrustFund plan', 'error');
      }
    } catch (err) {
      console.error('Start TrustFund Error:', err);
      toggleLoader(false);
      showToast('Network Error. Please try again.', 'error');
    }
  }

  /* =======================================================
    6️⃣ ACTIVE TRUSTS
    ======================================================= */
  async function loadActiveTrusts() {
    try {
      toggleLoader(true);
      const res = await callTrustFundAPI('get_active');
      toggleLoader(false);

      const tbody = document.getElementById('active-trusts-tbody');
      if (!tbody) return;

      tbody.innerHTML = '';
      const trusts = res.data?.trusts || [];

      if (!trusts.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-3">No active TrustFund plans.</td></tr>`;
        return;
      }

      trusts.forEach(t => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td>${escapeHtml(t.plan_name)}</td>
          <td>$${Number(t.amount).toFixed(2)}</td>
          <td class="text-Green">${Number(t.roi_percent || 0).toFixed(2)}%</td>
          <td>${escapeHtml(t.duration_days ? t.duration_days + ' days' : '—')}</td>
          <td>${escapeHtml(t.payout_option || 'maturity')}</td>
          <td><div class="box-status ${t.status === 'active' ? 'bg-Green' : 'bg-Gray'}"><span>${t.status}</span></div></td>
          <td>${t.created_at || '—'}</td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error('Load Active Trusts Error:', err);
      toggleLoader(false);
    }
  }

  /* =======================================================
    7️⃣ MATURED TRUSTS
    ======================================================= */
  async function loadMaturedTrusts() {
    try {
      const res = await callTrustFundAPI('get_matured');
      const tbody = document.getElementById('matured-trusts-tbody');
      if (!tbody) return;

      tbody.innerHTML = '';
      const trusts = res.data?.trusts || [];

      if (!trusts.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No matured TrustFunds available for unlock.</td></tr>`;
        return;
      }

      trusts.forEach(t => {
        const tr = document.createElement('tr');
        tr.className = 'tf-table-item';
        tr.innerHTML = `
          <td>${escapeHtml(t.plan_name)}</td>
          <td>$${Number(t.amount).toFixed(2)}</td>
          <td class="text-Green">$${Number(t.roi_earned).toFixed(2)}</td>
          <td>${t.maturity_date || '—'}</td>
          <td>$${Number(t.total_payout).toFixed(2)}</td>
          <td>
            <button class="tf-button bg-Green text-White f12-regular hover:bg-Primary"
              onclick="unlockTrustFund(${t.id}, false)">Unlock</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    } catch (err) {
      console.error('Load Matured Trusts Error:', err);
    }
  }

  /* =======================================================
    8️⃣ UNLOCK
    ======================================================= */
  async function unlockTrustFund(trust_id, early = false) {
    const confirmMsg = early
      ? 'This will perform an early unlock and apply penalty. Proceed?'
      : 'Unlock this matured TrustFund and credit your wallet?';
    if (!confirm(confirmMsg)) return;

    try {
      toggleLoader(true);
      const res = await callTrustFundAPI('unlock', { trust_id, early: early ? 1 : 0 });
      toggleLoader(false);

      if (res.status === 'success') {
        showToast('Unlock request submitted successfully', 'success');
        loadTrustFundSummary();
        loadActiveTrusts();
        loadMaturedTrusts();
      } else {
        showToast(res.message || 'Failed to submit unlock request', 'error');
      }
    } catch (err) {
      toggleLoader(false);
      console.error('Unlock TrustFund Error:', err);
      showToast('Network Error. Please try again.', 'error');
    }
  }

  /* =======================================================
    🔧 UTILITIES
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
    🔁 AUTO REFRESH (Every 60s)
    ======================================================= */
  setInterval(() => {
    loadTrustFundSummary();
    loadActiveTrusts();
    loadMaturedTrusts();
  }, 60000);
