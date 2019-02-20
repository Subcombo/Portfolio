<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'login-toolkit/login_setup.php'; ?>
    <?php include_once 'PHP-API/Localisation.php'; ?>
    <?php include_once 'PHP-Reusables/header.php'; ?>
    <title><?=l('Ticket')?></title>
    <?php
      if (!isset($_GET["call_id"])
        || !($call_id = $_GET["call_id"])
        || !($call_details = \db\get_call($call_id)))
      {
        http_response_code(400);
        die("call_id parameter mandatory");
      }

      if (isset($_GET["action"]) && isset($_GET["selected_ticket"]))
      {
        if ($_GET["action"] == 'delete')
        {
          $tickets = \db\get_tickets_for_call($call_id);
          $first_undeleted = null;
          foreach ($tickets as $ticket_id)
          {
            $ticket_details = \db\get_ticket($ticket_id);
            if ($ticket_details["call_id"] == $call_id && $ticket_id == $_GET["selected_ticket"])
            {
              \db\delete_ticket($ticket_id);
            }
            elseif ($ticket_id == $_GET["selected_ticket"])
            {
              \db\unbind_ticket_from_call($ticket_id, $call_id);
            }
            elseif ($first_undeleted === null) $first_undeleted = $ticket_id;
          }
          header("Location: call.php?" . http_build_query([ "call_id" => $call_id, "selected_ticket" => $first_undeleted ]));
        }
        elseif ($_GET["action"] == 'add_ticket')
        {
          \db\assign_ticket_to_call($_GET["selected_ticket"], $call_id);
          header("Location: call.php?" . http_build_query([ "call_id" => $call_id, "selected_ticket" => $_GET["selected_ticket"] ]));
        }
        else http_response_code(400);
        exit();
      }
      elseif (isset($_GET["action"]))
      {
        if ($_GET["action"] == 'new_ticket')
        {
          $new_ticket_id = \db\create_ticket($call_id, 1, "", 0);
          header("Location: call.php?" . http_build_query([ "call_id" => $call_id, "selected_ticket" => $new_ticket_id ]));
        }
        elseif ($_GET["action"] == 'delete_all_new')
        {
          $tickets = \db\get_tickets_for_call($call_id);
          $first_undeleted = null;
          foreach ($tickets as $ticket_id)
          {
            $ticket_details = \db\get_ticket($ticket_id);
            if ($ticket_details["call_id"] == $call_id)
            {
              \db\delete_ticket($ticket_id);
            }
            elseif ($first_undeleted === null) $first_undeleted = $ticket_id;
          }
          header("Location: call.php?" . http_build_query([ "call_id" => $call_id, "selected_ticket" => $first_undeleted ]));
        }
        exit();
      }

      $caller = \db\get_employee($call_details["reporting_employee"]);
      $tickets = \db\get_tickets_for_call($call_id);

      $this_ticket = isset($_GET["selected_ticket"]) ? $_GET["selected_ticket"] : null;
    ?>
  </head>
  <body>
    <?php
    $PAGE_TITLE = l('Call with') . " <b>" . $caller["first_name"] . " " . $caller["last_name"] . "</b> "
      . l('on') . " " . ldt($call_details["call_timestamp"]);
    ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>

    <div class="container-fluid">
      <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px;">
        <?php foreach ($tickets as $ticket_id) { ?>
        <li role="presentation" class="<?= ($ticket_id == $this_ticket ? "active" : "") ?>">
          <a href="call.php?call_id=<?= $call_id ?>&selected_ticket=<?= $ticket_id ?>" aria-controls="home" role="tab">
            Ticket #<?= $ticket_id ?>
            <?php
              $ticket_details = \db\get_ticket($ticket_id);
              if ($ticket_details["call_id"] == $call_id) echo '<small>' . l('(new)') . '</small>';
              elseif ($ticket_details["status"] == \db\status::Open) echo '<small>' . l('(open)') . '</small>';
              elseif ($ticket_details["status"] == \db\status::Pending) echo '<small>' . l('(pending)') . '</small>';
              elseif ($ticket_details["status"] == \db\status::Closed) echo '<small>' . l('(closed)') . '</small>';
            ?>
          </a>
        </li>
        <?php } ?>
        <li role="presentation" style="float: <?= is_language_rtl() ? 'left' : 'right' ?>; line-height: 42px;">
          <div class="btn-group">
            <a class="btn btn-success btn-sm" href="call.php?call_id=<?=$call_id?>&action=new_ticket">
              <span class="glyphicon glyphicon-plus"></span> <?=l('New Ticket')?>
            </a>
            <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#search_modal">
              <span class="glyphicon glyphicon-search"></span> <?=l('Search Tickets')?>
            </a>
            <a class="btn btn-sm btn-default" href="home.php">
              <span class="glyphicon glyphicon-stop"></span> <?=l('End Call')?>
            </a>
            <a class="btn btn-sm btn-danger" href="call.php?call_id=<?=$call_id?>&action=delete_all_new">
              <span class="glyphicon glyphicon-trash"></span> <?=l('Delete All New')?>
            </a>
          </div>
        </li>
      </ul>

      <div class="tab-content" id="ticket_details">
        <div role="tabpanel" class="tab-pane active" id="home">
          <?php
            if ($this_ticket !== null)
            {
              $ticket_details = \db\get_ticket($this_ticket);
              if ($ticket_details["call_id"] == $call_id)
                include 'call_elements/edit_ticket.php';
              else
                include 'call_elements/view_ticket.php';
            }
          ?>
        </div>
      </div>
    </div>

    <?php include 'call_elements/equip_spec_modals.php' ?>

    <!-- Search Modal-->
    <?php if ($this_ticket === null) { ?>
      <script type="text/javascript">
        $(window).load(function(){
          $('#search_modal').modal('show');
        });
      </script>
    <?php } ?>
    <div class="modal fade in" id="search_modal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?=l('Search')?></h4>
          </div>
          <div class="modal-body">
            <script type="text/javascript">
              // This function is used to specify the url for results clicked in the search box
              function override_get_url_for(call_id, selected_ticket)
              {
                return 'call.php?call_id=' + call_id + '&selected_ticket=' + selected_ticket +  '&action=add_ticket';
              }
            </script>
            <?php include 'call_elements/search_widget.php' ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?=l('New Problem')?></button>
          </div>
        </div>
      </div>
    </div>

  	<script>
      $('textarea').textcomplete([{
        match: /(^|\s|\\b)#(\w*)$/,
        search: function (term, callback) {
          var words = [term, 'desperatecaller', 'windows', 'urgent', 'mouse', 'logitech', 'printer'];
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
