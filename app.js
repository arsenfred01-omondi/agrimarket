/* FredAgriMarket front-end behaviour (no framework). */
(() => {
  'use strict';

  /* Auto-dismiss flash messages */
  document.querySelectorAll('.flash').forEach((el) => {
    setTimeout(() => { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; }, 4000);
    setTimeout(() => el.remove(), 4500);
  });

  /* Live search suggestions (uses api/produce.php) */
  const input = document.getElementById('global-search');
  const box = document.getElementById('search-suggestions');
  if (input && box) {
    let timer;
    const esc = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    input.addEventListener('input', () => {
      clearTimeout(timer);
      const q = input.value.trim();
      if (q.length < 2) { box.classList.add('hidden'); return; }
      timer = setTimeout(async () => {
        try {
          const res = await fetch('api/produce.php?limit=5&q=' + encodeURIComponent(q));
          const { data } = await res.json();
          box.innerHTML = data.length
            ? data.map((p) => `<a href="product.php?id=${p.id}" class="flex items-center gap-3 px-4 py-2 hover:bg-brand-50 text-sm">
                 <span class="text-xl">${esc(p.emoji)}</span>
                 <span class="flex-1">${esc(p.name)}</span>
                 <span class="text-xs text-gray-400">KES ${Number(p.price).toLocaleString()}</span></a>`).join('')
            : '<p class="px-4 py-3 text-xs text-gray-500">No matches found</p>';
          box.classList.remove('hidden');
        } catch (_) { box.classList.add('hidden'); }
      }, 250);
    });
    document.addEventListener('click', (e) => { if (!box.contains(e.target) && e.target !== input) box.classList.add('hidden'); });
  }

  /* Catalogue: auto-submit filters, live price label */
  const filters = document.getElementById('catalogue-filters');
  if (filters) {
    filters.querySelectorAll('input[type=radio], input[type=checkbox], select').forEach((el) => el.addEventListener('change', () => filters.submit()));
    const range = document.getElementById('price-range');
    const label = document.getElementById('price-range-value');
    if (range && label) {
      range.addEventListener('input', () => { label.textContent = 'KES ' + range.value; });
      range.addEventListener('change', () => filters.submit());
    }
  }

  /* Product page quantity stepper */
  const qty = document.getElementById('qty-input');
  document.querySelectorAll('[data-qty-step]').forEach((btn) => btn.addEventListener('click', () => {
    if (!qty) return;
    const max = parseInt(qty.max || '99', 10);
    qty.value = Math.min(max, Math.max(1, (parseInt(qty.value, 10) || 1) + parseInt(btn.dataset.qtyStep, 10)));
  }));

  /* Register: show farmer-only fields */
  const roleInputs = document.querySelectorAll('input[name=role]');
  const farmerFields = document.getElementById('farmer-fields');
  if (roleInputs.length && farmerFields) {
    const sync = () => {
      const farmer = document.querySelector('input[name=role]:checked')?.value === 'farmer';
      farmerFields.classList.toggle('hidden', !farmer);
      farmerFields.querySelectorAll('input').forEach((i) => { i.required = farmer; });
    };
    roleInputs.forEach((r) => r.addEventListener('change', sync));
    sync();
  }

  /* Confirm destructive actions */
  document.querySelectorAll('[data-confirm]').forEach((el) => el.addEventListener('submit', (e) => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  }));
})();
