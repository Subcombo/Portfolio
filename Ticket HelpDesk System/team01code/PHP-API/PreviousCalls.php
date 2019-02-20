<?php
  include_once '../login-toolkit/login_setup.php';
  include_once '../PHP-API/Localisation.php';
  include_once '../PHP-DB/api.php';

  if (isset($_GET["ticket_id"]))
  {
    $calls = \db\get_calls_for_ticket($_GET["ticket_id"]);
    foreach ($calls as &$call) {
      $call["timestamp"] = ldt($call["timestamp"]);
    }
    echo json_encode($calls);
  }
  else http_response_code(500);
?>
