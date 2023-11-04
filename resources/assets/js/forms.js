import Choices from 'choices.js';
import { formatDay, formatTime } from './date';
import { ready } from './ready';

/**
 * Sets all checkboxes to the wanted state
 */
ready(() => {
  document.querySelectorAll('button.checkbox-selection').forEach((buttonElement) => {
    buttonElement.addEventListener('click', () => {
      document.querySelectorAll(`#${buttonElement.dataset.id} input[type="checkbox"]`).forEach((checkboxElement) => {
        /**
         * @type {boolean|int[]}
         */
        const value = JSON.parse(buttonElement.dataset.value);
        if (typeof value === 'boolean') {
          checkboxElement.checked = value;
        } else {
          checkboxElement.checked = value.includes(Number(checkboxElement.value));
        }
      });
    });
  });
});

ready(() => {
  /**
   * @param {HTMLElement} element
   */
  const triggerChange = (element) => {
    const changeEvent = new Event('change');
    element.dispatchEvent(changeEvent);
  };

  /**
   * Sets a select value and triggers a change.
   * If the select has a Choices.js instances, it uses this instead to set the value.
   *
   * @param {HTMLSelectElement} element
   * @param {*} value
   */
  const setSelectValue = (element, value) => {
    if (element.choices) {
      element.choices.setChoiceByValue(value);
    }

    element.value = value;
    triggerChange(element);
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
  const setInput = (from, to) => {
    const fromDay = document.getElementById('start_day');
    const fromTime = document.getElementById('start_time');
    const toDay = document.getElementById('end_day');
    const toTime = document.getElementById('end_time');

    if (!fromDay || !fromTime || !toDay || !toTime) {
      console.warn('cannot set input date because of missing field');
      return;
    }

    setSelectValue(fromDay, formatDay(from));
    fromTime.value = formatTime(from);

    setSelectValue(toDay, formatDay(to));
    toTime.value = formatTime(to);
  };

  /**
   * @param {MouseEvent} event
   */
  const onClickDate = (event) => {
    const days = Number(event.currentTarget.dataset.days);

    const from = new Date();
    from.setHours(0, 0, 0, 0);

    // add days, Date handles the overflow
    from.setDate(from.getDate() + days);

    const to = new Date(from);
    to.setHours(23, 59);

    setInput(from, to);
  };

  /**
   * @param {MouseEvent} event
   */
  const onClickTime = (event) => {
    const hours = Number(event.currentTarget.dataset.hours);

    const from = new Date();
    const to = new Date(from);

    // add hours, Date handles the overflow
    to.setHours(to.getHours() + hours);

    if (to < from) {
      setInput(to, from);
    } else {
      setInput(from, to);
    }
  };

  document.querySelectorAll('.set-date').forEach((element) => {
    element.addEventListener('click', onClickDate);
  });
  document.querySelectorAll('.set-time').forEach((element) => {
    element.addEventListener('click', onClickTime);
  });
});

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

/**
 * {@link https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes/disabled#overview}
 */
const DISABLE_ELEMENTS = [
  'button',
  'command',
  'fieldset',
  'input',
  'keygen',
  'optgroup',
  'option',
  'select',
  'textarea',
];
ready(() => {
  // get all input-radio's and add for each an onChange event listener
  document.querySelectorAll('input[type="radio"]').forEach((radioElement) => {
    // build selector and get all corresponding elements for this input-radio
    const selector = DISABLE_ELEMENTS.map(
      (tagName) => `${tagName}[data-radio-name="${radioElement.name}"][data-radio-value]`
    ).join(',');
    const elements = Array.from(document.querySelectorAll(selector));

    // set all states one time on init for each of the corresponding elements
    elements.forEach((element) => {
      // each radio button updates only his elements
      if (element.dataset.radioValue === radioElement.value) {
        element.disabled = !radioElement.checked;
      }
    });

    // add an onChange event listener that update the disabled state for all corresponding elements
    radioElement.addEventListener('change', () => {
      elements.forEach((element) => {
        element.disabled = element.dataset.radioValue !== radioElement.value;
      });
    });
  });
});

ready(() => {
  const addClickHandler = (selector, onClick) => {
    document.querySelectorAll(selector).forEach((element) => {
      const inputElement = document.getElementById(element.dataset.inputId);

      if (!inputElement || !inputElement.stepUp || !inputElement.stepDown) return;

      if (inputElement.disabled || inputElement.readOnly) {
        // The input element is disabled or read-only → disable the +/- button as well.
        // Note that changing the "disabled" or "readonly" attributes during runtime is not yet supported.
        element.setAttribute('disabled', 'disabled');
        return;
      }

      element.addEventListener('click', () => onClick(inputElement));
    });
  };

  addClickHandler('.spinner-up', (inputElement) => {
    inputElement.stepUp();
  });

  addClickHandler('.spinner-down', (inputElement) => {
    inputElement.stepDown();
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

/**
 * Init select dropdown choices
 */
ready(() => {
  document.querySelectorAll('select').forEach((element) => {
    element.choices = new Choices(element, {
      allowHTML: true,
      classNames: {
        containerInner: 'choices__inner form-control',
      },
      fuseOptions: {
        distance: 0,
        ignoreLocation: true,
        includeScore: true,
        threshold: 0,
      },
      itemSelectText: '',
      // do not use Number.MAX_SAFE_INTEGER here, because otherwise the script gets stuck
      searchResultLimit: 9999,
    });
  });
});

/**
 * Init Bootstrap Popover
 */
ready(() => {
  document.querySelectorAll('[data-bs-toggle="popover"]').forEach((element) => new bootstrap.Popover(element));
});

/**
 * Init Bootstrap Tooltips
 */
ready(() => {
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => new bootstrap.Tooltip(element));
});

/**
 * Init Bootstrap Modals
 */
ready(() => {
  document.querySelectorAll('.modal').forEach((element) => new bootstrap.Modal(element));
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
  const collapseElement = document.getElementById('collapseShiftsFilterSelect');
  if (collapseElement) {
    if (localStorage.getItem('collapseShiftsFilterSelect') === 'hidden.bs.collapse') {
      collapseElement.classList.remove('show');
    }

    /**
     * @param {Event} event
     */
    const onChange = (event) => {
      localStorage.setItem('collapseShiftsFilterSelect', event.type);
    };

    collapseElement.addEventListener('hidden.bs.collapse', onChange);
    collapseElement.addEventListener('shown.bs.collapse', onChange);
  }
});

/**
 * Show/hide checkboxes for User Driver-Licenses
 */
ready(() => {
  const checkboxElement = document.getElementById('wants_to_drive');
  const drivingLicenseElement = document.getElementById('driving_license');

  if (checkboxElement && drivingLicenseElement) {
    drivingLicenseElement.hidden = !checkboxElement.checked;

    checkboxElement.addEventListener('click', () => {
      drivingLicenseElement.hidden = !checkboxElement.checked;
    });
  }
});

/**
 * Prevent scrolling on # links in menu
 */
ready(() => {
  const elements = document.querySelectorAll('.navbar a[href="#"]');
  elements.forEach((a) => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
    });
  });
});
