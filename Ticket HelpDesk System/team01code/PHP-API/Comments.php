<?php
  include_once '../login-toolkit/login_setup.php';
  include_once '../PHP-DB/api.php';
  include_once '../PHP-API/Localisation.php';
  if (isset($_GET["ticket_id"]))
  {
    $comments = \db\comments_on_problem($_GET["ticket_id"]);
    foreach ($comments as &$comment)
    {
      $comment["comment_timestamp"] = ldt($comment["comment_timestamp"]);
    }
    echo json_encode($comments);
  }
  elseif (isset($_POST["ticket_id"])
    && isset($_POST["comment_content"]))
  {
    \db\create_comment($_POST["ticket_id"], new DateTime(), $_POST["comment_content"], $_SESSION["user_id"]);
  }
  else http_response_code(500);
?>
