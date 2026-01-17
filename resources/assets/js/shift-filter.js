import { ready } from './ready';

ready(() => {
  const filterButtons = document.querySelectorAll('[data-filter]');
  const shiftsContainer = document.getElementById('shifts-table-container');

  if (!filterButtons.length || !shiftsContainer) {
    return;
  }

  const table = shiftsContainer.querySelector('table');
  if (!table) {
    return;
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter;

      // Update active button state
      filterButtons.forEach((btn) => btn.classList.remove('active'));
      button.classList.add('active');

      // Filter rows
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
