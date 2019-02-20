<?php include_once dirname(__FILE__, 2) . '/PHP-API/Localisation.php'; ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

<!-- Bootstrap -->
<?php if($_SESSION["theme"] == 'dark') { ?>
  <link href="bootstrap-dark/css/bootstrap.min.css" rel="stylesheet" id="theme">
<?php } else { ?>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" id="theme">
<?php } ?>
<link href="fontawesome/css/font-awesome.min.css" rel="stylesheet">
<link href="bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
<script src="jquery/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="bootstrap-typeahead/bootstrap3-typeahead.min.js"></script>
<script src="bootstrap-validator/js/validator.min.js"></script>
<script src="jquery-textcomplete/jquery.textcomplete.min.js"></script>
<script src='autosize/autosize.min.js'></script>

<?php if (is_language_rtl()) {echo '<link href="bootstrap/css/bootstrap-rtl.min.css" rel="stylesheet">';} ?>

<style type="text/css">
  .icon-danger { color: #CC0000; }
  .icon-warning { color: #ffbb33; }
  .icon-success { color: #007E33; }
</style>
