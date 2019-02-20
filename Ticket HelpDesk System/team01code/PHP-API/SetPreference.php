<?php
  include_once '../login-toolkit/login_setup.php';
  include_once '../PHP-DB/api.php';
  include_once '../PHP-API/Localisation.php';
  if (isset($_POST["language"]))
  {
    \db\set_user_preference($_SESSION['user_id'], 'language', $_POST['language']);
    $_SESSION['language'] = $_POST['language'];
  }
  elseif (isset($_POST["theme"]))
  {
    \db\set_user_preference($_SESSION['user_id'], 'theme', $_POST['theme']);
    $_SESSION['theme'] = $_POST['theme'];
  }
  else http_response_code(400);
?>
