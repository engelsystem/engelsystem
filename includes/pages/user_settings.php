<?php

function settings_title() {
  return _("Settings");
}

/**
 * Change user main attributes (name, dates, etc.)
 *
 * @param User $user_source
 *          The user
 */
function user_settings_main($user_source, $enable_tshirt_size, $tshirt_sizes) {
  $valid = true;
  
  if (isset($_REQUEST['mail'])) {
    $result = User_validate_mail($_REQUEST['mail']);
    $user_source['email'] = $result->getValue();
    if (! $result->isValid()) {
      $valid = false;
      error(_("E-mail address is not correct."));
    }
  } else {
    $valid = false;
    error(_("Please enter your e-mail."));
  }
  
  $user_source['email_shiftinfo'] = isset($_REQUEST['email_shiftinfo']);
  $user_source['email_by_human_allowed'] = isset($_REQUEST['email_by_human_allowed']);
  
  if (isset($_REQUEST['jabber'])) {
    $result = User_validate_mail($_REQUEST['jabber']);
    $user_source['jabber'] = $result->getValue();
    if (! $result->isValid()) {
      $valid = false;
      error(_("Please check your jabber account information."));
    }
  }
  
  if (isset($_REQUEST['tshirt_size']) && isset($tshirt_sizes[$_REQUEST['tshirt_size']])) {
    $user_source['Size'] = $_REQUEST['tshirt_size'];
  } elseif ($enable_tshirt_size) {
    $valid = false;
  }
  
  if (isset($_REQUEST['planned_arrival_date'])) {
    $tmp = parse_date("Y-m-d", $_REQUEST['planned_arrival_date']);
    $result = User_validate_planned_arrival_date($tmp);
    $user_source['planned_arrival_date'] = $result->getValue();
    if (! $result->isValid()) {
      $valid = false;
      error(_("Please enter your planned date of arrival. It should be after the buildup start date and before teardown end date."));
    }
  }
  
  if (isset($_REQUEST['planned_departure_date'])) {
    $tmp = parse_date("Y-m-d", $_REQUEST['planned_departure_date']);
    $result = User_validate_planned_departure_date($user_source['planned_arrival_date'], $tmp);
    $user_source['planned_departure_date'] = $result->getValue();
    if (! $result->isValid()) {
      $valid = false;
      error(_("Please enter your planned date of departure. It should be after your planned arrival date and after buildup start date and before teardown end date."));
    }
  }
  
  // Trivia
  $user_source['Name'] = strip_request_item('lastname', $user_source['Name']);
  $user_source['Vorname'] = strip_request_item('prename', $user_source['Vorname']);
  $user_source['Alter'] = strip_request_item('age', $user_source['Alter']);
  $user_source['Telefon'] = strip_request_item('tel', $user_source['Telefon']);
  $user_source['DECT'] = strip_request_item('dect', $user_source['DECT']);
  $user_source['Handy'] = strip_request_item('mobile', $user_source['Handy']);
  $user_source['Hometown'] = strip_request_item('hometown', $user_source['Hometown']);
  
  if ($valid) {
    User_update($user_source);
    success(_("Settings saved."));
    redirect(page_link_to('user_settings'));
  }
}

/**
 * Change user password.
 *
 * @param User $user_source
 *          The user
 */
function user_settings_password($user_source) {
  if (! isset($_REQUEST['password']) || ! verify_password($_REQUEST['password'], $user_source['Passwort'], $user_source['UID'])) {
    error(_("-> not OK. Please try again."));
  } elseif (strlen($_REQUEST['new_password']) < MIN_PASSWORD_LENGTH) {
    error(_("Your password is to short (please use at least 6 characters)."));
  } elseif ($_REQUEST['new_password'] != $_REQUEST['new_password2']) {
    error(_("Your passwords don't match."));
  } elseif (set_password($user_source['UID'], $_REQUEST['new_password'])) {
    success(_("Password saved."));
  } else {
    error(_("Failed setting password."));
  }
  redirect(page_link_to('user_settings'));
}

/**
 * Change user theme
 *
 * @param User $user_sources
 *          The user
 * @param array<String> $themes
 *          List of available themes
 */
function user_settings_theme($user_source, $themes) {
  $valid = true;
  
  if (isset($_REQUEST['theme']) && isset($themes[$_REQUEST['theme']])) {
    $user_source['color'] = $_REQUEST['theme'];
  } else {
    $valid = false;
  }
  
  if ($valid) {
    sql_query("UPDATE `User` SET `color`='" . sql_escape($user_source['color']) . "' WHERE `UID`='" . sql_escape($user_source['UID']) . "'");
    
    success(_("Theme changed."));
    redirect(page_link_to('user_settings'));
  }
}

/**
 * Change use locale
 *
 * @param User $user_source
 *          The user
 * @param array<String> $locales
 *          List of available locales
 */
function user_settings_locale($user_source, $locales) {
  $valid = true;
  
  if (isset($_REQUEST['language']) && isset($locales[$_REQUEST['language']])) {
    $user_source['Sprache'] = $_REQUEST['language'];
  } else {
    $valid = false;
  }
  
  if ($valid) {
    sql_query("UPDATE `User` SET `Sprache`='" . sql_escape($user_source['Sprache']) . "' WHERE `UID`='" . sql_escape($user_source['UID']) . "'");
    $_SESSION['locale'] = $user_source['Sprache'];
    
    success("Language changed.");
    redirect(page_link_to('user_settings'));
  }
}

/**
 * Main user settings page/controller
 */
function user_settings() {
  global $enable_tshirt_size, $tshirt_sizes, $themes, $locales;
  global $user;
  
  $buildup_start_date = null;
  $teardown_end_date = null;
  $event_config = EventConfig();
  if ($event_config != null) {
    if (isset($event_config['buildup_start_date'])) {
      $buildup_start_date = $event_config['buildup_start_date'];
    }
    if (isset($event_config['teardown_end_date'])) {
      $teardown_end_date = $event_config['teardown_end_date'];
    }
  }
  
  $user_source = $user;
  
  if (isset($_REQUEST['submit'])) {
    user_settings_main($user_source, $enable_tshirt_size, $tshirt_sizes);
  } elseif (isset($_REQUEST['submit_password'])) {
    user_settings_password($user_source);
  } elseif (isset($_REQUEST['submit_theme'])) {
    user_settings_theme($user_source, $themes);
  } elseif (isset($_REQUEST['submit_language'])) {
    user_settings_locale($user_source, $locales);
  }
  
  return User_settings_view($user_source, $locales, $themes, $buildup_start_date, $teardown_end_date, $enable_tshirt_size, $tshirt_sizes);
}
?>
