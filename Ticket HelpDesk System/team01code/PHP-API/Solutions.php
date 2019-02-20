<?php
  include_once '../login-toolkit/login_setup.php';
  include_once '../PHP-API/Localisation.php';
  include_once '../PHP-DB/api.php';

  if (isset($_GET["ticket_id"]))
  {
    $solutions = \db\get_solutions_for_problem($_GET["ticket_id"]);
    foreach ($solutions as &$solution) {
      $solution["timestamp"] = ldt($solution["timestamp"]);
    }
    echo json_encode($solutions);
  }
  elseif (isset($_POST["solution"]) && isset($_POST["ticket_id"]))
  {
    $sid = \db\create_solution($_POST["ticket_id"], $user_id, $_POST["solution"], new DateTime());
    $ticket = \db\get_ticket($_POST["ticket_id"]);
    \db\assign_specialist_to_tag($ticket["problem_tag_id"], $user_id);
    echo json_encode([ "solution_id" => $sid ]);
  }
  elseif (isset($_POST["ticket_id"]) && isset($_POST["set_status"]))
  {
    \db\set_ticket_status($_POST["ticket_id"], $_POST["set_status"]);
  }
  else http_response_code(400);
?>
