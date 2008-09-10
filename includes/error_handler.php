<?php

  require_once("./inc/funktion_jabber.php");

  // global array for collected error_messages
  $error_messages = array();

  // general error handler collecting all messages in an array
  function Error_Handler($error_number, $error_string, $error_file, $error_line, $error_context)
  {
    global $error_messages, $con;

    //SQL error genauer analysiert
    $Temp = "";
    foreach ($error_context as $k => $v )
        if( (strpos( "0$k", "sql") > 0) || (strpos( "0$k", "SQL") > 0))
            $Temp .= "Error Context: $k = $v\n";

    if( (strpos( "0$error_string", "MySQL") > 0) )
    	$Temp .= "Error MySQL: ". mysql_error($con). "\n";
   
    //übergeben des arrays
    array_push( $error_messages, "Error Number: $error_number\n".
    				 "Error String: $error_string\n".
				 "Error File: $error_file\n".
				 "Error Line: $error_line\n".
				 (strlen($Temp)? "$Temp": "")
				 );
  }

  // register error handler
  set_error_handler("Error_Handler");

  ini_set( "error_reporting", E_ALL);
  if( $DEBUG)
  {
	  ini_set( "display_errors", "On");
	  ini_set( "display_startup_errors", "On");
	  ini_set( "html_errors", "On");
  }
  
  // send errors 
  function send_errors()
  {
    global $error_messages;

    if (!$error_messages) return;
    
    $url = $_SERVER['PHP_SELF'];

    $message = "";
    foreach($error_messages as $value)
      $message .= $value."\n";
    $message .= "\n";
    
    if( isset( $_POST))
    {
      foreach ($_POST as $k => $v ) 	 
          $message .= "_POST: $k = ". ( $k!="password"? $v : "???..."). "\n"; 
      $message .= "\n";
    }
    
    if( isset( $_GET))
    {
      foreach ($_GET as $k => $v ) 	 
	$message .= "_GET: $k = $v\n"; 
      $message .= "\n";
    }
    
    $message .= "\n\n";
    
    if( isset( $_SESSION))
    {
      foreach ($_SESSION as $k => $v ) 	 
        $message .= "_SESSION: $k = $v\n"; 
      $message .= "\n";
    }
    
    if( isset( $_SESSION['CVS']))
    {
      foreach ($_SESSION['CVS'] as $k => $v ) 	 
        if( strlen($k)>3 ) 
          $message .= "_SESSION['CVS']: $k = $v\n"; 
      $message .= "\n";
    }
    
    foreach ($_SERVER as $k => $v ) 	 
      if( strpos( "0$k", "SERVER_")==0)
          $message .= "_SERVER: $k = $v\n"; 

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
    require_once('../inc/config_jabber.php');

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
