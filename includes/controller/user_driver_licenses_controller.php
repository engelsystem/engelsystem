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

    $angeltypes = User_angeltypes($user->id);
    $user_driver_license = UserDriverLicense($user->id);

    // User has already entered data, no hint needed.
    if (!empty($user_driver_license)) {
        return null;
    }

    foreach ($angeltypes as $angeltype) {
        if ($angeltype['requires_driver_license']) {
            return sprintf(
                __('You joined an angeltype which requires a driving license. Please edit your driving license information here: %s.'),
                '<a href="' . user_driver_license_edit_link() . '">' . __('driving license information') . '</a>'
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
        redirect(page_link_to(''));
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
            redirect(user_driver_license_edit_link());
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
    global $privileges;
    $user = auth()->user();
    $request = request();
    $user_source = user_driver_license_load_user();

    // only privilege admin_user can edit other users driver license information
    if ($user->id != $user_source->id && !in_array('admin_user', $privileges)) {
        redirect(user_driver_license_edit_link());
    }

    $user_driver_license = UserDriverLicense($user_source->id);
    if (empty($user_driver_license)) {
        $wants_to_drive = false;
        $user_driver_license = UserDriverLicense_new();
    } else {
        $wants_to_drive = true;
    }

    if ($request->hasPostData('submit')) {
        $wants_to_drive = $request->has('wants_to_drive');
        if ($wants_to_drive) {
            $user_driver_license['has_car'] = $request->has('has_car');
            $user_driver_license['has_license_car'] = $request->has('has_license_car');
            $user_driver_license['has_license_3_5t_transporter'] = $request->has('has_license_3_5t_transporter');
            $user_driver_license['has_license_7_5t_truck'] = $request->has('has_license_7_5t_truck');
            $user_driver_license['has_license_12_5t_truck'] = $request->has('has_license_12_5t_truck');
            $user_driver_license['has_license_forklift'] = $request->has('has_license_forklift');

            if (UserDriverLicense_valid($user_driver_license)) {
                if (empty($user_driver_license['user_id'])) {
                    $user_driver_license = UserDriverLicenses_create($user_driver_license, $user_source->id);
                } else {
                    UserDriverLicenses_update($user_driver_license);
                }
                engelsystem_log('Driver license information updated.');
                success(__('Your driver license information has been saved.'));
                redirect(user_link($user_source->id));
            } else {
                error(__('Please select at least one driving license.'));
            }
        } elseif (!empty($user_driver_license['user_id'])) {
            UserDriverLicenses_delete($user_source->id);
            engelsystem_log('Driver license information removed.');
            success(__('Your driver license information has been removed.'));
            redirect(user_link($user_source->id));
        }
    }

    return [
        sprintf(__('Edit %s driving license information'), $user_source->name),
        UserDriverLicense_edit_view($user_source, $wants_to_drive, $user_driver_license)
    ];
}
