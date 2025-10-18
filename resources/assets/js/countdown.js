import { ready } from './ready';

/**
 * Initialises all countdown fields on the page.
 */
ready(() => {
  const lang = document.documentElement.getAttribute('lang');

  const rtf = new Intl.RelativeTimeFormat(lang ?? [], { numeric: 'auto' });

  const timeFrames = [
    [60 * 60 * 24 * 365, 'year'],
    [60 * 60 * 24 * 30, 'month'],
    [60 * 60 * 24 * 7, 'week'],
    [60 * 60 * 24, 'day'],
    [60 * 60, 'hour'],
    [60, 'minute'],
    [1, 'second'],
  ];

  /**
   * @param {number} timestamp
   * @returns {string}
   */
  function formatFromNow(timestamp) {
    const now = Date.now() / 1000;
    const diff = Math.round(timestamp - now);
    const absValue = Math.abs(diff);

    for (const [duration, unit] of timeFrames) {
      if (absValue >= duration) {
        return rtf.format(Math.round(diff / duration), unit);
      }
    }

    return rtf.format(0, 'second');
  }

  document.querySelectorAll('[data-countdown-ts]').forEach((element) => {
    const timestamp = Number(element.dataset.countdownTs);
    const template = element.textContent;
    const templateExpired = String(element.dataset.countdownExpiredTemplate);
    element.textContent = template.replace('%c', formatFromNow(timestamp));
    setInterval(() => {
      if (templateExpired !== 'undefined' && Date.now() / 1000 >= timestamp) {
        element.textContent = templateExpired.replace('%c', formatFromNow(timestamp));
      } else {
        element.textContent = template.replace('%c', formatFromNow(timestamp));
      }
    }, 1000);
  });
});
