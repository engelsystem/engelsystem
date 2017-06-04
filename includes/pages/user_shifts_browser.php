<?php
use Engelsystem\ShiftsFilter;

function shifts_browser_title() {
  return _("Shifts");
}

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

