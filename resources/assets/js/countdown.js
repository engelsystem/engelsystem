import { ready } from './ready';

const lang = document.documentElement.getAttribute('lang');

const templateFuture = 'in %value %unit';
const templatePast = lang === 'en'
  ? '%value %unit ago'
  : 'vor %value %unit';

const yearUnits = lang === 'en'
  ? ['year', 'years']
  : ['Jahr', 'Jahren'];

const monthUnits = lang === 'en'
  ? ['month', 'months']
  : ['Monat', 'Monaten'];

const dayUnits = lang === 'en'
  ? ['day', 'days']
  : ['Tag', 'Tagen'];

const hourUnits = lang === 'en'
  ? ['hour', 'hours']
  : ['Stunde', 'Stunden'];

const minuteUnits = lang === 'en'
  ? ['minute', 'minutes']
  : ['Minute', 'Minuten'];

const secondUnits = lang === 'en'
  ? ['second', 'seconds']
  : ['Sekunde', 'Sekunden'];

const nowString = lang === 'en' ? 'now' : 'jetzt';

const secondsHour = 60 * 60;

const timeFrames = [
  [365 * 24 * 60 * 60, yearUnits],
  [30 * 24 * 60 * 60, monthUnits],
  [24 * 60 * 60, dayUnits],
  [secondsHour, hourUnits],
  [60, minuteUnits],
  [1, secondUnits],
];

function formatFromNow(timestamp) {
  const now = Date.now() / 1000;
  const diff = Math.abs(timestamp - now);
  const ago = now > timestamp;

  for (const [duration, [singular, plural]] of timeFrames) {
    const value = diff < secondsHour
      ? Math.floor(diff / duration)
      : Math.round(diff / duration);

    if (value) {
      const template = ago ? templatePast : templateFuture;
      const unit = value === 1 ? singular : plural;
      return template
        .replace('%value', value)
        .replace('%unit', unit);
    }
  }

  return nowString;
}

/**
 * Initialises all countdown fields on the page.
 */
ready(function () {
  $.each($('[data-countdown-ts]'), function (i, e) {
    const span = $(e);
    const timestamp = span.data('countdown-ts');
    const text = span.html();
    span.html(text.replace('%c', formatFromNow(timestamp)));
    setInterval(function () {
      span.html(text.replace('%c', formatFromNow(timestamp)));
    }, 1000);
  });
});
