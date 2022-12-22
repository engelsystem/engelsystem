import 'select2';
import { formatDay, formatTime } from './date';
import { ready } from './ready';

/**
 * @param {HTMLElement} element
 */
const triggerChange = (element) => {
  const changeEvent = new Event('change');
  element.dispatchEvent(changeEvent);
};

/**
 * Sets all checkboxes to the wanted state
 *
 * @param {string} id Id of the element containing all the checkboxes
 * @param {boolean} checked True if the checkboxes should be checked
 */
global.checkAll = (id, checked) => {
  document.querySelectorAll(`#${id} input[type="checkbox"]`).forEach((element) => {
    element.checked = checked;
  });
};

/**
 * Sets the checkboxes according to the given type
 *
 * @param {string} id The elements ID
 * @param {int[]} shiftsList A list of numbers
 */
global.checkOwnTypes = (id, shiftsList) => {
  document.querySelectorAll(`#${id} input[type="checkbox"]`).forEach((element) => {
    const value = parseInt(element.value, 10);
    element.checked = shiftsList.includes(value);
  });
};

/**
 * Sets the values of the input fields with the IDs to from/to:
 * - date portion of from → start_day
 * - time portion of from → start_time
 * - date portion of to → end_day
 * - time portion of to → end_time
 *
 * @param {Date} from
 * @param {Date} to
 */
global.setInput = (from, to) => {
  const fromDay = document.getElementById('start_day');
  const fromTime = document.getElementById('start_time');
  const toDay = document.getElementById('end_day');
  const toTime = document.getElementById('end_time');

  if (!fromDay || !fromTime || !toDay || !toTime) {
    console.warn('cannot set input date because of missing field');
    return;
  }

  fromDay.value = formatDay(from);
  triggerChange(fromDay);
  fromTime.value = formatTime(from);

  toDay.value = formatDay(to);
  triggerChange(toDay);
  toTime.value = formatTime(to);
};

global.setDay = (days) => {
  days = days || 0;

  const from = new Date();
  from.setHours(0, 0, 0, 0);

  // add days, Date handles the overflow
  from.setDate(from.getDate() + days);

  const to = new Date(from);
  to.setHours(23, 59);

  setInput(from, to);
};

global.setHours = (hours) => {
  hours = hours || 1;

  const from = new Date();
  const to = new Date(from);

  // convert hours to add to milliseconds (60 minutes * 60 seconds * 1000 for milliseconds)
  const msToAdd = hours * 60 * 60 * 1000;
  to.setTime(to.getTime() + msToAdd, 'h');
  if (to < from) {
    setInput(to, from);
    return;
  }

  setInput(from, to);
};

ready(() => {
  /**
   * Disable every submit button after clicking (to prevent double-clicking)
   */
  document.querySelectorAll('form').forEach((formElement) => {
    formElement.addEventListener('submit', () => {
      document.querySelectorAll('input[type="submit"],button[type="submit"]').forEach((element) => {
        element.readOnly = true;
        element.classList.add('disabled');
      });
    });
  });
});

ready(() => {
  document.querySelectorAll('.spinner-down').forEach((element) => {
    const inputElement = document.getElementById(element.dataset.inputId);
    if (inputElement) {
      element.addEventListener('click', () => {
        inputElement.stepDown();
      });
    }
  });
  document.querySelectorAll('.spinner-up').forEach((element) => {
    const inputElement = document.getElementById(element.dataset.inputId);
    if (inputElement) {
      element.addEventListener('click', () => {
        inputElement.stepUp();
      });
    }
  });
});

/**
 * Button to set current time in time input fields.
 */
ready(() => {
  document.querySelectorAll('.input-group.time').forEach((element) => {
    const button = element.querySelector('button');
    if (!button) return;

    button.addEventListener('click', () => {
      const now = new Date();
      const input = element.querySelector('input');
      if (!input) return;

      input.value = formatTime(now);
      const daySelector = document.getElementById(input.id.replace('time', 'day'));
      if (!daySelector) return;

      const dayElements = daySelector.querySelectorAll('option');
      const yyyyMMDD = formatDay(now);
      dayElements.forEach((dayElement) => {
        if (dayElement.value === yyyyMMDD) {
          daySelector.value = dayElement.value;
          return false;
        }
      });
    });
  });
});

ready(() => {
  $('select').select2({
    theme: 'bootstrap-5',
    width: '100%',
  });
});

/**
 * Show oauth buttons on welcome title click
 */
ready(() => {
  [
    ['welcome-title', '.btn-group .btn.d-none'],
    ['settings-title', '.user-settings .nav-item'],
    ['oauth-settings-title', 'table tr.d-none'],
  ].forEach(([id, selector]) => {
    document.getElementById(id)?.addEventListener('click', () => {
      document.querySelectorAll(selector).forEach((element) => {
        element.classList.remove('d-none');
      });
    });
  });
});

/**
 * Set the filter selects to latest state
 *
 * Uses DOMContentLoaded to prevent flickering
 */
ready(() => {
  const filter = document.getElementById('collapseShiftsFilterSelect');
  if (!filter || localStorage.getItem('collapseShiftsFilterSelect') !== 'hidden.bs.collapse') {
    return;
  }

  filter.classList.remove('show');
});

ready(() => {
  if (typeof localStorage === 'undefined') {
    return;
  }

  /**
   * @param {Event} event
   */
  const onChange = (event) => {
    localStorage.setItem('collapseShiftsFilterSelect', event.type);
  };

  document.getElementById('collapseShiftsFilterSelect')?.addEventListener('hidden.bs.collapse', onChange);

  document.getElementById('collapseShiftsFilterSelect')?.addEventListener('shown.bs.collapse', onChange);
});
