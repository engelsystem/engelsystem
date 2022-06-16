<?php

use Engelsystem\Models\User\License;
use Engelsystem\Models\User\User;

/**
 * Edit a user's driving license information.
 *
 * @param User  $user_source         The user
 * @param License $user_driver_license The user driver license
 * @return string
 */
function UserDriverLicense_edit_view($user_source, $user_driver_license)
{
    return page_with_title(sprintf(__('Edit %s driving license information'), User_Nick_render($user_source)), [
        buttons([
            button(user_link($user_source->id), __('Back to profile'), 'back')
        ]),
        msg(),
        form([
            form_info(__('Privacy'), __('Your driving license information is only visible for supporters and admins.')),
            form_checkbox('wants_to_drive', __('I am willing to drive a car for the event'), $user_driver_license->wantsToDrive()),
            div('m-3', [
                    form_checkbox(
                        'has_car',
                        __('I have my own car with me and am willing to use it for the event (You\'ll get reimbursed for fuel)'),
                        $user_driver_license->has_car
                    ),
                    heading(__('Driver license'), 3),
                    form_checkbox('has_license_car', __('Car'), $user_driver_license->drive_car),
                    form_checkbox(
                        'has_license_3_5t_transporter',
                        __('Transporter 3,5t'),
                        $user_driver_license->drive_3_5t
                    ),
                    form_checkbox(
                        'has_license_7_5t_truck',
                        __('Truck 7,5t'),
                        $user_driver_license->drive_7_5t
                    ),
                    form_checkbox(
                        'has_license_12t_truck',
                        __('Truck 12t'),
                        $user_driver_license->drive_12t
                    ),
                    form_checkbox(
                        'has_license_forklift',
                        __('Forklift'),
                        $user_driver_license->drive_forklift
                    )
            ], 'driving_license'),
            form_submit('submit', __('Save'))
        ]),
        '
        <script type="text/javascript">
            $(function() {
                let checkbox = $(\'#wants_to_drive\');
                if(checkbox.is(\':checked\'))
                    $(\'#driving_license\').show();
                else
                    $(\'#driving_license\').hide();

                checkbox.click(function() {
                if($(\'#wants_to_drive\').is(\':checked\'))
                    $(\'#driving_license\').show();
                else
                    $(\'#driving_license\').hide();
                });
            });
        </script>
        '
    ], true);
}
