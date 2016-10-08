<?php
use Engelsystem\ShiftsFilterRenderer;
use Engelsystem\ShiftsFilter;
use Engelsystem\ShiftCalendarRenderer;

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
  
  $shiftsFilter = new ShiftsFilter(true, [
      $room['RID'] 
  ], AngelType_ids());
  $selected_day = date("Y-m-d");
  if (! empty($days)) {
    $selected_day = $days[0];
  }
  if (isset($_REQUEST['shifts_filter_day'])) {
    $selected_day = $_REQUEST['shifts_filter_day'];
  }
  $shiftsFilter->setStartTime(parse_date("Y-m-d H:i", $selected_day . ' 00:00'));
  $shiftsFilter->setEndTime(parse_date("Y-m-d H:i", $selected_day . ' 23:59'));
  
  $shiftsFilterRenderer = new ShiftsFilterRenderer($shiftsFilter);
  $shiftsFilterRenderer->enableDaySelection($days);
  
  $shifts = Shifts_by_ShiftsFilter($shiftsFilter, $user);
  
  return [
      $room['Name'],
      Room_view($room, $shiftsFilterRenderer, new ShiftCalendarRenderer($shifts, $shiftsFilter)) 
  ];
}

/**
 * Dispatch different room actions.
 */
function rooms_controller() {
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