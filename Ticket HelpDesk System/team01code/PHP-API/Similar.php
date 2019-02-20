<?php
  include_once '../login-toolkit/login_setup.php';
  if (isset($_GET["ticket_id"]) && isset($_GET["solution_id"]))
  {
    include '../PHP-DB/api.php';
    \db\assign_solution_to_ticket($_GET["solution_id"], $_GET["ticket_id"]);
    \db\set_ticket_status($_GET["ticket_id"], \db\status::Closed);
  }
  elseif(isset($_GET["open_ticket"]))
  {
    include '../PHP-DB/api.php';
    \db\set_ticket_status($_GET["open_ticket"], \db\status::Open);
  }
  elseif (isset($_GET["status_for_ticket"]))
  {
    include '../PHP-DB/api.php';
    $ticket = \db\get_ticket($_GET["status_for_ticket"]);
    echo json_encode(["status" => $ticket["status"]]);
  }
  elseif (isset($_GET["ticket_id"]))
  {
    include '../PHP-DB/api.php';
    $result = \db\get_solutions_for_similar($_GET["ticket_id"]);
    echo json_encode($result);
  }
  else header('HTTP/1.1 400 Bad Request');
?>
