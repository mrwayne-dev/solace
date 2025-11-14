/* =======================================================
   HealthRunCare - Maintenance.js (Final Version)
   Purpose: UI + API logic for the Maintenance Development page
   ======================================================= */

document.addEventListener("DOMContentLoaded", () => {
  loadMaintenanceSummary();
  loadActiveMaintenance();
  loadMaturedMaintenance();
  loadMaintenancePlans();

  const form = document.getElementById("development-form");
  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      await startMaintenance();
    });
  }

  const planSelect = document.getElementById("plan-select");
  if (planSelect) planSelect.addEventListener("change", updatePlanDetails);
});

/* =======================================================
   API WRAPPER
   ======================================================= */
async function callMaintenanceAPI(action, data = {}) {
  return await fetchApi("/api/backend/maintenance.php", { ...data, action });
}

/* =======================================================
   LOAD SUMMARY
   ======================================================= */
async function loadMaintenanceSummary() {
  try {
    const res = await callMaintenanceAPI("get_summary");
    if (res.status === "success") {
      const { summary, wallet } = res.data;
      updateSummaryUI(summary, wallet);
    } else {
      showToast(res.message || "Failed to load summary", "error");
    }
  } catch (err) {
    console.error("Maintenance Summary Error:", err);
    showToast("Network error while loading summary", "error");
  }
}

function updateSummaryUI(summary, wallet) {
  const set = (id, v) => {
    const el = document.getElementById(id);
    if (el) el.textContent = v;
  };

  set("dev-active", summary.active_projects ?? 0);
  set("dev-spent", `$${parseFloat(summary.total_spent || 0).toFixed(2)}`);
  set("dev-completed", summary.total_roi ?? 0);
  set("dev-next", summary.next_maintenance || "—");

  const walletEl = document.getElementById("wallet-balance");
  if (walletEl)
    walletEl.textContent = `$${parseFloat(wallet.balance || 0).toFixed(2)}`;
}

/* =======================================================
   LOAD PLANS (for dropdown)
   ======================================================= */
async function loadMaintenancePlans() {
  const planSelect = document.getElementById("plan-select");
  if (!planSelect) return;

  try {
    const res = await callMaintenanceAPI("get_plans");
    if (res.status === "success" && res.data?.plans?.length) {
      planSelect.innerHTML = '<option value="">Select a Plan</option>';
      res.data.plans.forEach((plan) => {
        const opt = document.createElement("option");
        opt.value = plan.id;
        opt.dataset.min = plan.min;
        opt.dataset.roi = plan.roi_percent;
        opt.dataset.duration = plan.duration_days;
        opt.textContent = plan.name;
        planSelect.appendChild(opt);
      });
    }
  } catch (err) {
    console.error("Load Plans Error:", err);
  }
}

/* =======================================================
   UPDATE PLAN DETAILS
   ======================================================= */
function updatePlanDetails() {
  const planSelect = document.getElementById("plan-select");
  if (!planSelect) return;

  const selected = planSelect.options[planSelect.selectedIndex];
  const min = parseFloat(selected.dataset.min || 0);
  const roi = selected.dataset.roi || "";
  const payoff = selected.dataset.duration
    ? `${Math.round(selected.dataset.duration / 30)} months`
    : "";

  const durationEl = document.getElementById("duration");
  const roiEl = document.getElementById("expected-roi");
  const amountInput = document.getElementById("invest-amount");

  if (durationEl) durationEl.value = payoff;
  if (roiEl) roiEl.value = `${roi}%`;

  if (amountInput) {
    amountInput.min = min || 1;
    amountInput.placeholder = `Min: $${Number(min).toLocaleString()}`;
  }

  const btn = document.getElementById("invest-btn");
  if (btn) btn.disabled = !selected.value;
}

/* =======================================================
   START MAINTENANCE PLAN
   ======================================================= */
async function startMaintenance() {
  const planSelect = document.getElementById("plan-select");
  const amountInput = document.getElementById("invest-amount");

  if (!planSelect || !amountInput) return;

  const plan_id = parseInt(planSelect.value || 0);
  const amount = parseFloat(amountInput.value || 0);

  if (!plan_id || amount <= 0) {
    showToast("Please select a valid plan and amount", "error");
    return;
  }

  try {
    toggleLoader(true);
    const res = await callMaintenanceAPI("start_maintenance", { plan_id, amount });
    toggleLoader(false);

    if (res.status === "success") {
      showToast("Maintenance plan started successfully", "success");
      document.getElementById("development-form").reset();
      document.getElementById("invest-btn").disabled = true;
      loadMaintenanceSummary();
      loadActiveMaintenance();
      loadMaturedMaintenance();
    } else {
      showToast(res.message || "Failed to start maintenance plan", "error");
    }
  } catch (err) {
    console.error("Start Maintenance Error:", err);
    toggleLoader(false);
    showToast("Network error. Please try again.", "error");
  }
}

/* =======================================================
   LOAD ACTIVE MAINTENANCE
   ======================================================= */
async function loadActiveMaintenance() {
  const tbody = document.getElementById("active-dev-tbody");
  const emptyState = document.getElementById("empty-active-dev");
  if (!tbody || !emptyState) return;

  try {
    toggleLoader(true);
    const res = await callMaintenanceAPI("get_active");
    toggleLoader(false);

    const items = res.data?.maintenances || [];
    tbody.innerHTML = "";

    // Toggle empty message
    if (!items.length) {
      emptyState.style.display = "block";
      return;
    } else {
      emptyState.style.display = "none";
    }

    // Render rows
    items.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "tf-table-item";
      tr.innerHTML = `
        <td>${escapeHtml(item.plan_name || "—")}</td>
        <td>$${Number(item.amount).toFixed(2)}</td>
        <td class="text-Green">${Number(item.roi_percent || 0).toFixed(2)}%</td>
        <td>${item.duration_days ? `${item.duration_days} days` : "—"}</td>
        <td>${item.maturity_date || "—"}</td>
        <td>
          <div class="box-status ${
            item.status === "active" ? "bg-Green" : "bg-Gray"
          }"><span>${item.status || "Active"}</span></div>
        </td>
        <td>${item.created_at || "—"}</td>
      `;
      tbody.appendChild(tr);
    });
  } catch (err) {
    console.error("Load Active Maintenance Error:", err);
    toggleLoader(false);
  }
}

/* =======================================================
   LOAD MATURED MAINTENANCE
   ======================================================= */
async function loadMaturedMaintenance() {
  const tbody = document.getElementById("matured-dev-tbody");
  const emptyState = document.getElementById("empty-matured-dev");
  if (!tbody || !emptyState) return;

  try {
    toggleLoader(true);
    const res = await callMaintenanceAPI("get_matured");
    toggleLoader(false);

    const items = res.data?.maintenances || [];
    tbody.innerHTML = "";

    // Toggle empty message
    if (!items.length) {
      emptyState.style.display = "block";
      return;
    } else {
      emptyState.style.display = "none";
    }

    // Render rows
    items.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "tf-table-item";
      tr.innerHTML = `
        <td>${escapeHtml(item.plan_name)}</td>
        <td>$${Number(item.amount).toFixed(2)}</td>
        <td class="text-Green">$${Number(item.roi_earned || 0).toFixed(2)}</td>
        <td>${item.maturity_date || "—"}</td>
        <td>$${Number(item.total_payout || 0).toFixed(2)}</td>
        <td>
          <button class="tf-button bg-Green text-White f12-regular"
            onclick="unlockMaintenance(${item.id}, false)">
            Unlock
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  } catch (err) {
    console.error("Load Matured Maintenance Error:", err);
    toggleLoader(false);
  }
}

/* =======================================================
   UNLOCK MAINTENANCE PLAN
   ======================================================= */
async function unlockMaintenance(maintenance_id, early = false) {
  const confirmMsg = early
    ? "This will perform an early unlock and apply a penalty. Proceed?"
    : "Unlock this matured maintenance plan and credit your wallet?";
  if (!confirm(confirmMsg)) return;

  try {
    toggleLoader(true);
    const res = await callMaintenanceAPI("unlock", {
      maintenance_id,
      early: early ? 1 : 0,
    });
    toggleLoader(false);

    if (res.status === "success") {
      showToast("Unlock processed successfully", "success");
      loadMaintenanceSummary();
      loadActiveMaintenance();
      loadMaturedMaintenance();
    } else {
      showToast(res.message || "Failed to process unlock", "error");
    }
  } catch (err) {
    console.error("Unlock Error:", err);
    toggleLoader(false);
    showToast("Network error. Please try again.", "error");
  }
}

/* =======================================================
   UTILITIES
   ======================================================= */
function toggleLoader(show = true) {
  const loader = document.getElementById("loader");
  if (loader) loader.classList.toggle("hidden", !show);
}

function escapeHtml(str) {
  if (!str && str !== 0) return "";
  return String(str).replace(/[&<>"'`=\/]/g, (s) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;",
    "/": "&#x2F;",
    "`": "&#x60;",
    "=": "&#x3D;",
  })[s]);
}

function showToast(message, type = "info") {
  let container = document.getElementById("toast-container");

  // Create if missing (prevents crashes)
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "toast-container fixed top-4 right-4 z-50";
    document.body.appendChild(container);
  }

  // Create toast element
  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  // Append and auto-remove
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3500);
}


/* =======================================================
   AUTO REFRESH (1 min)
   ======================================================= */
setInterval(() => {
  loadMaintenanceSummary();
  loadActiveMaintenance();
  loadMaturedMaintenance();
}, 60000);
