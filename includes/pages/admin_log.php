<?php

function admin_log_title() {
  return _("Log");
}

function admin_log() {
  $filter = "";
  if (isset($_REQUEST['keyword'])) {
    $filter = strip_request_item('keyword');
  }
  $log_entries_source = LogEntries_filter($_POST['keyword']);
  
  $log_entries = [];
  foreach ($log_entries_source as $log_entry) {
    $log_entry['date'] = date("d.m.Y H:i", $log_entry['timestamp']);
    $log_entries[] = $log_entry;
  }
  
  return page_with_title(admin_log_title(), [
      msg(),
      form([
          form_text('keyword', _("Search"), $filter),
          form_submit(_("Search"), "Go") 
      ]),
      table([
          'date' => "Time",
          'nick' => "Angel",
          'message' => "Log Entry" 
      ], $log_entries) 
  ]);
}
?>
