<?php
require_once 'database.php';

const point_block = 500;
const money_block = 100;

/* class cItem */
class cItem {
private static $db;
private $sess;

public function __construct() {
  if (isset($_SESSION["session"]))
    $this->sess = $_SESSION["session"];
}

public function Conv($count) {
  if (!isset($count) || empty($count))
    return 0;

  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }
  
  $sql = "SELECT `POINTS` AS `RATE` FROM `PRIZES` WHERE `TYPE`=1 AND `COUNT`=(SELECT MAX(`COUNT`) FROM `PRIZES` WHERE `TYPE`=1);";
  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }

  $rat = $arr[0]['RATE'];

  if ($rat <= 0)
    $res = $count;
  else
    $res = $rat * $count;

  return $res;
}

protected function updDB($item_id, $count, $dec_pr = true) {
  if (!isset($item_id) || empty($item_id) || !isset($count) || empty($count) || !isset($this->sess) || empty($this->sess))
    return false;                 

  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  $sql = "SELECT `NAME`, `COUNT` FROM `PRIZES` WHERE `ID`={?};";
  $arr = $db->QuerySQL($sql, array($item_id));
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  $cnt = $arr[0]['COUNT'];
  if ($cnt <= 0) {
    $_SESSION["error"] = "No enough!";
    return false;
  }

  $_SESSION["name"] = $arr[0]['NAME'];

  if ($cnt >= $count)
    $cnt = $count;

  if (isset($dec_pr) && !empty($dec_pr) && ($dec_pr)) {                      
    $sql = "UPDATE `PRIZES` SET `COUNT`=`COUNT`-{?} WHERE `ID`={?};";
  
    if (!$db->ExecSQL($sql, array($cnt, $item_id))) {
      $_SESSION["error"] = "Internal server error!";
      return false;
    }
  }

  $sql = "INSERT INTO `STATUSES` (`USER_ID`, `PRIZE_ID`, `COUNT`) VALUES((SELECT `ID` FROM `USERS` WHERE `SESSION_ID`={?} LIMIT 1), {?}, {?});";

  if (!$db->ExecSQL($sql, array($this->sess, $item_id, $cnt))) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }
  
  $sql = "UPDATE `USERS` U SET `STATUS_ID`=(SELECT MAX(`ID`) FROM `STATUSES` S WHERE S.`USER_ID` = U.`ID`) WHERE `SESSION_ID`={?};";
  if (!$db->ExecSQL($sql, array($this->sess))) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }
  
  return true;
}
                                         
protected function getCount() {
  return 0;
}

final public function Session() {
  return $this->sess;
}

public function Get() {
  return false;
}

final public function isPresent() {
  if ($this->getCount())
    return true;

  return false;
}
}

/* class cPoint */
class cPoint extends cItem {
protected function getCount() {
  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }
  
  $sql = "SELECT MAX(`COUNT`) AS CNT FROM `PRIZES` WHERE `TYPE`=1;";
  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }

  $res = $arr[0]['CNT'];
  if ($res > 0)
    $res = point_block;

  return $res;
}

public function Get() {
  $cnt = $this->getCount();
  if ($cnt <= 0)
    return false;

  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }
  
  $sql = "SELECT `ID` FROM `PRIZES` WHERE `TYPE`=1 AND `COUNT`=(SELECT MAX(`COUNT`) FROM `PRIZES` WHERE `TYPE`=1);";

  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  $id = $arr[0]['ID'];

  $sum = mt_rand(1, $cnt);
  return $this->updDB($id, $sum, false);
}
}

/* class cThing */
class cThing extends cItem {
protected function getCount() {
  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }
  
  $sql = "SELECT COUNT(`COUNT`) AS CNT FROM `PRIZES` WHERE `TYPE`=2;";
  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }

  $res = $arr[0]['CNT'];

  return $res;
}

public function Get() {
  $cnt = $this->getCount();
  if ($cnt <= 0)
    return false;

  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }
  
  $sql = "SELECT MIN(`ID`) AS `MIN`, MAX(`ID`) AS `MAX` FROM `PRIZES` WHERE `TYPE`=2 AND `COUNT`>0;";

  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  $min = $arr[0]['MIN'];
  $max = $arr[0]['MAX'];

  $id = mt_rand($min, $max);

  $sql = "SELECT `ID` FROM `PRIZES` WHERE `TYPE`=2 AND `ID` BETWEEN {?} AND {?} AND `COUNT`>0 LIMIT 1;";

  $arr = $db->QuerySQL($sql, array($id, $max));
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  return $this->updDB($id, 1);
}
}

/* class cMoney */
class cMoney extends cItem {
protected function getCount() {
  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }
  
  $sql = "SELECT MAX(`COUNT`) AS CNT FROM `PRIZES` WHERE `TYPE`=3;";
  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return 0;
  }

  $res = $arr[0]['CNT'];
  if ($res > money_block)
    $res = money_block;

  return $res;
}

public function Get() {
  $cnt = $this->getCount();
  if ($cnt <= 0)
    return false;

  $db = cDB::Create();
  if (!$db->Connected()) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  $sql = "SELECT `ID` FROM `PRIZES` WHERE `TYPE`=3 AND `COUNT`=(SELECT MAX(`COUNT`) FROM `PRIZES` WHERE `TYPE`=3);";

  $arr = $db->QuerySQL($sql, array());
  if (!isset($arr) || empty($arr)) {
    $_SESSION["error"] = "Internal server error!";
    return false;
  }

  $id = $arr[0]['ID'];

  $sum = mt_rand(1, $cnt); 
  return $this->updDB($id, $sum);
}
}
?>