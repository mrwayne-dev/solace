/**
 * ============================================================
 *  HealthRunCare — Transaction.js
 *  Handles loading, searching, filtering, pagination & export
 * ============================================================
 */

$(document).ready(function () {
  const listEl = $('#transactionList');
  const searchInput = $('.form-search input');
  const filterMenu = $('.dropdown-menu a');
  const exportBtn = $('.tf-button.style-2');
  const paginationEl = $('#pagination');
  let currentStatus = 'all';
  let currentPage = 1;
  let limit = 10;

  async function loadTransactions(page = 1, status = currentStatus, search = '') {
    listEl.html('<tr><td colspan="5" class="text-center text-muted p-3">Loading transactions...</td></tr>');
    try {
      const res = await fetch('/api/backend/transactions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ page, limit, status, search })
      }).then(r => r.json());

      if (res.status !== 'success') throw new Error(res.message);

      const data = res.data.transactions;
      const pagination = res.data.pagination;

      listEl.empty();

      if (!data.length) {
        listEl.append(`<tr><td colspan="5" class="text-center text-muted p-3">No transactions found</td></tr>`);
        paginationEl.empty();
        return;
      }

      data.forEach(tx => {
        const colorClass =
          tx.status.toLowerCase() === 'completed'
            ? 'bg-Green'
            : tx.status.toLowerCase() === 'pending'
            ? 'bg-Orange'
            : 'bg-Salmon';

        const row = `
          <tr class="tf-table-item">
            <td data-label="Transaction ID"><div class="f12-medium">#${tx.reference}</div></td>
            <td data-label="Date"><div class="f12-medium">${tx.date}</div></td>
            <td data-label="Type"><div class="f12-bold">${tx.type}</div></td>
            <td data-label="Amount"><div class="f12-medium">$${tx.amount}</div></td>
            <td data-label="Status"><div class="box-status ${colorClass}"><span class="font-poppins">${tx.status.toUpperCase()}</span></div></td>
          </tr>`;
        listEl.append(row);
      });

      renderPagination(pagination);
    } catch (err) {
      console.error('Error loading transactions:', err);
      listEl.html('<tr><td colspan="5" class="text-center text-danger p-3">Failed to load transactions.</td></tr>');
      paginationEl.empty();
    }
  }

  // Render pagination buttons
  function renderPagination({ page, pages }) {
    paginationEl.empty();
    pages = Math.max(1, pages);

    if (pages <= 1) return;

    for (let i = 1; i <= pages; i++) {
      const btn = $(`<button class="page-btn ${i === page ? 'active' : ''}">${i}</button>`);
      btn.on('click', () => {
        currentPage = i;
        loadTransactions(currentPage, currentStatus, searchInput.val().trim());
      });
      paginationEl.append(btn);
    }
  }

  // Search form
  $('.form-search').on('submit', function (e) {
    e.preventDefault();
    loadTransactions(1, currentStatus, searchInput.val().trim());
  });

  // Filter dropdown
  filterMenu.on('click', function (e) {
    e.preventDefault();
    currentStatus = $(this).text().toLowerCase() || 'all';
    loadTransactions(1, currentStatus, searchInput.val().trim());
  });

  // Export to CSV
  exportBtn.on('click', function (e) {
    e.preventDefault();
    window.location.href = '/api/backend/transactions.php?export=true';
  });

  // Initial load
  loadTransactions();
});
