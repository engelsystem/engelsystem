require('core-js/stable');
window.$ = window.jQuery = require('jquery');
window.bootstrap = require('bootstrap');
require('./forms');
require('./sticky-headers');
require('./countdown');

$.ajaxSetup({
  headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
});
