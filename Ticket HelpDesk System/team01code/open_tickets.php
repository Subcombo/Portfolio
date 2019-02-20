<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'login-toolkit/login_setup.php';?>
    <?php include_once 'PHP-API/Localisation.php';?>
    <?php include_once 'PHP-Reusables/header.php';?>
    <title><?=l('Browse Tickets')?></title>
  </head>
  <body>
    <?php $PAGE_TITLE = l('Browse Tickets'); ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>
    <!-- main body -->
    <div class="container">
      <br>
      <script type="text/javascript">
      function override_get_url_for(call_id, selected_ticket)
      {
        return "ticket.php?ticket_id=" + selected_ticket;
      }
      </script>
      <?php include 'call_elements/search_widget.php';?>
    </div>
  </body>
</html>
