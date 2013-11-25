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
function ShiftEntry_edit_view($angel, $date, $location, $title, $type, $comment) {
  return page(array(
      form(array(
          form_info(_("Angel:"), $angel),
          form_info(_("Date, Duration:"), $date),
          form_info(_("Location:"), $location),
          form_info(_("Title:"), $title),
          form_info(_("Type:"), $type),
          form_textarea('comment', _("Comment (for your eyes only):"), $comment),
          form_submit('submit', _("Save")) 
      )) 
  ));
}

?>