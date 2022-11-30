require('select2');
import { formatDay, formatTime } from './date';

/**
 * Sets all checkboxes to the wanted state
 *
 * @param {string} id Id of the element containing all the checkboxes
 * @param {boolean} checked True if the checkboxes should be checked
 */
global.checkAll = (id, checked) => {
  $('#' + id + ' input[type="checkbox"]').each(function () {
    this.checked = checked;
  });
};

/**
 * Sets the checkboxes according to the given type
 *
 * @param {string} id The elements ID
 * @param {list} shiftsList A list of numbers
 */
global.checkOwnTypes = (id, shiftsList) => {
  $('#' + id + ' input[type="checkbox"]').each(function () {
    this.checked = $.inArray(parseInt(this.value), shiftsList) != -1;
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
  const fromDay = $('#start_day');
  const fromTime = $('#start_time');
  const toDay = $('#end_day');
  const toTime = $('#end_time');

  if (!fromDay || !fromTime || !toDay || !toTime) {
    console.warn('cannot set input date because of missing field');
    return;
  }

  fromDay.val(formatDay(from)).trigger('change');
  fromTime.val(formatTime(from));

  toDay.val(formatDay(to)).trigger('change');
  toTime.val(formatTime(to));
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

$(function () {
  /**
     * Disable every submit button after clicking (to prevent double-clicking)
     */
  $('form').submit(function (ev) {
    $('input[type="submit"]').prop('readonly', true).addClass('disabled');
    return true;
  });

});

/*
 * Button to set current time in time input fields.
 */
$(function () {
  $('.input-group.time').each(function () {
    const elem = $(this);
    elem.find('button').on('click', function () {
      const now = new Date();
      const input = elem.children('input').first();
      input.val(formatTime(now));
      const daySelector = $('#' + input.attr('id').replace('time', 'day'));
      const days = daySelector.children('option');
      const yyyyMMDD = formatDay(now);
      days.each(function (i) {
        if ($(days[i]).val() === yyyyMMDD) {
          daySelector.val($(days[i]).val());
          return false;
        }
      });
    });
  });
});

$(function () {
  $('select').select2({
    theme: 'bootstrap-5',
    width: '100%',
  });
})

/**
 * Show oauth buttons on welcome title click
 */
$(function () {
  $('#welcome-title').on('click', function () {
    $('.btn-group.btn-group .btn.d-none').removeClass('d-none');
  });
  $('#settings-title').on('click', function () {
    $('.user-settings .nav-item').removeClass('d-none');
  });
  $('#oauth-settings-title').on('click', function () {
    $('table tr.d-none').removeClass('d-none');
  });
});

/**
 * Set the filter selects to latest state
 *
 * Uses DOMContentLoaded to prevent flickering
 */
window.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('collapseShiftsFilterSelect');
  if (!filter || localStorage.getItem('collapseShiftsFilterSelect') !== 'hidden') {
    return;
  }

  filter.classList.remove('show');
});

$(() => {
  if (typeof (localStorage) === 'undefined') {
    return;
  }

  const onChange = (e) => {
    localStorage.setItem('collapseShiftsFilterSelect', e.type);
  };

  $('#collapseShiftsFilterSelect')
    .on('hidden.bs.collapse', onChange)
    .on('shown.bs.collapse', onChange);
});
