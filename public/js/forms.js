/**
 * Runs through the DOM under the element with the given id, finds all
 * checkboxes and sets them to the wanted state.
 *
 * @param String
 *            id Id of the element containing all the checkboxes
 * @param Boolean
 *            checked True if the checkboxes should be checked
 */
function checkAll(id, checked) {
    var obj = document.getElementById(id);
    var boxes = obj.getElementsByTagName("input");
    for (var i = 0; i < boxes.length; i++) {
        if (boxes[i].type === "checkbox" && !boxes[i].disabled) {
            boxes[i].checked = checked;
        }
    }
}

/**
 * @param {moment} date
 */
function formatDay(date) {
    return date.format("YYYY-MM-DD");
}

/**
 * @param {moment} date
 */
function formatTime(date) {
    return date.format("HH:mm");
}

/**
 * @param {moment} from
 * @param {moment} to
 */
function setInput(from, to) {
    var fromDay = $("#start_day"), fromTime = $("#start_time"), toDay = $("#end_day"), toTime = $("#end_time");

    fromDay.val(formatDay(from));
    fromTime.val(formatTime(from));

    toDay.val(formatDay(to));
    toTime.val(formatTime(to));
}

function setDay(days) {
    days = days || 0;

    var from = moment();
    from.hours(0).minutes(0).seconds(0);

    from.add(days, "d");

    var to = from.clone();
    to.hours(23).minutes(59);

    setInput(from, to);
}

function setHours(hours) {
    hours = hours || 1;

    var from = moment();
    var to = from.clone();

    to.add(hours, "h");
    if (to < from) {
        setInput(to, from);
        return;
    }

    setInput(from, to);
}

$(function () {
    /**
     * Disable every submit button after clicking (to prevent double-clicking)
     */
    $("form").submit(function (ev) {
        $("input[type='submit']").prop("readonly", true).addClass("disabled");
        return true;
    });
});
