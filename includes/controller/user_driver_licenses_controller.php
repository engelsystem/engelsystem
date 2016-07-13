<?php

/**
 * Generates a hint, if user joined angeltypes that require a driving license and the user has no driver license information provided.
 */
function user_driver_license_required_hint() {
  global $user;

  $angeltypes = User_angeltypes($user);
  if ($angeltypes === false)
    engelsystem_error("Unable to load user angeltypes.");
  $user_driver_license = UserDriverLicense($user['UID']);
  if ($user_driver_license === false)
    engelsystem_error("Unable to load user driver license.");

  $driving_license_information_required = false;
  foreach ($angeltypes as $angeltype)
    if ($angeltype['requires_driver_license']) {
      $driving_license_information_required = true;
      break;
    }

  if ($driving_license_information_required && $user_driver_license == null)
    return info(sprintf(_("You joined an angeltype which requires a driving license. Please edit your driving license information here: %s."), '<a href="' . user_driver_license_edit_link() . '">' . _("driving license information") . '</a>'), true);

  return '';
}

/**
 * Route user driver licenses actions.
 */
function user_driver_licenses_controller() {
  global $privileges, $user;

  if (! isset($user))
    redirect(page_link_to(''));

  if (! isset($_REQUEST['action']))
    $_REQUEST['action'] = 'edit';

  switch ($_REQUEST['action']) {
    default:
    case 'edit':
      return user_driver_license_edit_controller();
  }
}

/**
 * Link to user driver license edit page for given user.
 *
 * @param User $user
 */
function user_driver_license_edit_link($user = null) {
  if ($user == null)
    return page_link_to('user_driver_licenses');
  return page_link_to('user_driver_licenses') . '&user_id=' . $user['UID'];
}

/**
 * Edit a users driver license information.
 */
function user_driver_license_edit_controller() {
  global $privileges, $user;

  if (isset($_REQUEST['user_id'])) {
    $user_source = User($_REQUEST['user_id']);
    if ($user_source === false)
      engelsystem_error('Unable to load angeltype.');
    if ($user_source == null)
      redirect(user_driver_license_edit_link());

      // only privilege admin_user can edit other users driver license information
    if ($user['UID'] != $user_source['UID'] && ! in_array('admin_user', $privileges))
      redirect(user_driver_license_edit_link());
  } else {
    $user_source = $user;
  }

  $wants_to_drive = false;
  $has_car = false;
  $has_license_car = false;
  $has_license_3_5t_transporter = false;
  $has_license_7_5t_truck = false;
  $has_license_12_5t_truck = false;
  $has_license_forklift = false;

  $user_driver_license = UserDriverLicense($user_source['UID']);
  if ($user_driver_license === false)
    engelsystem_error('Unable to load user driver license.');
  if ($user_driver_license != null) {
    $wants_to_drive = true;
    $has_car = $user_driver_license['has_car'];
    $has_license_car = $user_driver_license['has_license_car'];
    $has_license_3_5t_transporter = $user_driver_license['has_license_3_5t_transporter'];
    $has_license_7_5t_truck = $user_driver_license['has_license_7_5t_truck'];
    $has_license_12_5t_truck = $user_driver_license['has_license_12_5t_truck'];
    $has_license_forklift = $user_driver_license['has_license_forklift'];
  }

  if (isset($_REQUEST['submit'])) {
    $ok = true;
    $wants_to_drive = isset($_REQUEST['wants_to_drive']);
    $has_car = isset($_REQUEST['has_car']);
    $has_license_car = isset($_REQUEST['has_license_car']);
    $has_license_3_5t_transporter = isset($_REQUEST['has_license_3_5t_transporter']);
    $has_license_7_5t_truck = isset($_REQUEST['has_license_7_5t_truck']);
    $has_license_12_5t_truck = isset($_REQUEST['has_license_12_5t_truck']);
    $has_license_forklift = isset($_REQUEST['has_license_forklift']);

    if ($wants_to_drive && ! $has_license_car && ! $has_license_3_5t_transporter && ! $has_license_7_5t_truck && ! $has_license_12_5t_truck && ! $has_license_forklift) {
      $ok = false;
      error(_("Please select at least one driving license."));
    }

    if ($ok) {
      if (! $wants_to_drive && $user_driver_license != null) {
        $result = UserDriverLicenses_delete($user_source['UID']);
        if ($result === false)
          engelsystem_error("Unable to remove user driver license information");
        engelsystem_log("Driver license information removed.");
        success(_("Your driver license information has been removed."));
      } else {
        if ($wants_to_drive) {
          if ($user_driver_license == null)
            $result = UserDriverLicenses_create($user_source['UID'], $has_car, $has_license_car, $has_license_3_5t_transporter, $has_license_7_5t_truck, $has_license_12_5t_truck, $has_license_forklift);
          else
            $result = UserDriverLicenses_update($user_source['UID'], $has_car, $has_license_car, $has_license_3_5t_transporter, $has_license_7_5t_truck, $has_license_12_5t_truck, $has_license_forklift);

          if ($result === false)
            engelsystem_error("Unable to save user driver license information.");
          engelsystem_log("Driver license information updated.");
        }
        success(_("Your driver license information has been saved."));
      }

      redirect(user_link($user_source));
    }
  }

  return [
      sprintf(_("Edit %s driving license information"), $user_source['Nick']),
      UserDriverLicense_edit_view($user_source, $wants_to_drive, $has_car, $has_license_car, $has_license_3_5t_transporter, $has_license_7_5t_truck, $has_license_12_5t_truck, $has_license_forklift)
  ];
}

?>
