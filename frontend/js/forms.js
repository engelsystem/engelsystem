/**
 * Sets all checkboxes to the wanted state
 *
 * @param {string} id Id of the element containing all the checkboxes
 * @param {bool} checked True if the checkboxes should be checked
 */
global.checkAll = (id, checked) => {
    $("#" + id + " input[type='checkbox']").each(function () {
        this.checked = checked;
    });
}

/**
 * Sets the checkboxes according to the given type
 *
 * @param {string} id The elements ID
 * @param {list} shifts_list A list of numbers
 */
global.checkOwnTypes = (id, shifts_list) => {
    $("#" + id + " input[type='checkbox']").each(function () {
        this.checked = $.inArray(parseInt(this.value), shifts_list) != -1;
    });
}

/**
 * @param {moment} date
 */
global.formatDay = (date) => {
    return date.format("YYYY-MM-DD");
}

/**
 * @param {moment} date
 */
global.formatTime = (date) => {
    return date.format("HH:mm");
}

/**
 * @param {moment} from
 * @param {moment} to
 */
global.setInput = (from, to) => {
    var fromDay = $("#start_day"), fromTime = $("#start_time"), toDay = $("#end_day"), toTime = $("#end_time");

    fromDay.val(formatDay(from));
    fromTime.val(formatTime(from));

    toDay.val(formatDay(to));
    toTime.val(formatTime(to));
}

global.setDay = (days) => {
    days = days || 0;

    var from = moment();
    from.hours(0).minutes(0).seconds(0);

    from.add(days, "d");

    var to = from.clone();
    to.hours(23).minutes(59);

    setInput(from, to);
}

global.setHours = (hours) => {
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

    $(".dropdown-menu").css("max-height", function () {
        return ($(window).height() - 50) + "px";
    }).css("overflow-y", "scroll");
});
