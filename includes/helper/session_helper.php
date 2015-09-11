<?php
/**
 * Set lifetime of php session.
 *
 * @param int $lifetime
 *          Lifetime in minutes
 * @param string $application_name
 *          Name of the application
 */
function session_lifetime($lifetime, $application_name) {
  // Set session save path and name
  $session_save_path = '/tmp/' . $application_name;
  if (! file_exists($session_save_path))
    mkdir($session_save_path);
  if (file_exists($session_save_path))
    session_save_path($session_save_path);
  session_name($application_name);
  
  // Set session lifetime
  ini_set('session.gc_maxlifetime', $lifetime * 60);
  ini_set('session.gc_probability', 1);
  ini_set('session.gc_divisor', 100);
  
  // Cookie settings (lifetime)
  ini_set('session.cookie_lifetime', $lifetime * 60);
}
