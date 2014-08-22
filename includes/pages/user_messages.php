<?php
function messages_title() {
  return _("Messages");
}

function user_unread_messages() {
  global $user;
  
  if (isset($user)) {
    $new_messages = sql_num_query("SELECT * FROM `Messages` WHERE isRead='N' AND `RUID`=" . sql_escape($user['UID']));
    return '<span class="badge">' . $new_messages . '</span>';
  }
  return '';
}

function user_messages() {
  global $user;
  
  if (! isset($_REQUEST['action'])) {
    $users = sql_select("SELECT * FROM `User` WHERE NOT `UID`=" . sql_escape($user['UID']) . " ORDER BY `Nick`");
    
    $to_select_data = array(
        "" => _("Select recipient...") 
    );
    
    foreach ($users as $u)
      $to_select_data[$u['UID']] = $u['Nick'];
    
    $to_select = html_select_key('to', 'to', $to_select_data, '');
    
    $messages_html = "";
    $messages = sql_select("SELECT * FROM `Messages` WHERE `SUID`=" . sql_escape($user['UID']) . " OR `RUID`=" . sql_escape($user['UID']) . " ORDER BY `isRead`,`Datum` DESC");
    foreach ($messages as $message) {
      $sender_user_source = User($message['SUID']);
      if ($sender_user_source === false)
        engelsystem_error(_("Unable to load user."));
      $receiver_user_source = User($message['RUID']);
      if ($receiver_user_source === false)
        engelsystem_error(_("Unable to load user."));
      
      $messages_html .= sprintf('<tr %s> <td>%s</td> <td>%s</td> <td>%s</td> <td>%s</td><td>%s</td>', ($message['isRead'] == 'N' ? ' class="new_message"' : ''), ($message['isRead'] == 'N' ? '•' : ''), date("Y-m-d H:i", $message['Datum']), User_Nick_render($sender_user_source), User_Nick_render($receiver_user_source), str_replace("\n", '<br />', $message['Text']));
      
      $messages_html .= '<td>';
      if ($message['RUID'] == $user['UID']) {
        if ($message['isRead'] == 'N')
          $messages_html .= '<a href="' . page_link_to("user_messages") . '&action=read&id=' . $message['id'] . '">' . _("mark as read") . '</a>';
      } else {
        $messages_html .= '<a href="' . page_link_to("user_messages") . '&action=delete&id=' . $message['id'] . '">' . _("delete message") . '</a>';
      }
      $messages_html .= '</td></tr>';
    }
    
    return template_render('../templates/user_messages.html', array(
        'title' => messages_title(),
        'link' => page_link_to("user_messages"),
        'greeting' => sprintf(_("Hello %s, here can you leave messages for other angels"), User_Nick_render($user)) . '<br /><br />',
        'messages' => $messages_html,
        'new_label' => _("New"),
        'date_label' => _("Date"),
        'from_label' => _("Transmitted"),
        'to_label' => _("Recipient"),
        'text_label' => _("Message"),
        'date' => date("Y-m-d H:i"),
        'from' => User_Nick_render($user),
        'to_select' => $to_select,
        'submit_label' => _("Save") 
    ));
  } else {
    switch ($_REQUEST['action']) {
      case "read":
        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error(_("Incomplete call, missing Message ID."), true);
        
        $message = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        if (count($message) > 0 && $message[0]['RUID'] == $user['UID']) {
          sql_query("UPDATE `Messages` SET `isRead`='Y' WHERE `id`=" . sql_escape($id) . " LIMIT 1");
          redirect(page_link_to("user_messages"));
        } else
          return error(_("No Message found."), true);
        break;
      
      case "delete":
        if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error(_("Incomplete call, missing Message ID."), true);
        
        $message = sql_select("SELECT * FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
        if (count($message) > 0 && $message[0]['SUID'] == $user['UID']) {
          sql_query("DELETE FROM `Messages` WHERE `id`=" . sql_escape($id) . " LIMIT 1");
          redirect(page_link_to("user_messages"));
        } else
          return error(_("No Message found."), true);
        break;
      
      case "send":
        if (Message_send($_REQUEST['to'], $_REQUEST['text']) === true) {
          redirect(page_link_to("user_messages"));
        } else {
          return error(_("Transmitting was terminated with an Error."), true);
        }
        break;
      
      default:
        return error(_("Wrong action."), true);
    }
  }
}
?>
