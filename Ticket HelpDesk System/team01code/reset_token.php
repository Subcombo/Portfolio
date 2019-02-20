<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'PHP-API/Localisation.php'; ?>
    <?php include_once 'PHP-Reusables/header.php'; ?>
    <title><?=l('Reset Password')?></title>
    <?php
      if (!isset($_GET['user_id'], $_GET['token_id'])
        && !isset($_POST['user_id'], $_POST['token_id']))
      {
        http_response_code(400);
        header("Location: login.php");
        exit();
      }
    ?>
    <link href="./login-toolkit/login_style.css" rel="stylesheet">
  </head>
  <body>
    <!--Main box, containing form-->
    <?php
      if ($_SERVER['REQUEST_METHOD'] === "GET")
      {
        $get_token_ID = $_GET['token_id'];
        $get_user_ID = \db\get_user_preference_ID($get_token_ID);
        $inputUserid = $get_user_ID['user_id'];
        if ($inputUserid === $_GET['user_id'])
        {
          header("Location: login.php");
          exit();
        }
      }
      elseif ($_SERVER['REQUEST_METHOD'] === "POST")
      {
        $get_token_ID = $_POST['token_id'];
        $get_user_ID = \db\get_user_preference_ID($get_token_ID);
        $inputUserid = $get_user_ID['user_id'];
        if ($inputUserid === $_GET['user_id'])
        {
          header("Location: login.php");
          exit();
        }
        if(isset($_POST['password']))
        {
          $new_pass = $_POST['password'];
          $g_type = \db\get_user_type($inputUserid);
          if ($u_type = \db\get_employee($inputUserid))
          {
            if ($g_type == \db\login_status::Operator)
            {
              \db\change_operator_password($inputUserid, $new_pass);
              \db\delete_user_preference($inputUserid, 'token');
            }
            else if ($g_type == \db\login_status::Specialist)
            {
              \db\change_specialist_password($inputUserid, $new_pass);
              \db\delete_user_preference($inputUserid, 'token');
            }
            header("Location: login.php");
            exit();
          }
        }
      }
      else
      {
        header("Location: login.php");
        exit();
      }
    ?>
    <div class="jumbotron">
      <form class="form-signin" action="reset_token.php" method="post">
        <h2 class="form-signin-heading"><?=l('Reset Password')?></h2>
        <div id="both_text_boxes">
          <label for="inputPassword" class="sr-only"><?= l('Password'); ?></label>
          <input type="password" id="inputPassword" class="form-control top" placeholder="<?= l('Password'); ?>" name="password" required autofocus>
          <label for="verifyPassword" class="sr-only"><?= l('Verify Password'); ?></label>
          <input type="password" id="verifyPassword" class="form-control bottom" placeholder="<?= l('Verify Password'); ?>" name="verifyPassword" required>
          <input type="hidden" name="token_id" value="<?=$_GET["token_id"]?>"/>
          <input type="hidden" name="user_id" value="<?=$_GET["user_id"]?>"/>
        </div>
        <br><button class="btn btn-lg btn-primary btn-block" type="submit" id="submitbutton"><?=l('Reset Password')?></button>
      </form>
    </div>
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
            $("#both_text_boxes").css("box-shadow", " 0px 0px 5px red");
            $("#submitbutton").prop("disabled", true);
            return false;
        }
        else
        {
            $("#both_text_boxes").css("box-shadow", " 0px 0px 5px green");
            $("#submitbutton").prop("disabled", false);
            return true;
        }
      }
    </script>
  </body>
</html>
