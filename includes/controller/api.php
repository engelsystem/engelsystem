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
$ curl -d '{"key":"<key>","cmd":"getVersion"}' '<Address>/?p=api'


Methods:
--------
getVersion
  Description:
    Returns API version. 
  Parameters:
    nothing
  Return Example:
    {"version": "1"}

getRoom
  Description:
    Returns a list of all Rooms (no id set) or details of a single Room (requested id) 
  Parameters:
    id (integer) - Room ID 
  Return Example:
    [{"RID":"1"},{"RID":"2"},{"RID":"3"},{"RID":"4"}]
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
    {"SID":"1","start":"1388185200","end":"1388199600","RID":"1","name":"Shift 1","URL":null,"PSID":null}

getMessage
  Description:
    Returns a list of all Messages (no id set) or details of a single Message (requested id) 
  Parameters:
    id (integer) - Message ID 
  Return Example:
    [{"id":"1"},{"id":"2"},{"id":"3"}]
    {"id":"3","Datum":"1388247583","SUID":"23","RUID":"42","isRead":"N","Text":"message text"}


************************************************************************************************/


/**
 * General API Controller
 */
function api_controller() {
  global $DataJson, $_REQUEST;
 
  // decode JSON request
  $input = file_get_contents("php://input");
  $input = json_decode($input, true);
  $_REQUEST = $input;

  // get API KEY
  if (isset($_REQUEST['key']) && preg_match("/^[0-9a-f]{32}$/", $_REQUEST['key']))
    $key = $_REQUEST['key'];
  else
    die("Missing key.");
  
  // check API key
  $user = User_by_api_key($key);
  if ($user === false)
    die("Unable to find user.");
  if ($user == null)
    die("Key invalid.");

  // get command
  $cmd='';
  if (isset($_REQUEST['cmd']) )
    $cmd = strtolower( $_REQUEST['cmd']);

  // decode command
  switch( $cmd) {
    case 'echo':
      $DataJson = $input;
      break;
    case 'getversion':
      getVersion();
      break;
    case 'getroom':
      getRoom();
      break;
    case 'getangeltype':
      getAngelType();
      break;
    case 'getuser':
      getUser();
      break;
    case 'getshift':
      getShift();
      break;
    case 'getmessage':
      getMessage();
      break;
    default:
      die("Unknown Command (". $cmd. ")");
  }
 
  
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode($DataJson);
  die();
}

/**
 * Get Version of API
 */
function getVersion(){
  global $DataJson;
  $DataJson['Version'] = 1;
}

/**
 * Get Room 
 */
function getRoom(){
  global $DataJson, $_REQUEST;
  
  if (isset($_REQUEST['id']) ) {
    $DataJson = mRoom( $_REQUEST['id']);
  } else {
    $DataJson = mRoomList();
  }
}

/**
 * Get AngelType
 */
function getAngelType(){
  global $DataJson, $_REQUEST;

  if (isset($_REQUEST['id']) ) {
    $DataJson = mAngelType( $_REQUEST['id']);
  } else {
    $DataJson = mAngelTypeList();
  }
}

/**
 * Get User
 */
function getUser(){
  global $DataJson, $_REQUEST;
  
  if (isset($_REQUEST['id']) ) {
    $DataJson = mUser_Limit( $_REQUEST['id']);
  } else {
    $DataJson = mUserList();
  }
}

/**
 * Get Shift
 */
function getShift(){
  global $DataJson, $_REQUEST;

  if (isset($_REQUEST['id']) ) {
    $DataJson = mShift( $_REQUEST['id']);
  } else {
    $DataJson = mShiftList();
  }
}

/**
 * Get Message
 */
function getMessage(){
  global $DataJson, $_REQUEST;

  if (isset($_REQUEST['id']) ) {
    $DataJson = mMessage( $_REQUEST['id']);
  } else {
    $DataJson = mMessageList();
  }
}

?>
