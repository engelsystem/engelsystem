<?php

/**
 * Edit a user's driving license information.
 *
 * @param User $user_source
 *          The user
 * @param bool $wants_to_drive
 *          true, if the user wants to drive
 * @param UserDriverLicense $user_driver_license
 *          The user driver license
 * @return string
 */
function UserDriverLicense_edit_view($user_source, $wants_to_drive, $user_driver_license) {
  return page_with_title(sprintf(_("Edit %s driving license information"), User_Nick_render($user_source)), [
      buttons([
          button(user_link($user_source), _("Back to profile"), 'back') 
      ]),
      msg(),
      form([
          form_info(_("Privacy"), _("Your driving license information is only visible for supporters and admins.")),
          form_checkbox('wants_to_drive', _("I am willing to operate cars for the PL"), $wants_to_drive),
          div('panel panel-default', [
              div('panel-body', [
                  form_checkbox('has_car', _("I have my own car with me and am willing to use it for the PL (You'll get reimbursed for fuel)"), $user_driver_license['has_car']),
                  heading(_("Driver license"), 3),
                  form_checkbox('has_license_car', _("Car"), $user_driver_license['has_license_car']),
                  form_checkbox('has_license_3_5t_transporter', _("Transporter 3,5t"), $user_driver_license['has_license_3_5t_transporter']),
                  form_checkbox('has_license_7_5t_truck', _("Truck 7,5t"), $user_driver_license['has_license_7_5t_truck']),
                  form_checkbox('has_license_12_5t_truck', _("Truck 12,5t"), $user_driver_license['has_license_12_5t_truck']),
                  form_checkbox('has_license_forklift', _("Forklift"), $user_driver_license['has_license_forklift']) 
              ]) 
          ], 'driving_license'),
          form_submit('submit', _("Save")) 
      ]),
      '<script type="text/javascript">
        $(function() {
          if($("#wants_to_drive").is(":checked"))
            $("#driving_license").show();
          else
            $("#driving_license").hide();
        
          $("#wants_to_drive").click(
            function(e) {
              if($("#wants_to_drive").is(":checked"))
                $("#driving_license").show();
              else
                $("#driving_license").hide();
            }
          );
        });
      </script>' 
  ]);
}

?>