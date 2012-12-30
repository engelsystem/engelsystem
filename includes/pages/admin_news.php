<?php
function admin_news() {
  global $user;

  if (!isset ($_GET["action"])) {
    redirect(page_link_to("news"));
  } else {
    $html = "";
    switch ($_GET["action"]) {
      case 'edit' :
        if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing News ID.", true);

        $news = sql_select("SELECT * FROM `News` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
        if (count($news) > 0) {
          list ($news) = $news;

          $html .= '<a href="' . page_link_to("news") . '">&laquo Back</a>';

          $html .= "<form action=\"" . page_link_to("admin_news") . "&action=save\" method=\"post\">\n";

          $html .= "<table>\n";
          $html .= "  <tr><td>Datum</td><td>" .
              date("Y-m-d H:i", $news['Datum']) . "</td></tr>\n";
          $html .= "  <tr><td>Betreff</td><td><input type=\"text\" size=\"40\" name=\"eBetreff\" value=\"" .
              $news["Betreff"] . "\"></td></tr>\n";
          $html .= "  <tr><td>Text</td><td><textarea rows=\"10\" cols=\"80\" name=\"eText\">" .
              $news["Text"] . "</textarea></td></tr>\n";
          $html .= "  <tr><td>Engel</td><td>" .
              UID2Nick($news["UID"]) . "</td></tr>\n";
          $html .= "  <tr><td>Treffen</td><td>" . html_select_key('eTreffen', 'eTreffen', array (
            '1' => "Ja",
            '0' => "Nein"
          ), $news['Treffen']) . "</td></tr>\n";
          $html .= "</table>";

          $html .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n";
          $html .= "<input type=\"submit\" name=\"submit\" value=\"Speichern\">\n";
          $html .= "</form>";

          $html .= "<form action=\"" . page_link_to("admin_news") . "&action=delete\" method=\"POST\">\n";
          $html .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n";
          $html .= "<input type=\"submit\" name=\"submit\" value=\"Löschen\">\n";
          $html .= "</form>";
        } else
          return error("No News found.", true);
        break;

      case 'save' :
        if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing News ID.", true);

        $news = sql_select("SELECT * FROM `News` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
        if (count($news) > 0) {
          list ($news) = $news;

          sql_query("UPDATE `News` SET `Datum`='" . sql_escape(time()) . "', `Betreff`='" . sql_escape($_POST["eBetreff"]) . "', `Text`='" . sql_escape($_POST["eText"]) . "', `UID`='" . sql_escape($user['UID']) .
              "', `Treffen`='" . sql_escape($_POST["eTreffen"]) . "' WHERE `ID`=".sql_escape($id)." LIMIT 1");
          engelsystem_log("News updated: " . $_POST["eBetreff"]);
          redirect(page_link_to("news"));
        } else
          return error("No News found.", true);
        break;

      case 'delete' :
        if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
          $id = $_REQUEST['id'];
        else
          return error("Incomplete call, missing News ID.", true);

        $news = sql_select("SELECT * FROM `News` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
        if (count($news) > 0) {
          list ($news) = $news;

          sql_query("DELETE FROM `News` WHERE `ID`=" . sql_escape($id) . " LIMIT 1");
          engelsystem_log("News deleted: " . $news['Betreff']);
          redirect(page_link_to("news"));
        } else
          return error("No News found.", true);
        break;
    }
  }
  return $html;
}
?>