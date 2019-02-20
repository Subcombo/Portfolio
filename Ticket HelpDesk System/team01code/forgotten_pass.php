<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'PHP-API/Localisation.php'; ?>
    <?php include_once 'PHP-Reusables/header.php'; ?>
    <?php $language = isset($_GET["language"]) ? $_GET["language"] : (isset($_POST["language"]) ? $_POST["language"] : browser_preferred_language()); ?>
    <title><?=lx('Reset Password', $language)?></title>
    <link href="./login-toolkit/login_style.css" rel="stylesheet">
  </head>
  <body>
    <div class="language_box pull-right flip">
      <!-- sets the language localisation -->
      <?= lx('Change language:', $language) ?></li>
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
        $lang = $language;
      ?>
    </div>
    <!--Main box, containing form-->
    <div class="jumbotron">
      <form class="form-signin" action="forgotten_pass.php" method="post">
      <h2 class="form-signin-heading"><?=lx('Reset Password', $lang)?></h2>
        <!-- connects to the gmail account using swiftmailer, and it then generates a random ticket (when the user presses the submit button)
        gets the user ID provided in the text box by the person who wants to recover the password, finds the employee using the get_employee function
        then it sends an email to that specific employee with a reset password link
        Done by Eduardas Verba -->
        <?php
        require_once 'lib/swift_required.php';
        $transport = \Swift_SmtpTransport::newInstance('smtp.gmail.com',465,'ssl')->setUsername('Team01Lboro@gmail.com')->setPassword('FirstPlace');

        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $new_token = '';
        $charactersLength = strlen($characters) - 1;
        for ($i = 0; $i < 20; $i++)
        {
            $n = rand(0, $charactersLength);
            $new_token .= $characters[$n];
        }
        $inputUserid = '';
        if(isset($_POST['userid']))
        {
          $inputUserid = $_POST['userid'];
          if ($u_type = \db\get_employee($inputUserid))
          {
            $hash_token = password_hash($new_token, PASSWORD_DEFAULT);
            \db\set_user_preference($inputUserid, 'token', $new_token);
            $token_user_id = \db\get_employee($inputUserid);
            $employee_email = $token_user_id['email'];
            $employee_name = $token_user_id['first_name'] . ' ' . $token_user_id['last_name'];
            $send_to = $token_user_id['email'] . ' ' . $token_user_id['first_name'] . ' ' . $token_user_id['last_name'];

            $body_message = lx('Hello ', $lang) . $employee_name . lx(",", $lang) . "\r\n\r\n"
              . lx('click this link to reset your password:', $lang)
              . "\r\nhttp://team01.sci-project.lboro.ac.uk/reset_token.php?user_id=" . $inputUserid . '&token_id=' . $new_token
              . "\r\n\r\n" . lx('Best regards,', $lang) . "\r\n" . lx('Make-It-All Team', $lang);

            $mailer = \Swift_Mailer::newInstance($transport);
            $message = \Swift_Message::newInstance(lx('Your Reset Password Link', $lang))
              ->setFrom(array('team01lboro@gmail.com' => 'Team Number One'))
              ->setTo(array($employee_email => $employee_name))
              ->setBody($body_message);

              $result = $mailer->send($message);
          }
          header("Location: login.php?language=" . $lang);
        }
        ?>
        <label for="inputUserID" class="sr-only"></label>
        <input type="text" id="inputUserID" class="form-control" placeholder="<?=lx('User ID', $lang)?>" name="userid" value="<?= $inputUserid ?>" required autofocus>
        <?php
          if (isset($_GET['language']))
          {
            echo '<input type="hidden" name="language" value="' . $_GET['language'] . '" />';
          }
        ?>
        <br><button class="btn btn-lg btn-primary btn-block" type="submit"><?=lx('Send email link', $lang)?></button>
      </form>

    </div>
  </body>
</html>
