<?php

/**
 * Shows basic event infos and countdowns.
 * @param EventConfig $event_config The event configuration
 */
function EventConfig_countdown_page($event_config) {
  if ($event_config == null) {
    return div('col-md-12 text-center', [
        heading(sprintf(_("Welcome to the %s!"), '<span class="icon-icon_angel"></span> ENGELSYSTEM'), 2) 
    ]);
  }
  
  $elements = [];
  
  if ($event_config['event_name'] != null) {
    $elements[] = div('col-sm-12 text-center', [
        heading(sprintf(_("Welcome to the %s!"), $event_config['event_name'] . ' <span class="icon-icon_angel"></span> ENGELSYSTEM'), 2) 
    ]);
  }
  
  if ($event_config['buildup_start_date'] != null && time() < $event_config['buildup_start_date']) {
    $elements[] = div('col-sm-3 text-center hidden-xs', [
        heading(_("Buildup starts"), 4),
        '<span class="moment-countdown text-big" data-timestamp="' . $event_config['buildup_start_date'] . '">%c</span>',
        '<small>' . date(_("Y-m-d"), $event_config['buildup_start_date']) . '</small>' 
    ]);
  }
  
  if ($event_config['event_start_date'] != null && time() < $event_config['event_start_date']) {
    $elements[] = div('col-sm-3 text-center hidden-xs', [
        heading(_("Event starts"), 4),
        '<span class="moment-countdown text-big" data-timestamp="' . $event_config['event_start_date'] . '">%c</span>',
        '<small>' . date(_("Y-m-d"), $event_config['event_start_date']) . '</small>' 
    ]);
  }
  
  if ($event_config['event_end_date'] != null && time() < $event_config['event_end_date']) {
    $elements[] = div('col-sm-3 text-center hidden-xs', [
        heading(_("Event ends"), 4),
        '<span class="moment-countdown text-big" data-timestamp="' . $event_config['event_end_date'] . '">%c</span>',
        '<small>' . date(_("Y-m-d"), $event_config['event_end_date']) . '</small>' 
    ]);
  }
  
  if ($event_config['teardown_end_date'] != null && time() < $event_config['teardown_end_date']) {
    $elements[] = div('col-sm-3 text-center hidden-xs', [
        heading(_("Teardown ends"), 4),
        '<span class="moment-countdown text-big" data-timestamp="' . $event_config['teardown_end_date'] . '">%c</span>',
        '<small>' . date(_("Y-m-d"), $event_config['teardown_end_date']) . '</small>' 
    ]);
  }
  
  return join("", $elements);
}

/**
 * Converts event name and start+end date into a line of text.
 */
function EventConfig_info($event_config) {
  if ($event_config == null) {
    return "";
  }
  
  // Event name, start+end date are set
  if ($event_config['event_name'] != null && $event_config['event_start_date'] != null && $event_config['event_end_date'] != null) {
    return sprintf(_("%s, from %s to %s"), $event_config['event_name'], date(_("Y-m-d"), $event_config['event_start_date']), date(_("Y-m-d"), $event_config['event_end_date']));
  }
  
  // Event name, start date are set
  if ($event_config['event_name'] != null && $event_config['event_start_date'] != null) {
    return sprintf(_("%s, starting %s"), $event_config['event_name'], date(_("Y-m-d"), $event_config['event_start_date']));
  }
  
  // Event start+end date are set
  if ($event_config['event_start_date'] != null && $event_config['event_end_date'] != null) {
    return sprintf(_("Event from %s to %s"), date(_("Y-m-d"), $event_config['event_start_date']), date(_("Y-m-d"), $event_config['event_end_date']));
  }
  
  // Only event name is set
  if ($event_config['event_name'] != null) {
    return sprintf($event_config['event_name']);
  }
  
  return "";
}

/**
 * Render edit page for event config.
 *
 * @param string $event_name
 *          The event name
 * @param string $event_welcome_msg
 *          The welcome message
 * @param date $buildup_start_date          
 * @param date $event_start_date          
 * @param date $event_end_date          
 * @param date $teardown_end_date          
 */
function EventConfig_edit_view($event_name, $event_welcome_msg, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date) {
  return page_with_title(event_config_title(), [
      msg(),
      form([
          div('row', [
              div('col-md-6', [
                  form_text('event_name', _("Event Name"), $event_name),
                  form_info('', _("Event Name is shown on the start page.")),
                  form_textarea('event_welcome_msg', _("Event Welcome Message"), $event_welcome_msg),
                  form_info('', _("Welcome message is shown after successful registration. You can use markdown.")) 
              ]),
              div('col-md-3 col-xs-6', [
                  form_date('buildup_start_date', _("Buildup date"), $buildup_start_date),
                  form_date('event_start_date', _("Event start date"), $event_start_date) 
              ]),
              div('col-md-3 col-xs-6', [
                  form_date('teardown_end_date', _("Teardown end date"), $teardown_end_date),
                  form_date('event_end_date', _("Event end date"), $event_end_date) 
              ]) 
          ]),
          div('row', [
              div('col-md-6', [
                  form_submit('submit', _("Save")) 
              ]) 
          ]) 
      ]) 
  ]);
}

?>