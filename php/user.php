<?php
require_once 'database.php';

class cUser {
private static $usr;

public static function Create() {
  if (!isset(self::$usr) || is_null(self::$usr)) {
    self::$usr = new cUser();
  }

  return self::$usr; 
}

public function Logon($user, $pass) {
  if (!isset($user) || !isset($pass))
    return;

  if (empty($user) || empty($pass))
    return;

  $this->Logout();

  $db = cDB::Create();

  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return;
  }

  $sql = "SELECT COUNT(1) AS CNT FROM `USERS` WHERE `NAME`={?} AND `PASS`=MD5({?});";
  $arr = $db->QuerySQL($sql, array($user, $user.$pass));
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Incorrect user and/or password!";
    return;
  }

  if ($arr[0]['CNT'] != 1) {
    $_SESSION["error"] = "Incorrect user and/or password!";
    return;
  }

  if (session_status() != PHP_SESSION_DISABLED && session_status() != PHP_SESSION_ACTIVE)
    session_start();
           
  $_SESSION["session"] = md5($user.getenv("REMOTE_ADDR"));
  $_SESSION["username"] = $user;

  $sql = "UPDATE `USERS` SET `SESSION_ID`={?}, `ACTIVITY`=NOW(), `ADDR`={?} WHERE `NAME`={?};";
  if (!$db->ExecSQL($sql, array($_SESSION["session"], getenv("REMOTE_ADDR"), $user))) {
    $e = $db->GetError();
    $this->Logout();
    if (is_null($e)) {
      $_SESSION["error"] = htmlspecialchars("Internal error!");
    } else {
      $_SESSION["error"] = htmlspecialchars("$e");
    } 
  }
}
 
public function Logout() {
  $db = cDB::Create();

  if (isset($_SESSION["username"]) && $db->Connected()) {
    $sql = "BEGIN UPDATE `USERS` SET `SESSION_ID`=NULL, `ACTIVITY`=NULL, `ADDR`=NULL WHERE `NAME`={?};";
    $db->ExecSQL($sql, array($_SESSION["username"]));
  } elseif (isset($_SESSION["session"]) && $db->Connected()) {
    $sql = "BEGIN UPDATE `USERS` SET `SESSION_ID`=NULL, `ACTIVITY`=NULL, `ADDR`=NULL WHERE `SESSION_ID`={?};";
    $db->ExecSQL($sql, array($_SESSION["session"]));
  }

  if (session_status() != PHP_SESSION_DISABLED && session_status() == PHP_SESSION_ACTIVE) {
    $_SESSION = array();
    session_destroy(); 
  }
}                            

public function CheckUser() {
  if (!isset($_SESSION["username"]))
    return false;

  $db = cDB::Create(); 
  if (!$db->Connected())
    return false;

  $id = md5($_SESSION["username"].getenv("REMOTE_ADDR"));
  $sql = "SELECT COUNT(1) AS CNT FROM `USERS` WHERE `NAME`={?} AND `ADDR`={?} AND `SESSION_ID`={?} AND TIMESTAMPDIFF(MINUTE, `ACTIVITY`, NOW()) <= 5;";
  $arr = $db->QuerySQL($sql, array($_SESSION["username"], getenv("REMOTE_ADDR"), $id));
  if (!isset($arr) || empty($arr) || $arr[0]['CNT'] != 1) {
    $this->Logout();
    return false;
  }

  return true;
}

public function CheckSession() {
  if (!isset($_SESSION["session"]))
    return false;

  $db = cDB::Create(); 
  if (!$db->Connected())
    return false;

  $id = $_SESSION["session"];
  $sql = "SELECT COUNT(1) AS CNT FROM `USERS` WHERE `ADDR`={?} AND `SESSION_ID`={?} AND TIMESTAMPDIFF(MINUTE, `ACTIVITY`, NOW()) <= 5;";
  $arr = $db->QuerySQL($sql, array($id, getenv("REMOTE_ADDR"), $id));
  if (!isset($arr) || empty($arr) || $arr[0]['CNT'] != 1) {
    $this->Logout();
    return false;
  }

  return true;
}

public function GetPoints() {
  $res = 0;
  if (isset($_SESSION["username"])) {
    if ($this->CheckUser()) {
      $db = cDB::Create();
      if ($db->Connected()) {
        $sql = "SELECT SUM(`POINTS`) AS CNT FROM `USERS` WHERE `NAME`={?};";
        $arr = $db->QuerySQL($sql, array($_SESSION["username"]));
        if (isset($arr) && !empty($arr)) {
          $res = $arr[0]['CNT'];
        }
      }
    }
  } elseif (isset($_SESSION["session"])) {
    if ($this->CheckUser()) {
      $db = cDB::Create();
      if ($db->Connected()) {
        $sql = "SELECT SUM(`POINTS`) AS CNT FROM `USERS` WHERE `SESSION_ID`={?};";
        $arr = $db->QuerySQL($sql, array($_SESSION["session"]));
        if (isset($arr) && !empty($arr)) {
          $res = $arr[0]['CNT'];
        }
      }
    }
  }
                                                      
  $_SESSION["balance"] = $res;
  return $res;
}
}             
?>