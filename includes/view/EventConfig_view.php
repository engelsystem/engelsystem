<?php

/**
 * Render edit page for event config.
 * @param string $event_name The event name
 * @param string $event_welcome_msg The welcome message
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
              div('col-md-3', [
                  form_date('buildup_start_date', _("Buildup date"), $buildup_start_date),
                  form_date('event_start_date', _("Event start date"), $event_start_date) 
              ]),
              div('col-md-3', [
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