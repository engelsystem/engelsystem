<?php
function wakeup_title() {
  return _("Wakeup");
}

function user_wakeup() {
  global $user;

  $html = "";

  if (isset ($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
      case 'create' :
        $date = DateTime::createFromFormat("Y-m-d H:i", $_REQUEST['Date']);
        if ($date != null) {
          $date = $date->getTimestamp();
          $bemerkung = strip_request_item_nl('Bemerkung');
          $ort = strip_request_item('Ort');
          $SQL = "INSERT INTO `Wecken` (`UID`, `Date`, `Ort`, `Bemerkung`) "
          . "VALUES ('" . sql_escape($user['UID']) . "', '"
          . sql_escape($date) . "', '" . sql_escape($ort) . "', " . "'"
          . sql_escape($bemerkung) . "')";
          sql_query($SQL);
          $html .= success(_("Entry saved."), true);
        } else
          $html .= error(_("Broken date!"), true);
        break;

      case 'delete' :
        if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error(_("Incomplete call, missing wake-up ID."), true);

        $wakeup = sql_select("SELECT * FROM `Wecken` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
        if (count($wakeup) > 0 && $wakeup[0]['UID'] == $user['UID']) {
          sql_query("DELETE FROM `Wecken` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
          $html .= success(_("Wake-up call deleted."), true);
        } else
          return error(_("No wake-up found."), true);
        break;
    }
  }

  $html .= '<p>' . sprintf(_("Hello %s, here you can register for a wake-up call. Simply say when and where the angel should come to wake you."), User_Nick_render($user)) . '</p>';
  $html .= _("All ordered wake-up calls, next first.");
  $html .= '
  <table border="0" width="100%" class="border" cellpadding="2" cellspacing="1">
  <tr class="contenttopic">
  <th>' . _("Date") . '</th>
  <th>' . _("Nick") . '</th>
  <th>' . _("Place") . '</th>
  <th>' . _("Notes") . '</th>
  <th></th>
  </tr>
  ';

  $wecken_source = sql_select("SELECT * FROM `Wecken` ORDER BY `Date` ASC");
  foreach($wecken_source as $wecken) {
    $html .= '<tr class="content">';
    $html .= '<td>' . date("Y-m-d H:i", $wecken['Date']) . ' </td>';

    $user_source = User($wecken['UID']);
    if($user_source === false)
      engelsystem_error("Unable to load user.");

    $html .= '<td>' . User_Nick_render($user_source) . ' </td>';
    $html .= '<td>' . $wecken['Ort'] . ' </td>';
    $html .= '<td>' . $wecken['Bemerkung'] . ' </td>';
    if ($wecken['UID'] == $user['UID'])
      $html .= '<td><a href="' . page_link_to("user_wakeup") . '&action=delete&id=' . $wecken['ID'] . "\">" . _("delete") . '</a></td>';
    else
      $html .= '<td></td>';
    $html .= '</tr>';
  }

  $html .= '</table><hr />' . _("Schedule a new wake-up here:");

  $html .= template_render('../templates/user_wakeup.html', array (
    'wakeup_link'   => page_link_to("user_wakeup"),
    'date_text'     => _("Date"),
    'date_value'    => date("Y-m-d H:i"),
    'place_text'    => _("Place"),
    'comment_text'  => _("Notes"),
    'comment_value' => "Knock knock Leo, follow the white rabbit to the blue tent",
    'submit_text'   => _("Save")
  ));
  return $html;
}
?>
