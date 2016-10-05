<?php
use Engelsystem\ShiftsFilterRenderer;
use Engelsystem\ShiftsFilter;

/**
 * Room controllers for managing everything room related.
 */

/**
 * View a room with its shifts.
 */
function room_controller() {
  global $privileges, $user;
  
  if (! in_array('view_rooms', $privileges)) {
    redirect(page_link_to());
  }
  
  $room = load_room();
  $all_shifts = Shifts_by_room($room);
  $days = [];
  foreach ($all_shifts as $shift) {
    $day = date("Y-m-d", $shift['start']);
    if (! in_array($day, $days)) {
      $days[] = $day;
    }
  }
  
  $shiftsFilter = new ShiftsFilter(false, [
      $room['RID'] 
  ], []);
  $shiftsFilter->setStartTime(time());
  $shiftsFilter->setEndTime(time() + 24 * 60 * 60);
  
  $shiftsFilterRenderer = new ShiftsFilterRenderer($shiftsFilter);
  $shiftsFilterRenderer->enableDaySelection($days, EventConfig());
  
  return [
      $room['Name'],
      Room_view($room, $shiftsFilterRenderer) 
  ];
}

/**
 * Dispatch different room actions.
 */
function rooms_controller() {
  global $privileges;
  
  if (! isset($_REQUEST['action'])) {
    $_REQUEST['action'] = 'list';
  }
  
  switch ($_REQUEST['action']) {
    default:
    case 'list':
      redirect(page_link_to('admin_rooms'));
    case 'view':
      return room_controller();
  }
}

function room_link($room) {
  return page_link_to('rooms') . '&action=view&room_id=' . $room['RID'];
}

function room_edit_link($room) {
  return page_link_to('admin_rooms') . '&show=edit&id=' . $room['RID'];
}

/**
 * Loads room by request param room_id
 */
function load_room() {
  if (! test_request_int('room_id')) {
    redirect(page_link_to());
  }
  
  $room = Room($_REQUEST['room_id']);
  if ($room == null) {
    redirect(page_link_to());
  }
  
  return $room;
}

?>