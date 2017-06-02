<?php
use Engelsystem\ShiftsFilter;

function shifts_browser_title() {
  return _("Shifts");
}

/**
 * Start different controllers for deleting shifts and shift_entries, edit shifts and add shift entries.
 * FIXME:
 * Transform into shift controller and shift entry controller.
 * Split actions into shift edit, shift delete, shift entry edit, shift entry delete
 * Introduce simpler and beautiful actions for shift entry join/leave for users
 */
function user_shifts_browser() {
  global $user;
  return view_user_shifts_browser();
}

function view_user_shifts_browser() {
  global $user;
  return page([
      div('col-md-12', [
          template_render(__DIR__ . '/../../templates/user_shifts_browser.html', [
              'title' => shifts_browser_title(),
              'user_id' => $user['UID'],
          ])
      ])
  ]);
}

