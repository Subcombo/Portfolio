<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'login-toolkit/login_setup.php'; ?>
    <?php include_once 'PHP-API/Localisation.php'; ?>
    <?php include_once 'PHP-Reusables/header.php'; ?>
    <title><?=l('Ticket')?></title>
    <?php
      if (!isset($_GET["ticket_id"]))
      {
        http_response_code(400);
        die("ticket_id parameter mandatory and has to be valid");
      }
    ?>
  </head>
  <body>
    <?php
      $PAGE_TITLE = l('Viewing Ticket') . " #" . $_GET["ticket_id"];
    ?>

    <?php include 'PHP-Reusables/navbar.php'; ?>

    <div class="container-fluid">
      <?php
        if ($_GET["ticket_id"] === null || ($ticket_details = \db\get_ticket($_GET["ticket_id"])) === null)
        {
          die("Ticket doesn't exist");
        }
        $HIDE_DELETE_BUTTON = true;
        $this_ticket = $_GET["ticket_id"];
        include 'call_elements/view_ticket.php';
      ?>
    </div>

    <?php include 'call_elements/equip_spec_modals.php' ?>

  	<script>
      $('textarea').textcomplete([{
        match: /(^|\s|\\b)#(\w*)$/,
        search: function (term, callback) {
          var words = [term, 'desperatecaller', 'shreddedpaper', 'urgent', 'mouse', 'logitech'];
          if (term.length >= 2 && words.indexOf(term) == -1) words.unshift(term);
          callback($.map(words, function (word) {
            return word.toLowerCase().indexOf(term.toLowerCase()) != -1 ? word : null;
          }));
        },
        replace: function (word) {
          return '$1#' + word + ' ';
        },
        template: function (value, term) {
          var termPos = value.toLowerCase().indexOf(term.toLowerCase());
          return '#' + value.substring(0, termPos) + '<u>' + term + '</u>' + value.substr(termPos + term.length);
        }
      }]);
  	</script>
  </body>
</html>
