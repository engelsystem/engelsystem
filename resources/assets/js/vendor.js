require('core-js/stable');
window.$ = window.jQuery = require('jquery');
window.bootstrap = require('bootstrap');
window.moment = require('moment');
require('moment/locale/de');
require('./forms');
require('./sticky-headers');
require('./moment-countdown');

moment.updateLocale('en', {
    week : {
        dow : 1, // Monday is the first day of the week.
        doy : 4  // The week that contains Jan 4th is the first week of the year.
    }
});

$.ajaxSetup({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
});
