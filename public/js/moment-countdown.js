$(document).ready(function() {
    $.each($('.moment-countdown'), function(i, e) {
        var span = $(e);
        var text = span.html();
        var timestamp = moment(parseInt(span.attr("data-timestamp") * 1000));
        span.html(text.replace("%c", timestamp.fromNow()));
        setInterval(function() {
            span.html(text.replace("%c", timestamp.fromNow()));
        }, 1000);
    });
});