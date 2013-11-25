<?php
$locales = array(
    'de_DE.UTF-8' => "Deutsch",
    'en_US.UTF-8' => "English" 
);

$default_locale = 'en_US.UTF-8';

/**
 * Initializes gettext for internationalization and updates the sessions locale to use for translation.
 */
function gettext_init() {
  global $locales, $default_locale;
  
  if (isset($_REQUEST['set_locale']) && in_array($_REQUEST['set_locale'], array_keys($locales)))
    $_SESSION['locale'] = $_REQUEST['set_locale'];
  elseif (! isset($_SESSION['locale']))
    $_SESSION['locale'] = $default_locale;
  
  putenv('LC_ALL=' . $_SESSION['locale']);
  setlocale(LC_ALL, $_SESSION['locale']);
  bindtextdomain('default', '../locale');
  bind_textdomain_codeset('default', 'UTF-8');
  textdomain('default');
}

/**
 * Renders language selection.
 *
 * @return string
 */
function make_langselect() {
  global $locales;
  $URL = $_SERVER["REQUEST_URI"] . (strpos($_SERVER["REQUEST_URI"], "?") > 0 ? '&' : '?') . "set_locale=";
  
  $html = '<p class="content">';
  foreach ($locales as $locale => $name)
    $html .= '<a class="sprache" href="' . htmlspecialchars($URL) . $locale . '"><img src="pic/flag/' . $locale . '.png" alt="' . $name . '" title="' . $name . '"></a>';
  $html .= '</p>';
  return '<nav class="container"><h4>' . _("Language") . '</h4>' . $html . '</nav>';
}

?>