<?php
  header('Content-Type: text/html; charset=UTF-8');
  header('Cache-Control: no-cache, no-store, must-revalidate');
  header('Pragma: no-cache');
  header('Expires: 0');

  require_once 'php/database.php';
  require_once 'php/user.php';

  if (isset($_POST["username"]) && isset($_POST["password"])) {
    $db  = cDB::Create();
    $usr = cUser::Create();
 
    $usr->Logon(trim($_POST["username"]), $_POST["password"]);
    if (isset($_SESSION["error"]) && !empty($_SESSION["error"])) {
      $res = array(
	'message'=> $_SESSION["error"]
      );
      echo json_encode($res);	
      unset($_SESSION["error"]);
    } else {
      $res = array(
	'session_id'=> $_SESSION["session"]
      );
      echo json_encode($res);	
    }    
   }
?>