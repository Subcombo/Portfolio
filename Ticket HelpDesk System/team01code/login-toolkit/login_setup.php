<?php
  $user = null; $user_id = null; $user_agent = null;
  session_start();
  if (isset($_SESSION['user']))
  {
    $user = $_SESSION['user'];
    $user_id = $_SESSION['user_id'];
    $user_agent = $_SESSION['user_agent'];
    $user_type = $_SESSION['user_type'];
    $user_home = $_SESSION['user_home'];

    // For security regenerate ression ID (unless and AJAX request)
    if (!isset($headers['X-Requested-With']) || $headers['X-Requested-With'] != 'XMLHttpRequest')
      session_regenerate_id();
  }
  else
  {
    http_response_code(403); // Forbidden
    // Unless an AJAX request, redirect to the login page.
    if (!isset($headers['X-Requested-With']) || $headers['X-Requested-With'] != 'XMLHttpRequest')
      header('Location: login.php');;
  }
?>
