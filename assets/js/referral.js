/* =======================================================
   referral.js — Referral Center
   Solace Mining Frontend Logic
   ======================================================= */

document.addEventListener('DOMContentLoaded', function () {
  const linkEl = document.getElementById('referral-link');
  const codeEl = document.getElementById('referral-code');
  const copyBtn = document.getElementById('copy-referral-btn');
  const statReferrals = document.getElementById('stat-total-referrals');
  const statEarnings = document.getElementById('stat-total-earnings');
  const statPayouts = document.getElementById('stat-payouts');
  const referralsBody = document.getElementById('referrals-table-body');
  const historyBody = document.getElementById('referral-history-body');

  loadOverview();
  loadReferrals();
  loadHistory();

  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      const val = linkEl?.value || '';
      if (!val) return;
      navigator.clipboard.writeText(val).then(
        () => showToast('Referral link copied!', 'success'),
        () => showToast('Could not copy link.', 'error')
      );
    });
  }

  async function loadOverview() {
    const res = await fetchApi('/api/backend/referral.php', { action: 'get_overview' });
    if (res.status === 'success') {
      const d = res.data || {};
      if (linkEl) linkEl.value = d.referral_link || '';
      if (codeEl) codeEl.textContent = d.referral_code || '—';
      if (statReferrals) statReferrals.textContent = d.total_referrals ?? 0;
      if (statEarnings) statEarnings.textContent = '$' + (d.total_earnings ?? 0).toFixed(2);
      if (statPayouts) statPayouts.textContent = d.commission_payouts ?? 0;
    }
  }

  async function loadReferrals() {
    const res = await fetchApi('/api/backend/referral.php', { action: 'get_referrals' });
    if (res.status === 'success' && referralsBody) {
      const rows = res.data?.referrals || [];
      referralsBody.innerHTML = rows.length
        ? rows.map(r => `
            <tr class="tf-table-item">
              <td>${escapeHtml(r.name)}</td>
              <td>${escapeHtml(r.email)}</td>
              <td>${escapeHtml(r.joined)}</td>
              <td class="text-Green fw-bold">$${(r.earned || 0).toFixed(2)}</td>
            </tr>`).join('')
        : `<tr><td colspan="4" class="text-center text-Gray py-3">No referrals yet. Share your link to start earning.</td></tr>`;
    }
  }

  async function loadHistory() {
    const res = await fetchApi('/api/backend/referral.php', { action: 'get_history' });
    if (res.status === 'success' && historyBody) {
      const rows = res.data?.history || [];
      historyBody.innerHTML = rows.length
        ? rows.map(h => `
            <tr class="tf-table-item">
              <td>${escapeHtml(h.referred_name)}</td>
              <td>${h.commission_percent}%</td>
              <td class="text-Green fw-bold">$${(h.amount || 0).toFixed(2)}</td>
              <td><div class="box-status bg-Green"><span>${escapeHtml(h.status)}</span></div></td>
              <td>${escapeHtml(h.date)}</td>
            </tr>`).join('')
        : `<tr><td colspan="5" class="text-center text-Gray py-3">No commission earned yet.</td></tr>`;
    }
  }

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>"'`=\/]/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
      "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
    })[s]);
  }
});
