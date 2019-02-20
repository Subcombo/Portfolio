<?php
  include_once dirname(__FILE__, 2) . '/login-toolkit/login_setup.php';
  include_once dirname(__FILE__, 2) . '/PHP-API/Localisation.php';
  include_once dirname(__FILE__, 2) . '/PHP-DB/api.php';
  if ($user_id === null) {die('User not set! include login_setup.php!');}
  $employee = \db\get_employee($user_id);
  $name = htmlspecialchars($employee['first_name']) . ' ' . htmlspecialchars($employee['last_name']);
?>
<script type="text/javascript">
function set_preference(name, value, callback)
{
  var data = {};
  data[name] = value;
  $.ajax({
    'method' : 'POST',
    'url' : 'PHP-API/SetPreference.php',
    'data' : data,
    'success' : function(response)
    {
      callback();
    }
  });
}

function set_language(language)
{
  set_preference('language', language, function()
  {
    window.location.reload();
  });
}

function set_theme(theme)
{
  set_preference('theme', theme, function()
  {
    window.location.reload();
  });
}

function toggle_theme()
{
  var new_theme = "<?= $_SESSION["theme"] == 'dark' ? 'light' : 'dark' ?>";
  set_theme(new_theme);
}

$( document ).ready(function()
{
  $(".accessibility_mode").click(function()
  {
    toggle_theme();
  });
});
</script>
<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar">
        <span class="sr-only"><?=l('Toggle navigation')?></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand hidden-xs" href="<?=$user_home?>">Make-It-All</a>
      <a class="navbar-brand visible-xs" href="<?=$user_home?>">M-I-A</a>
      <p class="navbar-text visible-xs">
        <?php
          if (basename($_SERVER['PHP_SELF']) !== $user_home) echo "<a href=\"{$user_home}\">" . l('Home') . "</a> / ";
          if (isset($PAGE_TITLE)) echo $PAGE_TITLE;
        ?>
      </p>
    </div>
    <div class="collapse navbar-collapse" id="main-navbar">
      <p class="navbar-text hidden-xs">
        <?php
          if (basename($_SERVER['PHP_SELF']) !== $user_home) echo "<a href=\"{$user_home}\">" . l('Home') . "</a> / ";
          if (isset($PAGE_TITLE)) echo $PAGE_TITLE;
        ?>
      </p>
      <div class="navbar-<?=is_language_rtl() ? 'left' : 'right'?>">
        <p class="navbar-text"><?=l('Logged in as')?> <b><?= $name ?></b> (<?= ($user_type === \db\login_status::Operator ? l('Operator') : l('Specialist')) ?>)</p>
        <div class="btn-group navbar-btn hidden-xs">
          <a href="login-toolkit/logout.php" type="button" class="btn btn-default"><span class="glyphicon glyphicon-log-out"></span> <?=l('Log out')?></a>
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            <span class="caret"></span>
            <span class="sr-only"><?=l('Toggle Dropdown')?></span>
          </button>
          <ul class="dropdown-menu" role="menu">
            <?php
              $langs = get_available_languages();
              foreach ($langs as $lang) {
                if (user_preferred_language() != $lang)
                  echo '<li><a href="javascript:set_language(\'' . $lang . '\');">' . localised('flag', $lang) . '&nbsp;' . localised('language name', $lang) . '</a></li>';
                else
                  echo '<li class="disabled"><a href="#">' . localised('flag', $lang) . ' ' . localised('language name', $lang) . '</a></li>';
              }
            ?>
            <li role="separator" class="divider"></li>
            <li><a href="#" class="accessibility_mode"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;<?=l('Toggle accessibility mode')?></a></li>
            <li><a href="reset_password.php"><span class="glyphicon glyphicon-cog"></span>&nbsp;<?=l('Reset Password')?></a></li>
          </ul>
        </div>
        <!-- For mobile devices -->
        <?php
          $langs = get_available_languages();
          foreach ($langs as $lang) {
            if (user_preferred_language() != $lang)
              echo '<div class="navbar-text visible-xs"><a href="javascript:set_language(\'' . $lang . '\');">' . localised('flag', $lang) . '&nbsp;' . localised('language name', $lang) . '</a></li>';
            else
              echo '<div class="navbar-text visible-xs"><span>' . localised('flag', $lang) . ' ' . localised('language name', $lang) . '</span></div>';
          }
        ?>
        <div role="separator" class="divider"></div>
        <div class="navbar-text visible-xs"><a href="#" class="accessibility_mode"><span class="glyphicon glyphicon-eye-open"></span>&nbsp;<?=l('Toggle accessibility mode')?></a></div>
        <div class="navbar-text visible-xs"><a href="reset_password.php"><span class="glyphicon glyphicon-cog"></span>&nbsp;<?=l('Reset Password')?></a></div>
        <div class="navbar-text visible-xs"><a href="login-toolkit/logout.php" type="button" class="btn btn-default"><span class="glyphicon glyphicon-log-out"></span> <?=l('Log out')?></a></div>
      </div>
    </div>
  </div>
</nav>
