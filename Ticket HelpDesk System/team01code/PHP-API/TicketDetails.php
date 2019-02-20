<?php
  include '../login-toolkit/login_setup.php';
  include '../PHP-DB/api.php';
  if (isset($_GET["ticket_id"]))
  {
    $ticket = \db\get_ticket($_GET["ticket_id"]);
    echo json_encode($ticket);
  }
  elseif (isset($_POST["ticket_id"])
    && isset($_POST["notes"])
    && isset($_POST["parent_tag_id"]))
  {
    $tag_id = intval($_POST["parent_tag_id"]);
    if (isset($_POST["new_tag"]) && trim($_POST["new_tag"]) != "")
    {
      $tag_id = \db\create_problem_tag($_POST["new_tag"], $tag_id);
    }
    if ($tag_id === null) $tag_id = $_POST["parent_tag_id"];
    \db\amend_ticket($_POST["ticket_id"], null, $_POST["notes"], $tag_id, null, null);
  }
  elseif (isset($_POST["ticket_id"])
    && isset($_POST["set_priority"]))
  {
    \db\amend_ticket($_POST["ticket_id"], $_POST["set_priority"], null, null, null, null);
  }
  elseif (isset($_GET["priority_for_ticket"]))
  {
    $ticket = \db\get_ticket($_GET["priority_for_ticket"]);
    echo json_encode([ "priority" => $ticket["priority"] ]);
  }
  else http_response_code(500);
?>
