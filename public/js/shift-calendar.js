/**
 * Enables the fixed headers and time lane for the shift-calendar.
 */
$(document).ready(
    function() {
      var time_lanes = $(".shift-calendar .time");
      var headers = $(".shift-calendar .header");
      var top_reference = $(".container-fluid .row");
      var top = headers.offset().top;
      var left = 15;
      time_lanes.css({
        "position" : "relative",
        "z-index" : 1000
      });
      headers.css({
        "position" : "relative",
        "z-index" : 900
      });
      $(window).scroll(
          function() {
            time_lanes.css({
              "left" : Math.max(0, $(window).scrollLeft() - left) + "px"
            });
            headers.css({
              "top" : Math.max(0, $(window).scrollTop() - top
                  + top_reference.offset().top)
                  + "px"
            });
          });
    });