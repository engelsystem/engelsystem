/**
 * Enables the fixed headers and time lane for the shift-calendar and datatables
 */
$(function () {
  if ($('.shift-calendar').length) {
    const timeLanes = $('.shift-calendar .time');
    const headers = $('.shift-calendar .header');
    const topReference = $('.container-fluid .row');
    timeLanes.css({
      'position': 'relative',
      'z-index': 999
    });
    headers.css({
      'position': 'relative',
      'z-index': 900
    });
    $(window).scroll(
      function () {
        const top = headers.parent().offset().top;
        const left = 15;
        timeLanes.css({
          'left': Math.max(0, $(window).scrollLeft() - left) + 'px'
        });
        headers.css({
          'top': Math.max(0, $(window).scrollTop() - top
                        - 13
                        + topReference.offset().top)
                        + 'px'
        });
      });
  }
});
