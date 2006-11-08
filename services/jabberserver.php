<?php

/****************************************************************************************************/
//					INCLUDE
/****************************************************************************************************/
require_once('./inc/jabber.php');
require_once("./inc/funktion_jabber.php");
include("./inc/config_MessegeServer.php");

// Set time limit to indefinite execution
set_time_limit( 0 );

/****************************************************************************************************/
//					show MODE
/****************************************************************************************************/
if( DEBUG)
	echo "DEBUG mode is enable\n\tjabber is disable\n\n";

/****************************************************************************************************/
//					INIT jabber
/****************************************************************************************************/
if( !DEBUG)
{
	echo "INIT jabber\n";
	$jabber = new Jabber($server, $port, $username, $password, $resource);
	if ( !($jabber->Connect() && $jabber->SendAuth())) 
		die("Couldn't connect to Jabber Server.");
}


/****************************************************************************************************/
//					INIT socked
/****************************************************************************************************/
echo "INIT socked\n";
	//http://de3.php.net/manual/de/function.socket-listen.php
// Create a UDP socket
$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP) or die('Could not create socked ('.socket_strerror(socket_last_error()). ')');
// Bind the socket to an address/port
socket_bind($sock, SERVER_ADDRESS, SERVER_PORT) or die('Could not bind to address ('.socket_strerror(socket_last_error()). ')');
// Setzt Nonbock Mode
socket_set_nonblock($sock);


/****************************************************************************************************/
//					Loop continuos
/****************************************************************************************************/
$RUNNING = TRUE;
while( $RUNNING )
{
	if( @socket_recvfrom($sock, $data, 65535, 0, $ip, $port))
	{
		//daten empfangen
		$data = substr($data,0, strlen($data)-1); //ENTER entfernen
		echo "\n". gmdate("Y-m-d H:i:s", time()). "\tresive from $ip:$port ". strlen($data). " byte data ($data)\n";
		PackedAnalyser( $data);
	}

	usleep(100000); // 100ms delay keeps the doctor away
} // end while
	
//disconnect jabber	
if( !DEBUG)
	$jabber->Disconnect();

// Close the master sockets
socket_close($sock); 
	
/****************************************************************************************************/
/****************************************** MAIN END ************************************************/
/****************************************************************************************************/


/****************************************************************************************************/
//***** function PackedAnalyser
/****************************************************************************************************/
function PackedAnalyser( $data )
{
	GLOBAL $jabber, $RUNNING;
	// init array
	$matches = array();

	//#message
	if( preg_match( "/^#(message) ([^ ]+) (.+)/i", $data, $matches ) )
	{
		if( $matches[2]=="" || $matches[3]=="")
		{
			// Wrong syntax
			echo "\t\t\t\t#messaage parameter fail\n";
		}
		else
		{
			// Whisper
			if( !DEBUG)
				$jabber->SendMessage($value, "normal", NULL, array("body" => $message, "subject" => "Error in Pentabarf"), NULL);
			else	
				echo "\t\t\t\tmessage to:\"". $matches[2]. "\" Text: \"". $matches[3]. "\"\n";
		}
	}
	elseif( preg_match( "/^#quit/i", $data, $matches ) )
	{
		if( DEBUG)
		{
			echo "\t\t\t\tSystem Shutdown\n\n";
			$RUNNING = FALSE;
		}
	}
	else
	{
		echo "\t\t\t\tcommand not found\n\n";
	}
}

?>
