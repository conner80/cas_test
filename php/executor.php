<?php
require_once 'database.php';
require_once 'user.php';
require_once 'items.php';

const weight_money = 20;
const weight_thing = 35;
const weight_point = 45;
const rate = 10;

class cExec {
private static $exe;
private static $db;
private static $usr;

public static function Create() {
  if (!isset(self::$exe) || is_null(self::$exe)) {
    self::$exe = new cExec();
  }

  return self::$exe;
}

public function Logout() {
  $db  = cDB::Create();
  $usr = cUser::Create();

  $usr->Logout();
  $this->SendError("logout");
}

private function random_sel($types, $weights) {
  $tot = array_sum($weights);
  $i = 0;
  $n = mt_rand(1, $tot);
  foreach ($types as $j => $typ) {
    $i += $weights[$j];
    if ($i >= $n)
      return $types[$j];
  }
}

private function SendError($msg) {
  if (!isset($msg) || (empty($msg)))
    return;

  if (isset($_SESSION["type"]))
    unset($_SESSION["type"]);
  if (isset($_SESSION["count"]))
    unset($_SESSION["count"]);
  if (isset($_SESSION["name"]))
    unset($_SESSION["name"]);
  $_SESSION["error"] = $msg; 
  $res = array(
    'error' => $msg
  );

  echo json_encode($res);
}

private function SendPrize($type, $count, $name) {
  if (!isset($type) || empty($type) || !isset($count) || empty($count) || !isset($name) || empty($name))  
    return;

  if (isset($_SESSION["error"]))
    unset($_SESSION["error"]);
  $_SESSION["type"] = $type;
  $_SESSION["count"] = $count; 
  $_SESSION["name"] = $name; 
  $res = array(
    'type' => $type,
    'count' => $count,
    'name' => $name
  );

  echo json_encode($res);
}

public function Run() {
  $usr = cUser::Create();

/*  if (!$usr->CheckSession()) {
    $this->Logout();
    return;
  } */
  
  $pt = new cPoint();
  $mon = new cMoney();
  $th = new cThing();

  $typs = array();
  $wes = array(); 
  $sess = $pt->Session();

  if ($pt->isPresent()) {
    $wes[] = weight_point;
    $typs[] = 1;
  };
 
  if ($th->isPresent()) {
    $wes[] = weight_thing;
    $typs[] = 2;
  };
 
  if ($mon->isPresent()) {
    $wes[] = weight_money;
    $typs[] = 3;  
  };

  if (empty($wes)) {
    $this->SendError("No prizes!");
    return;
  };
  
  $typ = $this->random_sel($typs, $wes);
  switch ($typ) {
    case 1:
      $flg = $pt->Get();
      break;
    case 2:
      $flg = $th->Get();
      break;
    case 3:
      $flg = $mon->Get();
      break;
    default:
      $flg = false;  
  };

  unset($pt);
  unset($mon);
  unset($th); 

  if (!$flg) {
    $this->SendError("Can't get a prize!");
    return;
  };

  $db = cDB::Create();
  if (!$db->Connected()) {
    $this->SendError("Internal server error!");
    return;
  }
  
  $sql = "SELECT S.`COUNT` AS CNT, P.`NAME` FROM `USERS` U, `PRIZES` P, `STATUSES` S WHERE U.`SESSION_ID`={?} AND S.`ID`=U.`STATUS_ID` AND S.`PRIZE_ID`=P.`ID` LIMIT 1;";
  $arr = $db->QuerySQL($sql, array($sess));
  if (!isset($arr) || empty($arr)) {
    $this->SendError("Internal server error!");
    return;
  }

  $cnt = $arr[0]['CNT'];
  $nam = $arr[0]['NAME'];
  $this->SendPrize($typ, $cnt, $nam);
}

public function CancelPrize() {
  $db  = cDB::Create();
  $usr = cUser::Create();

/*  if (!$usr->CheckSession()) {
    $this->Logout();
    return;
  }*/
  
  $sess = $_SESSION["session"];
  $sql = "UPDATE `STATUSES` S, `USERS` U, `PRIZES` P SET S.`STATUS`=4, U.`STATUS_ID`=NULL, P.`COUNT`=P.`COUNT`+S.`COUNT` WHERE U.`SESSION_ID`={?} AND S.`ID`=U.`STATUS_ID` AND S.`PRIZE_ID`=P.`ID` AND S.`STATUS`=1;";
  if (!$db->ExecSQL($sql, array($sess))) {
    $this->SendError("Internal server error!");
    return;
  }
}

public function GetPrize() {
  if (!isset($_SESSION["choose"]) || empty($_SESSION["choose"]) || !isset($_SESSION["session"]) || empty($_SESSION["session"])) {
    $this->SendError("You MUST choose"); 
    return;
  }

  $sel = $_SESSION["choose"];
  $sess = $_SESSION["session"];

  if ($sel != 1 && $sel != 2) {
    $this->SendError("Incorrect 'choose' param!");
    return;
  }

  $db  = cDB::Create();
  $usr = cUser::Create();

/*  if (!$usr->CheckSession()) {
    $this->Logout();
    return;
  }*/
  
  $sql = "SELECT S.`ID`, S.`COUNT`, P.`TYPE`, (P.`POINTS` * S.`COUNT`) AS C_COUNT FROM `USERS` U, `STATUSES` S, `PRIZES` P WHERE U.`SESSION_ID`={?} AND U.`STATUS_ID`=S.`ID` AND S.`STATUS`=1 AND S.`PRIZE_ID`=P.`ID` LIMIT 1;";
  $arr = $db->QuerySQL($sql, array($sess));
  if (!isset($arr) || empty($arr)) {
    $this->SendError("Internal server error!");
    return;
  }

  $cnt = $arr[0]['COUNT'];
  $ccnt = $arr[0]['C_COUNT'];
  $typ = $arr[0]['TYPE'];
  $id = $arr[0]['ID'];
  
  if ($sel == 2 && $ccnt != 0)
    $cnt = $ccnt;
                  
  if ($typ == 1) {
    $sql = "UPDATE `STATUSES` S, `USERS` U SET S.`STATUS`=3, U.`POINTS`=U.`POINTS`+S.`COUNT` WHERE S.`ID`={?} AND S.`ID`=U.`STATUS_ID`;";
    if (!$db->ExecSQL($sql, array($id))) {
      $this->SendError("Internal server error!");
      return;
    }
  } 
  else {
    if ($typ == 3 && $sel == 2) {
      $sql = "UPDATE `STATUSES` S, `USERS` U SET S.`STATUS`=3, U.`POINTS`=U.`POINTS`+{?} WHERE S.`ID`={?} AND S.`ID`=U.`STATUS_ID`;";
      if (!$db->ExecSQL($sql, array($cnt, $id))) {
        $this->SendError("Internal server error!");
        return;
      }
   } else {
      $sql = "UPDATE `STATUSES` S SET S.`STATUS`=2 WHERE S.`ID`={?};";
      if (!$db->ExecSQL($sql, array($id))) {
        $this->SendError("Internal server error!");
        return;
      }
   }
  }
}
}
?>