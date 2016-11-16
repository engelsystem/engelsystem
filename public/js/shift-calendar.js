/**
 * Enables the fixed headers and time lane for the shift-calendar.
 */
$(document).ready(
    function() {
      var timeLanes = $(".shift-calendar .time");
      var headers = $(".shift-calendar .header");
      var topReference = $(".container-fluid .row");
      var top = headers.offset().top;
      var left = 15;
      timeLanes.css({
        "position" : "relative",
        "z-index" : 999
      });
      headers.css({
        "position" : "relative",
        "z-index" : 900
      });
      $(window).scroll(
          function() {
            timeLanes.css({
              "left" : Math.max(0, $(window).scrollLeft() - left) + "px"
            });
            headers.css({
              "top" : Math.max(0, $(window).scrollTop() - top
                  + topReference.offset().top)
                  + "px"
            });
          });
    });