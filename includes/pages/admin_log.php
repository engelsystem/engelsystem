<?php
function admin_log_title() {
  return _("Log");
}

function admin_log() {
  $log_entries_source = LogEntries();
  $log_entries = array();
  foreach ($log_entries_source as $log_entry) {
    $log_entry['date'] = date("H:i", $log_entry['timestamp']);
    $log_entries[] = $log_entry;
  }
  
  return page_with_title(admin_log_title(), array(
      msg(),
      table(array(
          'date' => "Time",
          'nick' => "Angel",
          'message' => "Log Entry" 
      ), $log_entries) 
  ));
}
?>
