import { ready } from './ready';

/**
 * @param {NodeList} elements
 * @param {string} prop
 * @param {*} value
 */
const applyStyle = (elements, prop, value) => {
  elements.forEach((element) => {
    element.style[prop] = value;
  });
};

/**
 * Enables the fixed headers and time lane for the shift-calendar and datatables
 */
ready(() => {
  if (!document.querySelector('.shift-calendar')) return;

  const headers = document.querySelectorAll('.shift-calendar .header');
  const timeLane = document.querySelector('.shift-calendar .time');
  const topReference = document.querySelector('.container-fluid .row');

  if (!headers.length || !timeLane || !topReference) return;

  timeLane.style.position = 'relative';
  timeLane.style.zIndex = 999;

  applyStyle(headers, 'position', 'relative');
  applyStyle(headers, 'zIndex', 900);

  window.addEventListener('scroll', () => {
    const top = headers.item(0).parentNode.getBoundingClientRect().top;
    const left = Math.max(0, window.scrollX - 15);

    timeLane.style.left = `${left}px`;

    const headersTop = Math.max(0, window.scrollY - top - 13 + topReference.getBoundingClientRect().top);
    applyStyle(headers, 'top', `${headersTop}px`);
  });
});
