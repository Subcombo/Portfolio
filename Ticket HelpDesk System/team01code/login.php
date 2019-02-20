<?php
  session_start();
  session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include 'PHP-Reusables/header.php'; ?>
    <title><?=l('Login')?></title>
    <link href="./login-toolkit/login_style.css" rel="stylesheet">
  </head>
  <body>
    <div class="language_box pull-right flip">
      <?= l('Change language:') ?></li>
      <?php
        $langs = get_available_languages();
        foreach ($langs as $lang) {
        	$text = is_language_rtl() && user_preferred_language() != $lang
        		? localised('language name', $lang) . ' ' . localised('flag', $lang)
        		:	localised('flag', $lang) . ' ' . localised('language name', $lang);
          if (user_preferred_language() != $lang)
          {
            echo '&emsp;';
            if (is_language_rtl()) echo '&lrm;';
            echo '<a href="?language=' . $lang . '">' . $text . '</a>';
            if (is_language_rtl()) echo '&rlm;';
          }
          else
          {
            echo '&emsp;' . $text;
          }
        }
      ?>
    </div>
    <!--Main box, containing form-->
    <div class="container">
      <form class="form-signin" action="./login.php" method="post">
        <h2 class="form-signin-heading">Make-It-All</h2>
        <?php
          $username = "";
          $password = "";
          if (isset($_POST['username']) && isset($_POST['password']))
          {
            $password = $_POST['password'];
            $username = $_POST['username'];

            if ($u_type = \db\validate_user_credentials($username, $password, $user_id))
            {
              session_start();
              $_SESSION['user'] = $username;
              $_SESSION['user_id'] = $user_id;
              // prevent session hijacking (not a huge safeguard, but beter this, than nothing)
              // also we wouldn't want to lock the user to a single host, they might be on their
              // phone, and switching between cellular and wifi e.g.
              $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
              $_SESSION['user_type'] = $u_type;
              \db\delete_user_preference($user_id, 'token');

              // set user language to the one chosen during login
              // if none was chosen, leave it, the localisation scripts
              // will automatically assume the language of the browser
              if (isset($_POST['language']))
              {
                \db\set_user_preference($user_id, 'language', $_POST['language']);
                $_SESSION['language'] = $_POST['language'];
              }
              else
              {
                $lang = \db\get_user_preference($user_id, 'language');
                if ($lang !== null)
                {
                  $_SESSION['language'] = $lang;
                }
              }

              $theme = \db\get_user_preference($user_id, 'theme');
              if ($theme === null) $_SESSION["theme"] = 'light';

              if ($u_type == \db\login_status::Operator)
              {
                $_SESSION['user_home'] = "home.php";
                header('Location: home.php');
              }
              else if ($u_type == \db\login_status::Specialist)
              {
                $_SESSION['user_home'] = "specialist_home.php";
                header('Location: specialist_home.php');
              }
              exit();
            }
            else
            {
              echo '<div class = "alert alert-danger" id="alert">';
              echo '<strong>Incorrect username or password</strong>';
              echo '</div>';
            }
          }
        ?>
        <label for="inputUsername" class="sr-only"><?= l('Username'); ?></label>
        <input type="text" id="inputUsername" class="form-control top" placeholder="<?= l('Username'); ?>" name="username" value="<?= $username ?>" required autofocus>
        <label for="inputPassword" class="sr-only"><?= l('Password'); ?></label>
        <input type="password" id="inputPassword" class="form-control bottom" placeholder="<?= l('Password'); ?>" name="password" required>
        <?php
          if (isset($_GET['language']))
          {
            echo '<input type="hidden" name="language" value="' . $_GET['language'] . '" />';
          }
        ?>
        <button class="btn btn-lg btn-primary btn-block" type="submit"><?= l('Sign in'); ?></button>
        <p>
          <a href="forgotten_pass.php?language=<?=user_preferred_language()?>" class="forgot_pass"><?= l('Forgot your password?'); ?></a>
        </p>
      </form>
    </div> <!-- /container -->
  </body>
</html>
