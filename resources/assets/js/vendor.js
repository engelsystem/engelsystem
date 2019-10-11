require('core-js/stable');
window.$ = window.jQuery = require('jquery');
require('imports-loader?define=>false!jquery-ui');
require('bootstrap');
require('imports-loader?this=>window&define=>false&exports=>false!moment');
require('imports-loader?this=>window&define=>false&exports=>false!moment/locale/de');
require('imports-loader?define=>false&exports=>false!eonasdan-bootstrap-datetimepicker');
require('imports-loader?this=>window!chart.js');
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
