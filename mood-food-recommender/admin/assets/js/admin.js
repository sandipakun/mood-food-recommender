(() => {
  const sidebar = document.getElementById('adminSidebar');
  const toggle = document.getElementById('sidebarToggle');
  if (toggle && sidebar) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

  // Delete confirmation modal handler (expects data-delete-form attribute)
  const deleteModalEl = document.getElementById('deleteConfirmModal');
  if (deleteModalEl) {
    const bsModal = new bootstrap.Modal(deleteModalEl);
    document.querySelectorAll('[data-delete-target]').forEach(btn => {
      btn.addEventListener('click', () => {
        const formId = btn.getAttribute('data-delete-target');
        const title = btn.getAttribute('data-delete-title') || 'this item';
        const label = deleteModalEl.querySelector('[data-delete-label]');
        if (label) label.textContent = title;
        const confirmBtn = deleteModalEl.querySelector('[data-delete-confirm]');
        confirmBtn.onclick = () => document.getElementById(formId)?.submit();
        bsModal.show();
      });
    });
  }

  // Dynamic ingredient rows
  document.querySelectorAll('[data-ingredients]').forEach(container => {
    const addBtn = container.querySelector('[data-add-ingredient]');
    const list = container.querySelector('[data-ingredient-list]');
    if (!addBtn || !list) return;
    addBtn.addEventListener('click', () => {
      const idx = list.children.length;
      const row = document.createElement('div');
      row.className = 'row g-2 align-items-end mb-2';
      row.innerHTML = `
        <div class="col-4">
          <label class="form-label mb-1">Quantity</label>
          <input class="form-control" name="ingredients[${idx}][qty]" required>
        </div>
        <div class="col-3">
          <label class="form-label mb-1">Unit</label>
          <input class="form-control" name="ingredients[${idx}][unit]">
        </div>
        <div class="col-4">
          <label class="form-label mb-1">Item</label>
          <input class="form-control" name="ingredients[${idx}][item]" required>
        </div>
        <div class="col-1">
          <button type="button" class="btn btn-outline-pink w-100" data-remove-row title="Remove">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      `;
      list.appendChild(row);
      row.querySelector('[data-remove-row]').addEventListener('click', () => row.remove());
    });
    list.querySelectorAll('[data-remove-row]').forEach(btn => {
      btn.addEventListener('click', () => btn.closest('.row')?.remove());
    });
  });

  // Dynamic steps rows
  document.querySelectorAll('[data-steps]').forEach(container => {
    const addBtn = container.querySelector('[data-add-step]');
    const list = container.querySelector('[data-step-list]');
    if (!addBtn || !list) return;
    const renumber = () => {
      [...list.querySelectorAll('[data-step-row]')].forEach((row, i) => {
        const number = row.querySelector('[data-step-number]');
        if (number) number.textContent = String(i + 1);
        const input = row.querySelector('textarea[name^="steps["]');
        if (input) input.name = `steps[${i}][instruction]`;
      });
    };
    addBtn.addEventListener('click', () => {
      const idx = list.querySelectorAll('[data-step-row]').length;
      const row = document.createElement('div');
      row.className = 'd-flex gap-2 mb-2';
      row.setAttribute('data-step-row', '1');
      row.innerHTML = `
        <div class="chip" style="min-width:52px; justify-content:center;">
          <span data-step-number>${idx + 1}</span>
        </div>
        <textarea class="form-control" rows="2" name="steps[${idx}][instruction]" required placeholder="Step instruction..."></textarea>
        <button type="button" class="btn btn-outline-pink" data-remove-step title="Remove">
          <i class="bi bi-x-lg"></i>
        </button>
      `;
      list.appendChild(row);
      row.querySelector('[data-remove-step]').addEventListener('click', () => { row.remove(); renumber(); });
    });
    list.querySelectorAll('[data-remove-step]').forEach(btn => {
      btn.addEventListener('click', () => { btn.closest('[data-step-row]')?.remove(); renumber(); });
    });
  });
})();

