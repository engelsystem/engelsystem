<?php


// path and include settings
$rootpath = str_replace(DIRECTORY_SEPARATOR . 'bootstrap.php', '', __FILE__);
define('ROOTPATH', $rootpath);

$includePath = ini_get('include_path');
$includePath .= PATH_SEPARATOR . ROOTPATH . DIRECTORY_SEPARATOR . '..';

ini_set('include_path', $includePath);
?>