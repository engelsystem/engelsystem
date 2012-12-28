<?php

function guest_stats() {
  global $api_key;

  if(isset($_REQUEST['api_key'])) {
    if($_REQUEST['api_key'] == $api_key) {
      $stats = array();

      list($user_count) = sql_select("SELECT count(*) as `user_count` FROM `User`");
      $stats['user_count'] = $user_count['user_count'];

      list($arrived_user_count) = sql_select("SELECT count(*) as `user_count` FROM `User` WHERE `Gekommen`=1");
      $stats['arrived_user_count'] = $arrived_user_count['user_count'];

      $done_shifts_source = sql_select("SELECT `Shifts`.`start`, `Shifts`.`end` FROM `ShiftEntry` JOIN `Shifts` ON `Shifts`.`SID`=`ShiftEntry`.`SID` WHERE `Shifts`.`end` < " . time());
      $done_shifts_seconds = 0;
      foreach($done_shifts_source as $done_shift)
        $done_shifts_seconds += $done_shift['end'] - $done_shift['start'];
      $stats['done_work_hours'] = round($done_shifts_seconds / (60*60), 0);

      header("Content-Type: application/json");
      die(json_encode($stats));
    } else die(json_encode(array('error' => "Wrong api_key.")));
  } else die(json_encode(array('error' => "Missing parameter api_key.")));

}


?>