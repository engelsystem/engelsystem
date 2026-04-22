import { ready } from './ready';

/**
 * Spoken languages editor for user settings
 */
ready(() => {
  const editor = document.getElementById('language-editor');
  if (!editor) return;

  const options = JSON.parse(editor.dataset.options || '[]');
  const nativeLabel = editor.dataset.nativeLabel || 'Native';
  const deleteLabel = editor.dataset.deleteLabel || 'Delete';

  const input = document.getElementById('language-input');
  const dropdown = document.getElementById('language-dropdown');
  const selectedContainer = document.getElementById('selected-languages');
  const hiddenInput = document.getElementById('languages-hidden');

  if (!input || !dropdown || !selectedContainer || !hiddenInput) return;

  const getSelectedCodes = () => {
    return hiddenInput.value ? hiddenInput.value.split(',').filter((c) => c) : [];
  };

  const updateHiddenInput = () => {
    const codes = Array.from(selectedContainer.querySelectorAll('[data-code]')).map((el) => el.dataset.code);
    hiddenInput.value = codes.join(',');
  };

  const escapeHtml = (text) => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  };

  const addLanguage = (code, name) => {
    const selected = getSelectedCodes();
    if (selected.includes(code)) return;

    const isLight = document.body.dataset.theme_type === 'light';
    const card = document.createElement('div');
    card.className = `card mb-2 ${isLight ? 'bg-white' : 'bg-dark'}`;
    card.dataset.code = code;
    card.innerHTML = `
      <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
        <div>
          <span class="badge bg-secondary me-2">${escapeHtml(code)}</span>
          <span>${escapeHtml(name)}</span>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="form-check">
            <input type="checkbox"
                   class="form-check-input"
                   name="native[]"
                   value="${escapeHtml(code)}"
                   id="native-${escapeHtml(code)}">
            <label class="form-check-label" for="native-${escapeHtml(code)}">
              ${escapeHtml(nativeLabel)}
            </label>
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger remove-language" aria-label="${escapeHtml(deleteLabel)}">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
    `;
    selectedContainer.appendChild(card);
    updateHiddenInput();
    input.value = '';
    hideDropdown();
  };

  const removeLanguage = (card) => {
    card.remove();
    updateHiddenInput();
  };

  const showDropdown = (matches) => {
    dropdown.innerHTML = '';
    matches.forEach((opt) => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'dropdown-item';
      item.innerHTML = `<span class="badge bg-secondary me-2">${escapeHtml(opt.code)}</span>${escapeHtml(opt.name)}`;
      item.addEventListener('click', () => addLanguage(opt.code, opt.name));
      dropdown.appendChild(item);
    });
    dropdown.style.display = 'block';
  };

  const hideDropdown = () => {
    dropdown.style.display = 'none';
  };

  input.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    if (query.length < 1) {
      hideDropdown();
      return;
    }

    const selected = getSelectedCodes().map((c) => c.toLowerCase());
    const matches = options
      .filter((opt) => {
        const codeMatch = opt.code.toLowerCase().startsWith(query);
        const nameMatch = opt.name.toLowerCase().includes(query);
        const notSelected = !selected.includes(opt.code.toLowerCase());
        return (codeMatch || nameMatch) && notSelected;
      })
      .slice(0, 10);

    if (matches.length > 0) {
      showDropdown(matches);
    } else {
      hideDropdown();
    }
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const firstItem = dropdown.querySelector('.dropdown-item');
      if (firstItem) firstItem.click();
    } else if (e.key === 'Escape') {
      hideDropdown();
    }
  });

  document.addEventListener('click', function (e) {
    if (!editor.contains(e.target)) {
      hideDropdown();
    }
  });

  selectedContainer.addEventListener('click', function (e) {
    const removeBtn = e.target.closest('.remove-language');
    if (removeBtn) {
      const card = removeBtn.closest('[data-code]');
      if (card) removeLanguage(card);
    }
  });
});
