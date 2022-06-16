<?php

use Engelsystem\Models\User\User;

/**
 * Generates a hint, if user joined angeltypes that require a driving license and the user has no driver license
 * information provided.
 *
 * @return string|null
 */
function user_driver_license_required_hint()
{
    $user = auth()->user();

    // User has already entered data, no hint needed.
    if ($user->license->wantsToDrive()) {
        return null;
    }

    $angeltypes = User_angeltypes($user->id);
    foreach ($angeltypes as $angeltype) {
        if ($angeltype['requires_driver_license']) {
            return sprintf(
                __('You joined an angeltype which requires a driving license. Please edit your driving license information here: %s.'),
                '<a href="' . user_driver_license_edit_link() . '" class="text-info">' . __('driving license information') . '</a>'
            );
        }
    }

    return null;
}

/**
 * Route user driver licenses actions.
 *
 * @return array
 */
function user_driver_licenses_controller()
{
    $user = auth()->user();

    if (!$user) {
        throw_redirect(page_link_to());
    }

    $action = strip_request_item('action', 'edit');

    switch ($action) {
        default:
        case 'edit':
            return user_driver_license_edit_controller();
    }
}

/**
 * Link to user driver license edit page for given user.
 *
 * @param User $user
 * @return string
 */
function user_driver_license_edit_link($user = null)
{
    if (!$user) {
        return page_link_to('user_driver_licenses');
    }

    return page_link_to('user_driver_licenses', ['user_id' => $user->id]);
}

/**
 * Loads the user for the driver license.
 *
 * @return User
 */
function user_driver_license_load_user()
{
    $request = request();
    $user_source = auth()->user();

    if ($request->has('user_id')) {
        $user_source = User::find($request->input('user_id'));
        if (empty($user_source)) {
            throw_redirect(user_driver_license_edit_link());
        }
    }

    return $user_source;
}

/**
 * Edit a users driver license information.
 *
 * @return array
 */
function user_driver_license_edit_controller()
{
    $user = auth()->user();
    $request = request();
    $user_source = user_driver_license_load_user();

    // only privilege admin_user can edit other users driver license information
    if ($user->id != $user_source->id && !auth()->can('admin_user')) {
        throw_redirect(user_driver_license_edit_link());
    }

    $driverLicense = $user_source->license;
    if ($request->hasPostData('submit')) {
        if ($request->has('wants_to_drive')) {
            $driverLicense->has_car = $request->has('has_car');
            $driverLicense->drive_car = $request->has('has_license_car');
            $driverLicense->drive_3_5t = $request->has('has_license_3_5t_transporter');
            $driverLicense->drive_7_5t = $request->has('has_license_7_5t_truck');
            $driverLicense->drive_12t= $request->has('has_license_12t_truck');
            $driverLicense->drive_forklift = $request->has('has_license_forklift');

            if ($driverLicense->wantsToDrive()) {
                $driverLicense->save();

                engelsystem_log('Driver license information updated.');
                success(__('Your driver license information has been saved.'));
                throw_redirect(user_link($user_source->id));
            } else {
                error(__('Please select at least one driving license.'));
            }
        } else {
            $driverLicense->has_car = false;
            $driverLicense->drive_car = false;
            $driverLicense->drive_3_5t = false;
            $driverLicense->drive_7_5t = false;
            $driverLicense->drive_12t = false;
            $driverLicense->drive_forklift = false;
            $driverLicense->save();

            engelsystem_log('Driver license information removed.');
            success(__('Your driver license information has been removed.'));
            throw_redirect(user_link($user_source->id));
        }
    }

    return [
        sprintf(__('Edit %s driving license information'), $user_source->name),
        UserDriverLicense_edit_view($user_source, $driverLicense)
    ];
}
