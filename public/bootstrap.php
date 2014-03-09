<?php
// path and include settings
$rootpath = __DIR__ . DIRECTORY_SEPARATOR . '..';
define('ROOTPATH', $rootpath);

$includePath = ini_get('include_path');
$includePath .= PATH_SEPARATOR . ROOTPATH;

ini_set('include_path', $includePath);
?>
