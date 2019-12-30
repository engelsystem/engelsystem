;(function($, window, document, undefined) {
    'use strict';

    $(document).ready(function () {
        $('[data-toggle="offcanvas"]').click(function () {
            var button = $(this);
            button.toggleClass('is-open')
            $(button.attr('data-target')).toggleClass('in');
        });
    });

}(jQuery, window, window.document));

