<?php

function event_config_title() {
  return _("Event config");
}

function event_config_edit_controller() {
  global $privileges;
  
  if (! in_array('admin_event_config', $privileges))
    redirect('?');
  
  $event_name = null;
  $event_welcome_msg = null;
  $buildup_start_date = null;
  $event_start_date = null;
  $event_end_date = null;
  $teardown_end_date = null;
  
  $settings_source = EventConfig();
  if ($settings_source === false)
    engelsystem_error('Unable to load event config.');
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
      $result = EventConfig_update($event_name, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date, $event_welcome_msg);
      
      if ($result === false)
        engelsystem_error("Unable to update event config.");
      
      engelsystem_log("Changed event config: $event_name, $event_welcome_msg, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date");
      success(_("Settings saved."));
      redirect(page_link_to('admin_settings'));
    }
  }
  
  return [
      event_config_title(),
      EventConfig_edit_view($event_name, $event_welcome_msg, $buildup_start_date, $event_start_date, $event_end_date, $teardown_end_date) 
  ];
}

?>