<?php

// publically available page to feed the news to feedreaders
function user_atom() {
  global $user, $DISPLAY_NEWS;
  
  if (! isset($_REQUEST['key']) || ! preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key'])) {
    engelsystem_error("Missing key.");
  }
  $key = $_REQUEST['key'];
  
  $user = User_by_api_key($key);
  if ($user == null) {
    engelsystem_error("Key invalid.");
  }
  if (! in_array('atom', privileges_for_user($user['UID']))) {
    engelsystem_error("No privilege for atom.");
  }
  
  $news = sql_select("SELECT * FROM `News` " . (empty($_REQUEST['meetings']) ? '' : 'WHERE `Treffen` = 1 ') . "ORDER BY `ID` DESC LIMIT " . sql_escape($DISPLAY_NEWS));
  
  $output = make_atom_entries_from_news($news);
  
  header('Content-Type: application/atom+xml; charset=utf-8');
  header("Content-Length: " . strlen($output));
  raw_output($output);
}

function make_atom_entries_from_news($news_entries) {
  $html = '<?xml version="1.0" encoding="utf-8"?>
  <feed xmlns="http://www.w3.org/2005/Atom">
  <title>Engelsystem</title>
  <id>' . $_SERVER['HTTP_HOST'] . htmlspecialchars(preg_replace('#[&?]key=[a-f0-9]{32}#', '', $_SERVER['REQUEST_URI'])) . '</id>
  <updated>' . date('Y-m-d\TH:i:sP', $news_entries[0]['Datum']) . "</updated>\n";
  foreach ($news_entries as $news_entry) {
    $html .= make_atom_entry_from_news($news_entry);
  }
  $html .= "</feed>";
  return $html;
}

function make_atom_entry_from_news($news_entry) {
  return "  <entry>
    <title>" . htmlspecialchars($news_entry['Betreff']) . "</title>
    <link href=\"" . page_link_to_absolute("news_comments&amp;nid=") . "${news_entry['ID']}\"/>
      <id>" . preg_replace('#^https?://#', '', page_link_to_absolute("news")) . "-${news_entry['ID']}</id>
      <updated>" . date('Y-m-d\TH:i:sP', $news_entry['Datum']) . "</updated>
    <summary type=\"html\">" . htmlspecialchars($news_entry['Text']) . "</summary>
    </entry>\n";
}
?>
