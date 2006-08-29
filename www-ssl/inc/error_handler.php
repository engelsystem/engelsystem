<?php

  require_once("./inc/funktion_jabber.php");

  // global array for collected error_messages
  $error_messages = array();

  // general error handler collecting all messages in an array
  function Error_Handler($error_number, $error_string, $error_file, $error_line, $error_context)
  {
    global $error_messages;
    array_push($error_messages, "Error Number: ".$error_number."\nError String: ".$error_string."\nError File: ".$error_file."\nError Line: ".$error_line."\n");
  }

  // register error handler
//  set_error_handler("Error_Handler", E_ALL);
  set_error_handler("Error_Handler");


  // send errors 
  function send_errors()
  {
    global $error_messages;

    if (!$error_messages) return;
    
    $url = $_SERVER['PHP_SELF'];

    $message = "";
    foreach($error_messages as $value)
      $message .= $value."\n";
    $message .= "\n\n\n\n\n";
    
    if( isset( $_SESSION))
      foreach ($_SESSION as $k => $v ) 	 
        $message .= "_SESSION: $k = $v\n"; 
    if( isset( $_SESSION['CVS']))
      foreach ($_SESSION['CVS'] as $k => $v ) 	 
        if( strlen($k)>3 ) 
          $message .= "_SESSION['CVS']: $k = $v\n"; 
    foreach ($_SERVER as $k => $v ) 	 
      $message .= "_SERVER: $k = $v\n"; 
    if( isset( $_POST))
      foreach ($_POST as $k => $v ) 	 
        $message .= "_POST: $k = $v\n"; 
    if( isset( $_GET))
      foreach ($_GET as $k => $v ) 	 
	$message .= "_GET: $k = $v\n"; 

    send_message($message);

    // display error messages on screen too for developers
    if ($_SESSION['CVS']['admin/debug.php']=='Y') 
    {
      echo "<pre id='error'>\n".$message."</pre>";
    }

}

  register_shutdown_function("send_errors");

  // send jabber message and email
  function send_message(&$message)
  {
    chdir(dirname(__FILE__));
    require_once('../inc/jabber.php');

    if (isset($jabber_recipient) && count($jabber_recipient)) {
      $jabber = new Jabber($server, $port, $username, $password, $resource);
      if ($jabber->Connect() && $jabber->SendAuth()) {
        foreach($jabber_recipient as $value)
        {
          $jabber->SendMessage($value, "normal", NULL, array("body" => $message, "subject" => "Error in Pentabarf"), NULL);
        }
        $jabber->Disconnect();
      } else {
        array_push($message, "Couldn't connect to Jabber Server.");
      }
    }

    if (isset($mail_recipient) && count($mail_recipient)) {
      foreach($mail_recipient as $to) {
        mail($to, isset($mail_subject) ? $mail_subject : "Pentabarf Error", $message);
      }
    }
  }

?>
