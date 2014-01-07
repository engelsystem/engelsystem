<?php

/************************************************************************************************
 * API Documentation
 ************************************************************************************************

General:
--------
All API calls output JSON-encoded data. Client parameters should be passed encoded using JSON in HTTP POST data.
Every API Request must be contained the Api Key (using JSON parameter 'key') and the Command (using JSON parameter 'cmd').


Testing API calls (using curl):
-------------------------------
$ curl -d '{"cmd":"getVersion"}' '<Address>/?p=api'
$ curl -d '{"cmd":"getApiKey","user":"admin","pw":"admin"}' '<Address>/?p=api'
$ curl -d '{"key":"<key>","cmd":"getRoom"}' '<Address>/?p=api'
$ curl -d '{"key":"<key>","cmd":"sendmessage","uid":"23","text":"test message"}' '<Address>/?p=api'

Methods without key:
--------------------
getVersion
  Description:
    Returns API version.
  Parameters:
    nothing
  Return Example:
    {"status":"success","version": "1"}

getApiKey
  Description:
    Returns API Key version.
  Parameters:
    user (string)
    pw (string)
  Return Example:
    {"status":"success","Key":"1234567890123456789012"}

Methods with Key:
-----------------
getRoom
  Description:
    Returns a list of all Rooms (no id set) or details of a single Room (requested id)
  Parameters:
    id (integer) - Room ID
  Return Example:
    [{"RID":"1"},{"RID":"23"},{"RID":"42"}]
    {"RID":"1","Name":"Room Name","Man":null,"FromPentabarf":"","show":"Y","Number":"0"}

getAngelType
  Description:
    Returns a list of all Angel Types (no id set) or details of a single Angel Type (requested id)
  Parameters:
    id (integer) - Type ID
  Return Example:
    [{"id":"8"},{"id":"9"}]
    {"id":"9","name":"Angeltypes 2","restricted":"0"}

getUser
  Description:
    Returns a list of all Users (no id set) or details of a single User (requested id)
  Parameters:
    id (integer) - User ID
  Return Example:
    [{"UID":"1"},{"UID":"23"},{"UID":"42"}]
    {"UID":"1","Nick":"admin","Name":"Gates","Vorname":"Bill","Telefon":"","DECT":"","Handy":"","email":"","ICQ":"","jabber":"","Avatar":"115"}

getShift
  Description:
    Returns a list of all Shifte (no id set, filter is optional) or details of a single Shift (requested id)
  Parameters:
    id (integer) - Shift ID
    filterRoom (Array of integer) - Array of Room IDs (optional, for list request)
    filterTask (Array of integer) - Array if Task (optional, for list request)
    filterOccupancy (integer) - Occupancy state: (optional, for list request)
      1 occupied
      2 free
      3 occupied and free
  Return Example:
    [{"SID":"1"},{"SID":"2"},{"SID":"3"}]
    {"SID":"10","start":"1388264400","end":"1388271600","RID":"1","name":"Shift 1","URL":null,"PSID":null,\
      "ShiftEntry":[{"TID":"8","UID":"4","freeloaded":"0"}],
      "NeedAngels":[{"TID":"8","count":"1","restricted":"0","taken":1},{"TID":"9","count":"2","restricted":"0","taken":0}]}

getMessage
  Description:
    Returns a list of all Messages (no id set) or details of a single Message (requested id)
  Parameters:
    id (integer) - Message ID
  Return Example:
    [{"id":"1"},{"id":"2"},{"id":"3"}]
    {"id":"3","Datum":"1388247583","SUID":"23","RUID":"42","isRead":"N","Text":"message text"}

sendMessage
  Description:
    send a Message to an other angel
  Parameters:
    uid (integer) - User ID of the reciever
    text (string) - Message Text
  Return Example:
    {"status":"success"}

************************************************************************************************/

/**
 * General API Controller
 */
function api_controller() {
  global $user, $DataJson;
  
  header("Content-Type: application/json; charset=utf-8");
  
  // decode JSON request
  $input = file_get_contents("php://input");
  $input = json_decode($input, true);
  $_REQUEST = $input;
  
  // get command
  $cmd = '';
  if (isset($_REQUEST['cmd']))
    $cmd = strtolower($_REQUEST['cmd']);
    
    // decode commands, without key
  switch ($cmd) {
    case 'getversion':
      getVersion();
      die(json_encode($DataJson));
      break;
    case 'getapikey':
      getApiKey();
      die(json_encode($DataJson));
      break;
  }
  
  // get API KEY
  if (isset($_REQUEST['key']) && preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key']))
    $key = $_REQUEST['key'];
  else
    die(json_encode(array(
        'status' => 'failed',
        'error' => 'Missing parameter "key".' 
    )));
    
    // check API key
  $user = User_by_api_key($key);
  if ($user === false)
    die(json_encode(array(
        'status' => 'failed',
        'error' => 'Unable to find user' 
    )));
  if ($user == null)
    die(json_encode(array(
        'status' => 'failed',
        'error' => 'Key invalid.' 
    )));
    
    // decode command
  switch ($cmd) {
    case 'getroom':
      getRoom();
      break;
    case 'getangeltype':
      getAngelType();
      break;
    case 'getuser':
      // TODO Dataleak! Only coordinators are allowed to see so much user informations.
      //getUser();
      break;
    case 'getshift':
      getShift();
      break;
    case 'getmessage':
      // TODO Dataleak!
      //getMessage();
      break;
    case 'sendmessage':
      sendMessage();
      break;
    default:
      $DataJson = array(
          'status' => 'failed',
          'error' => 'Unknown Command "' . $cmd . '"' 
      );
  }
  
  // check
  if ($DataJson === false) {
    $DataJson = array(
        'status' => 'failed',
        'error' => 'DataJson === false' 
    );
  } elseif ($DataJson == null) {
    $DataJson = array(
        'status' => 'failed',
        'error' => 'DataJson == null' 
    );
  }
  
  echo json_encode($DataJson);
  die();
}

/**
 * Get Version of API
 */
function getVersion() {
  global $DataJson;
  
  $DataJson = array(
      'status' => 'success',
      'Version' => 1 
  );
}

/**
 * Get API Key
 */
function getApiKey() {
  global $DataJson;
  
  if (! isset($_REQUEST['user'])) {
    $DataJson = array(
        'status' => 'failed',
        'error' => 'Missing parameter "user".' 
    );
  } elseif (! isset($_REQUEST['pw'])) {
    $DataJson = array(
        'status' => 'failed',
        'error' => 'Missing parameter "pw".' 
    );
  } else {
    $Erg = sql_select("SELECT `UID`, `Passwort`, `api_key` FROM `User` WHERE `Nick`='" . sql_escape($_REQUEST['user']) . "'");
    
    if (count($Erg) == 1) {
      $Erg = $Erg[0];
      if (verify_password($_REQUEST['pw'], $Erg["Passwort"], $Erg["UID"])) {
        $key = $Erg["api_key"];
        $DataJson = array(
            'status' => 'success',
            'Key' => $key 
        );
      } else {
        $DataJson = array(
            'status' => 'failed',
            'error' => 'PW wrong' 
        );
      }
    } else {
      $DataJson = array(
          'status' => 'failed',
          'error' => 'User not found.' 
      );
    }
  }
  
  sleep(1);
}

/**
 * Get Room
 */
function getRoom() {
  global $DataJson;
  
  if (isset($_REQUEST['id'])) {
    $DataJson = Room($_REQUEST['id']);
  } else {
    $DataJson = Room_ids();
  }
}

/**
 * Get AngelType
 */
function getAngelType() {
  global $DataJson;
  
  if (isset($_REQUEST['id'])) {
    $DataJson = AngelType($_REQUEST['id']);
  } else {
    $DataJson = AngelType_ids();
  }
}

/**
 * Get User
 */
function getUser() {
  global $DataJson;
  
  if (isset($_REQUEST['id'])) {
    $DataJson = mUser_Limit($_REQUEST['id']);
  } else {
    $DataJson = User_ids();
  }
}

/**
 * Get Shift
 */
function getShift() {
  global $DataJson;
  
  if (isset($_REQUEST['id'])) {
    $DataJson = Shift($_REQUEST['id']);
  } else {
    $DataJson = Shifts_filtered();
  }
}

/**
 * @TODO: Why are ALL messages of ALL users returned? Data leak. It is not checked if this is my message!
 * Get Message
 */
function getMessage() {
  global $DataJson;
  
  if (isset($_REQUEST['id'])) {
    $DataJson = Message($_REQUEST['id']);
  } else {
    $DataJson = Message_ids();
  }
}

/**
 * Send Message
 */
function sendMessage() {
  global $DataJson;
  
  if (! isset($_REQUEST['uid'])) {
    $DataJson = array(
        'status' => 'failed',
        'error' => 'Missing parameter "uid".' 
    );
  } elseif (! isset($_REQUEST['text'])) {
    $DataJson = array(
        'status' => 'failed',
        'error' => 'Missing parameter "text".' 
    );
  } else {
    if (Message_send($_REQUEST['uid'], $_REQUEST['text']) === true) {
      $DataJson = array(
          'status' => 'success' 
      );
    } else {
      $DataJson = array(
          'status' => 'failed',
          'error' => 'Transmitting was terminated with an Error.' 
      );
    }
  }
}

?>
