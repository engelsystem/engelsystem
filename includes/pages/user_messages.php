<?php
function user_unread_messages() {
  global $user, $privileges;

  if (in_array("user_messages", $privileges)) {
    $new_messages = sql_num_query("SELECT * FROM `Messages` WHERE isRead='N' AND `RUID`=" . sql_escape($user['UID']));

    if ($new_messages > 0)
      return sprintf('<p class="info"><a href="%s">%s %s %s</a></p><hr />', page_link_to("user_messages"), Get_Text("pub_messages_new1"), $new_messages, Get_Text("pub_messages_new2"));
  }

  return "";
}

function user_messages() {
  global $user;

  if (!isset ($_REQUEST['action'])) {
    $users = sql_select("SELECT * FROM `User` WHERE NOT `UID`=" . sql_escape($user['UID']) . " ORDER BY `Nick`");

    $to_select_data = array (
      "" => "Select recipient..."
    );

    foreach ($users as $u)
      $to_select_data[$u['UID']] = $u['Nick'];

    $to_select = html_select_key('to', 'to', $to_select_data, '');

    $messages_html = "";
    $messages = sql_select("SELECT * FROM `Messages` WHERE `SUID`=" . sql_escape($user['UID']) . " OR `RUID`=" . sql_escape($user['UID']) . " ORDER BY `isRead`,`Datum` DESC");
    foreach ($messages as $message) {
      $sender_user_source = User($message['SUID']);
      if($sender_user_source === false)
        engelsystem_error("Unable to load user.");
      $receiver_user_source = User($message['RUID']);
      if($receiver_user_source === false)
        engelsystem_error("Unable to load user.");

      $messages_html .= sprintf(
          '<tr %s> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td><td>%s</td>',
          ($message['isRead'] == 'N' ? ' class="new_message"' : ''),
          ($message['isRead'] == 'N' ? '•' : ''),
          date("Y-m-d H:i", $message['Datum']),
          User_Nick_render($sender_user_source),
          User_Nick_render($receiver_user_source),
          str_replace("\n", '<br />', $message['Text'])
      );

      $messages_html .= '<td>';
      if ($message['RUID'] == $user['UID']) {
        if ($message['isRead'] == 'N')
          $messages_html .= '<a href="' . page_link_to("user_messages") . '&action=read&id=' . $message['id'] . '">' . Get_Text("pub_messages_MarkRead") . '</a>';
      } else {
        $messages_html .= '<a href="' . page_link_to("user_messages") . '&action=delete&id=' . $message['id'] . '">' . Get_Text("pub_messages_DelMsg") . '</a>';
      }
      $messages_html .= '</td></tr>';
    }

    return template_render('../templates/user_messages.html', array (
      'link' => page_link_to("user_messages"),
      'greeting' => Get_Text("Hello") . User_Nick_render($user) . ", <br />\n" . Get_Text("pub_messages_text1") . "<br /><br />\n",
      'messages' => $messages_html,
      'new_label' => Get_Text("pub_messages_Neu"),
      'date_label' => Get_Text("pub_messages_Datum"),
      'from_label' => Get_Text("pub_messages_Von"),
      'to_label' => Get_Text("pub_messages_An"),
      'text_label' => Get_Text("pub_messages_Text"),
      'date' => date("Y-m-d H:i"),
      'from' => User_Nick_render($user),
      'to_select' => $to_select,
      'submit_label' => Get_Text("save")
    ));
  } else {
    switch ($_REQUEST['action']) {
      case "read" :
        if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing Message ID.", true);

        $message = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        if (count($message) > 0 && $message[0]['RUID'] == $user['UID']) {
          sql_query("UPDATE `Messages` SET `isRead`='Y' WHERE `id`=" . sql_escape($id) . " LIMIT 1");
          redirect(page_link_to("user_messages"));
        } else
          return error("No Message found.", true);
        break;

      case "delete" :
        if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing Message ID.", true);

        $message = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        if (count($message) > 0 && $message[0]['SUID'] == $user['UID']) {
          sql_query("DELETE FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
          redirect(page_link_to("user_messages"));
        } else
          return error("No Message found.", true);
        break;

      case "send" :
        $text = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['text']));
        $to = preg_replace("/([^0-9]{1,})/ui", '', strip_tags($_REQUEST['to']));
        if ($text != "" && is_numeric($to) && sql_num_query("SELECT * FROM `User` WHERE `UID`=" . sql_escape($to) . " AND NOT `UID`=" . sql_escape($user['UID']) . " LIMIT 1") > 0) {
          sql_query("INSERT INTO `Messages` SET `Datum`=" . sql_escape(time()) . ", `SUID`=" . sql_escape($user['UID']) . ", `RUID`=" . sql_escape($to) . ", `Text`='" . sql_escape($text) . "'");
          redirect(page_link_to("user_messages"));
        } else {
          return error(Get_Text("pub_messages_Send_Error"), true);
        }
        break;

      default :
        return error("Wrong action.", true);
    }
  }
}
?>
