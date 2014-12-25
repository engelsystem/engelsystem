<?php
function admin_log_title() {
  return _("Log");
}

function admin_log() {

  if (isset($_POST['keyword'])) {
    $filter = $_POST['keyword'];
    $log_entries_source = LogEntries_filter($_POST['keyword']);
  } else {
    $filter = "";
    $log_entries_source = LogEntries();
  }

  $log_entries = array();
  foreach ($log_entries_source as $log_entry) {
    $log_entry['date'] = date("d.m.Y H:i", $log_entry['timestamp']);
    $log_entries[] = $log_entry;
  }

  return page_with_title(admin_log_title(), array(
      msg(),
      form(array(
        form_text('keyword', _("Search"), $filter),
        form_submit(_("Search"), "Go")
      )),
      table(array(
          'date' => "Time",
          'nick' => "Angel",
          'message' => "Log Entry"
      ), $log_entries)
  ));
}
?>
