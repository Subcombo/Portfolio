<!DOCTYPE html>
<html>
  <head>
    <title>Logout</title>
    <?php
      session_start();
      session_destroy();
      header('Location: ../login.php');
    ?>
  </head>
  <body>
  </body>
</html>
