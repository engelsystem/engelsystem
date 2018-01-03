import 'imports-loader?module=>false!jquery';
import 'imports-loader?define=>false!jquery-ui';
import 'bootstrap';
import 'imports-loader?define=>false&exports=>false!bootstrap-datepicker';
import 'bootstrap-datepicker/js/locales/bootstrap-datepicker.de';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css';
import 'imports-loader?this=>window!chart.js';
import 'imports-loader?this=>window&define=>false&exports=>false!moment';
import 'imports-loader?this=>window&define=>false&exports=>false!moment/locale/de';
import './forms';
import './sticky-headers';
import 'icomoon/style.css';

$(function () {
    moment.locale("%locale%");
});

$(document).ready(function () {
    if (typeof moment !== "undefined") {
        $.each($(".moment-countdown"), function (i, e) {
            var span = $(e);
            var text = span.html();
            /* global moment */
            var timestamp = moment(parseInt(span.attr("data-timestamp") * 1000));
            span.html(text.replace("%c", timestamp.fromNow()));
            setInterval(function () {
                span.html(text.replace("%c", timestamp.fromNow()));
            }, 1000);
        });
    }
});
