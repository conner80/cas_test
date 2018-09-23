<?php
  header('Content-Type: text/html; charset=UTF-8');
  header('Cache-Control: no-cache, no-store, must-revalidate');
  header('Pragma: no-cache');
  header('Expires: 0');

  require_once 'php/executor.php';

  if (isset($_POST["session_id"]) && isset($_POST["action"])) {
    $exe = cExec::Create();

    if (!empty($_POST["action"])) {
      $act = $_POST["action"];
      $_SESSION["session"] = $_POST["session_id"];
      if ($act == 'run') {
        $exe->Run();
      } elseif ($act == 'get') {
        if (isset($_POST["choose"]) && !empty($_POST["choose"]))
          $_SESSION["choose"] = $_POST["choose"]; 
        $exe->GetPrize();
      } elseif ($act == 'cancel') {
        $exe->CancelPrize();
      } elseif ($act == 'logout') {
        $exec->Logout();
      }
    }
  }
?>