(() => {
  'use strict';
  const nodes = Array.from(document.querySelectorAll('[data-fee]'));
  if (!nodes.length) return;
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 8000);
  fetch('/fee-region.php', {cache: 'no-store', credentials: 'omit', signal: controller.signal})
    .then(response => { if (!response.ok) throw new Error('Fee lookup unavailable'); return response.json(); })
    .then(data => {
      if (!['PK', 'INTL'].includes(data.region) || data.currency !== (data.region === 'PK' ? 'PKR' : 'USD')) throw new Error('Invalid region');
      for (const node of nodes) {
        const amount = data.fees?.[node.dataset.fee];
        if (!Number.isFinite(amount) || amount <= 0) throw new Error('Invalid fee');
      }
      for (const node of nodes) node.textContent = `${data.currency} ${new Intl.NumberFormat('en-US').format(data.fees[node.dataset.fee])}`;
      document.querySelectorAll('[data-fee-region]').forEach(node => { node.textContent = data.region === 'PK' ? 'Pakistan fees' : 'International fees'; });
    })
    .catch(() => {
      nodes.forEach(node => { node.textContent = 'Contact admissions for fee'; });
      document.querySelectorAll('[data-fee-region]').forEach(node => { node.textContent = 'Fee information'; });
    })
    .finally(() => clearTimeout(timeout));
})();
