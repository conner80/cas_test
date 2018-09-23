<?php
class cDB {
private static $db; 
private $conn; 
private $para = "{?}"; 
private $flag = true;

public static function Create() {
  if (!isset(self::$db) || is_null(self::$db))
    self::$db = new cDB();

  return self::$db; 
}

private function __construct() {
  $this->conn = new mysqli("localhost", "cas", "1", "cas");
  if ($this->conn->error) {
    $this->flag = false;
  } else {
    $this->conn->query("SET lc_time_names = 'ru_RU'");
    $this->conn->query("SET NAMES 'utf8'");
  }
}

public function __destruct() {
  if ($this->conn)
    $this->conn->close();

  unset($this->conn); 
}

private function PrepareQuery($sql, $params) {
  if ($params) {
    for ($i = 0; $i < count($params); $i++) {
      $p = strpos($sql, $this->para);
      $arg = "'".$this->conn->real_escape_string($params[$i])."'";
      $sql = substr_replace($sql, $arg, $p, strlen($this->para)); 
    }
  }

  return $sql;
}

public function ExecSQL($sql, $params = false) {
  if (!$this->flag)
    return false;

  $res = $this->conn->query($this->PrepareQuery($sql, $params));

  if ($res) {
    if ($this->conn->insert_id === 0)
      return true;               
    else
      return $this->conn->insert_id; 
  } else
    return false;
}

private function DataSetToArray($dataset) {
  $arr = array();
  while (($row = $dataset->fetch_assoc()) != false) {
    $arr[] = $row;
  }

  return $arr;
}

public function QuerySQL($sql, $params = false, $show_query = false) {
  if (!$this->flag) 
    return false;

  if (isset($show_query) && ($show_query))
    echo $this->PrepareQuery($sql, $params)."\n";

  $query = $this->conn->query($this->PrepareQuery($sql, $params));

  if (!$query)
    return false;

  return $this->DataSetToArray($query);
}

public function GetError() {
  if (isset($this->conn))
    return $this->conn->error;

  return null;
}

public function Connected() {
  return $this->flag;
}
}
?>