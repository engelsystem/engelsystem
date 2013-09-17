<?php
/**
 * Names of available languages.
 */
$languages = array (
  'DE' => "Deutsch",
  'EN' => "English"
);

/**
 * Display acutual translation of given text id.
 * @param string $TextID
 * @param bool $NoError
 * @return string
 */
function Get_Text($TextID, $NoError = false) {
  global $debug;

  if (!isset ($_SESSION['Sprache']))
    $_SESSION['Sprache'] = "EN";
  if ($_SESSION['Sprache'] == "")
    $_SESSION['Sprache'] = "EN";
  if (isset ($_GET["SetLanguage"]))
    $_SESSION['Sprache'] = $_GET["SetLanguage"];

  $sprache_source = Sprache($TextID, $_SESSION['Sprache']);
  if($sprache_source === false)
    engelsystem_error("Unable to load text key.");
  if($sprache_source == null) {
    if($NoError && !$debug)
      return "";
    return $TextID;
  }
  return $sprache_source['Text'];
}
?>