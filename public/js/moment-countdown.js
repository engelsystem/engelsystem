/**
 * Initialize all moment countdowns on the page. A moment countdown has the
 * class "moment-countdown" and the attribute "data-timestamp" which defines the
 * countdown's time goal.
 */
$(document).ready(function() {
  if (typeof moment !== "undefined") {
    $.each($(".moment-countdown"), function(i, e) {
      var span = $(e);
      var text = span.html();
      /* global moment */
      var timestamp = moment(parseInt(span.attr("data-timestamp") * 1000));
      span.html(text.replace("%c", timestamp.fromNow()));
      setInterval(function() {
        span.html(text.replace("%c", timestamp.fromNow()));
      }, 1000);
    });
  }
});