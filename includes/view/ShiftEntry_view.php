<?php

/**
 * Display form for adding/editing a shift entry.
 * @param string $angel
 * @param string $date
 * @param string $location
 * @param string $title
 * @param string $type
 * @param string $comment
 * 
 * @return string
 */
function ShiftEntry_edit_view($angel, $date, $location, $title, $type, $comment, $freeloaded, $freeload_comment, $user_admin_shifts = false) {
  if ($user_admin_shifts) {
    $freeload_form = array(
        form_checkbox('freeloaded', _("Freeloaded"), $freeloaded),
        form_textarea('freeload_comment', _("Freeload comment (Only for shift coordination):"), $freeload_comment) 
    );
  } else {
    $freeload_form = array();
  }
  return page_with_title(_("Edit shift entry"), array(
      form(array(
          form_info(_("Angel:"), $angel),
          form_info(_("Date, Duration:"), $date),
          form_info(_("Location:"), $location),
          form_info(_("Title:"), $title),
          form_info(_("Type:"), $type),
          form_textarea('comment', _("Comment (for your eyes only):"), $comment),
          join("", $freeload_form),
          form_submit('submit', _("Save")) 
      )) 
  ));
}

?>