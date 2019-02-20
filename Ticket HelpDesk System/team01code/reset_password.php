<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'login-toolkit/login_setup.php';?>
    <?php include_once 'PHP-API/Localisation.php'; ?>
    <?php include_once 'PHP-Reusables/header.php'; ?>
    <title><?=l('Reset Password')?></title>
    <link href="./login-toolkit/login_style.css" rel="stylesheet">
  </head>
  <body>
    <?php $PAGE_TITLE = l("Reset Password"); ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>
    <!--Main box, containing form-->
    <div class="jumbotron">
      <form class="form-signin" action="reset_password.php" method="post">
      <h2 class="form-signin-heading"><?=l('Reset Password')?>'/h2>
      <label for="inputPassword" class="sr-only"><?= l('Password'); ?></label>
      <input type="password" id="inputPassword" class="form-control" placeholder="<?= l('Password'); ?>" name="password" required>
      <label for="inputPassword" class="sr-only"><?= l('Password'); ?></label>
      <input type="password" id="verifyPassword" class="form-control" placeholder="<?= l('Password'); ?>" name="verifyPassword" required>
      <br><button class="btn btn-lg btn-primary btn-block" type="submit" id="submitbutton"><?=l('Reset Password')?></button>
      </form>
    </div>
    <?php
      $inputUserid = $employee['employee_id'];
      if(isset($_POST['password']))
      {
        $new_pass = $_POST['password'];
        $g_type = \db\get_user_type($inputUserid);
        if ($u_type = \db\get_employee($inputUserid))
        {
          if ($g_type == \db\login_status::Operator)
          {
            \db\change_operator_password($inputUserid, $new_pass);
          }
          else if ($g_type == \db\login_status::Specialist)
          {
            \db\change_specialist_password($inputUserid, $new_pass);
          }
        }
        else
        {
          echo '<div class = "alert alert-danger" id="alert">';
          echo '<strong>User does not exist!</strong>';
          echo '</div>';
        }
      }
    ?>
    <script>
      $("input").keyup(function()
      {
        var password = $("#inputPassword").val();
        var verified_password = $("#verifyPassword").val();
        if (password.length >= 1 && verified_password.length >= 1)
        {
            checkPasswordMatch();
        }
      });
      $(document).ready(function(){$("#submitbutton").prop("disabled", true);});
      function checkPasswordMatch()
      {
        var password = $("#inputPassword").val();
        var verified_password = $("#verifyPassword").val();

        if (password != verified_password)
        {
            $("#inputPassword").css("box-shadow", "0px 0px 20px red");
            $("#verifyPassword").css("box-shadow", "0px 0px 20px red");
            $("#submitbutton").prop("disabled", true);
            return false;
        }
        else
        {
            $("#inputPassword").css("box-shadow", "0px 0px 20px green");
            $("#verifyPassword").css("box-shadow", "0px 0px 20px green");
            $("#submitbutton").prop("disabled", false);
            return true;
        }
      }
    </script>
  </body>
</html>
