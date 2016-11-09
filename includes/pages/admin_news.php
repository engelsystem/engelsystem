<?php

function admin_news() {
  global $user;
  
  if (! isset($_GET["action"])) {
    redirect(page_link_to("news"));
  }
  
  $html = '<div class="col-md-12"><h1>' . _("Edit news entry") . '</h1>' . msg();
  if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id'])) {
    $news_id = $_REQUEST['id'];
  } else {
    return error("Incomplete call, missing News ID.", true);
  }
  
  $news = sql_select("SELECT * FROM `News` WHERE `ID`='" . sql_escape($news_id) . "' LIMIT 1");
  if (empty($news)) {
    return error("No News found.", true);
  }
  switch ($_REQUEST["action"]) {
    default:
      redirect(page_link_to('news'));
    case 'edit':
      list($news) = $news;
      
      $user_source = User($news['UID']);
      
      $html .= form([
          form_info(_("Date"), date("Y-m-d H:i", $news['Datum'])),
          form_info(_("Author"), User_Nick_render($user_source)),
          form_text('eBetreff', _("Subject"), $news['Betreff']),
          form_textarea('eText', _("Message"), $news['Text']),
          form_checkbox('eTreffen', _("Meeting"), $news['Treffen'] == 1, 1),
          form_submit('submit', _("Save")) 
      ], page_link_to('admin_news&action=save&id=' . $news_id));
      
      $html .= '<a class="btn btn-danger" href="' . page_link_to('admin_news&action=delete&id=' . $news_id) . '"><span class="glyphicon glyphicon-trash"></span> ' . _("Delete") . '</a>';
      break;
    
    case 'save':
      list($news) = $news;
      
      sql_query("UPDATE `News` SET 
              `Datum`='" . sql_escape(time()) . "', 
              `Betreff`='" . sql_escape($_POST["eBetreff"]) . "', 
              `Text`='" . sql_escape($_POST["eText"]) . "', 
              `UID`='" . sql_escape($user['UID']) . "', 
              `Treffen`='" . sql_escape($_POST["eTreffen"]) . "' 
              WHERE `ID`='" . sql_escape($news_id) . "'");
      engelsystem_log("News updated: " . $_POST["eBetreff"]);
      success(_("News entry updated."));
      redirect(page_link_to("news"));
      break;
    
    case 'delete':
      list($news) = $news;
      
      sql_query("DELETE FROM `News` WHERE `ID`='" . sql_escape($news_id) . "' LIMIT 1");
      engelsystem_log("News deleted: " . $news['Betreff']);
      success(_("News entry deleted."));
      redirect(page_link_to("news"));
      break;
  }
  return $html . '</div>';
}
?>