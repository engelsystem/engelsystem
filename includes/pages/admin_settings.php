<?php

function admin_settings_title() {
  return _("Settings");
}

function admin_settings() {
  $event_name = null;
  $event_welcome_msg = null;
  $buildup_start_date = null;
  $event_start_date = null;
  $event_end_date = null;
  $teardown_end_date = null;
  
  $settings_source = Settings();
  if ($settings_source === false)
    engelsystem_error('Unable to load settings.');
  if ($settings_source != null) {
    $event_name = $settings_source['event_name'];
    $buildup_start_date = $settings_source['buildup_start_date'];
    $event_start_date = $settings_source['event_start_date'];
    $event_end_date = $settings_source['event_end_date'];
    $teardown_end_date = $settings_source['teardown_end_date'];
    $event_welcome_msg = $settings_source['event_welcome_msg'];
  }
  
  if (isset($_REQUEST['submit'])) {
    $ok = true;
    
    if (isset($_REQUEST['event_name']))
      $event_name = strip_request_item('event_name');
    if ($event_name == '')
      $event_name = null;
    
    if (isset($_REQUEST['event_welcome_msg']))
      $event_welcome_msg = strip_request_item_nl('event_welcome_msg');
    if ($event_welcome_msg == '')
      $event_welcome_msg = null;
    
    $result = check_request_date('buildup_start_date', _("Please enter buildup start date."), true);
    $buildup_start_date = $result->getValue();
    $ok &= $result->isOk();
    
    $result = check_request_date('event_start_date', _("Please enter event start date."), true);
    $event_start_date = $result->getValue();
    $ok &= $result->isOk();
    
    $result = check_request_date('event_end_date', _("Please enter event end date."), true);
    $event_end_date = $result->getValue();
    $ok &= $result->isOk();
    
    $result = check_request_date('teardown_end_date', _("Please enter teardown end date."), true);
    $teardown_end_date = $result->getValue();
    $ok &= $result->isOk();
    
    if ($ok) {
      $result = Settings_update($event_name, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date, $event_welcome_msg);
      
      if ($result === false)
        engelsystem_error("Unable to update settings.");
      
      success(_("Settings saved."));
      redirect(page_link_to('admin_settings'));
    }
  }
  
  return page_with_title(admin_settings_title(), [
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
