<?php
  include_once '../login-toolkit/login_setup.php';
  include '../PHP-DB/api.php';
  if (isset($_GET["ticket_id"]) && isset($_GET["send_to"]))
  {
    \db\assign_specialist_to_ticket($_GET["send_to"], $_GET["ticket_id"]);
    \db\set_ticket_status($_GET["ticket_id"], \db\status::Open);
  }
  elseif (isset($_GET["unbind_specialist_from_ticket_id"]))
  {
    \db\unbind_specialist_from_ticket(0 /* doesn't matter */, $_GET["unbind_specialist_from_ticket_id"]);
    \db\set_ticket_status($_GET["unbind_specialist_from_ticket_id"], \db\status::Open);
  }
  elseif (isset($_GET["assigned_specialist_for"]))
  {
    $id = \db\get_ticket($_GET["assigned_specialist_for"])["assigned_specialist"];
    if ($id != null)
    {
      $spec = \db\get_employee($id);
      $name = $spec["first_name"] . " " . $spec["last_name"];
      echo json_encode(["specialist_id" => $id, "name" => $name]);
    }
    else echo "{}";
  }
  elseif (isset($_GET["ticket_id"]))
  {
    $results = \db\get_specialist_suggestions_for_ticket($_GET["ticket_id"]);
    echo json_encode($results);
  }
  else header('HTTP/1.1 400 Bad Request');
?>
