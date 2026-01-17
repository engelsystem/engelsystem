import { ready } from './ready';

/**
 * Progressive enhancement for shift filtering on user profile.
 * Server-side filtering works via links; this JS enhances by filtering client-side
 * without a page reload.
 */
ready(() => {
  const filterButtonsContainer = document.getElementById('shift-filter-buttons');
  const shiftsContainer = document.getElementById('shifts-table-container');

  if (!filterButtonsContainer || !shiftsContainer) {
    return;
  }

  const filterLinks = filterButtonsContainer.querySelectorAll('a[data-filter]');
  const table = shiftsContainer.querySelector('table');

  if (!filterLinks.length || !table) {
    return;
  }

  filterLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();

      const filter = link.dataset.filter;
      const newUrl = link.href;

      // Update URL without page reload
      history.replaceState(null, '', newUrl);

      // Update active button state
      filterLinks.forEach((l) => l.classList.remove('active'));
      link.classList.add('active');

      // Filter rows client-side
      const rows = table.querySelectorAll('tbody tr');
      rows.forEach((row) => {
        if (filter === 'all') {
          row.style.display = '';
        } else {
          const stateClass = `shift-state-${filter}`;
          if (row.classList.contains(stateClass)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        }
      });
    });
  });
});
