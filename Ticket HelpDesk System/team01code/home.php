<!DOCTYPE html>
<html lang="en-us">
  <head>
    <?php include_once 'login-toolkit/login_setup.php';?>
    <?php include_once 'PHP-API/Localisation.php';?>
    <?php include_once 'PHP-Reusables/header.php';?>
    <title><?=l('Home Page')?></title>
    <style type="text/css">
    .dashboard_ticket
    {
      display: block;
      padding: 1px 15px 10px 15px;
    }

    .dashboard_ticket:hover
    {
      text-decoration: none;
      background-color: #77777733;
    }
    </style>
  </head>
  <body>
    <?php $PAGE_TITLE = l('Home Page'); ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>
    <!-- Main body -->
    <div class="col-md-6 col-md-offset-3 col-xs-10 col-xs-offset-1">
      <?php
        if (isset($_GET["error"]))
        {
          echo '<div class="alert alert-danger" role="alert"><strong>' . l('Oh snap!') . '</strong> ' . $_GET['error'] . '</div>';
        }
      ?>
      <!-- Button trigger modal -->
      <div class="hidden-xs text-center">
        <div class="btn-group" role="group" aria-label="main actions">
          <a href="#" class="btn btn-default" data-toggle="modal" data-target="#form_modal">
            <i class="fa fa-phone" aria-hidden="true"></i> <?=l('New Caller')?>
          </a>
          <a type="button" class="btn btn-default" href="open_tickets.php">
            <i class="fa fa-ticket" aria-hidden="true"></i> <?=l('Tickets')?>
          </a>
          <a type="button" class="btn btn-default" href="stats.php">
            <i class="fa fa-bar-chart" aria-hidden="true"></i> <?=l('Statistics')?>
          </a>
          <a type="button" class="btn btn-default" href="employee_directory.php">
            <i class="fa fa-address-card" aria-hidden="true"></i> <?=l('Employee Directory')?>
          </a>
        </div>
      </div>
      <div class="row visible-xs">
        <div class="btn-group btn-group-vertical" role="group" aria-label="main actions" style="width: 100%;">
          <a href="#" class="btn btn-lg btn-default" data-toggle="modal" data-target="#form_modal">
            <i class="fa fa-phone" aria-hidden="true"></i> <?=l('New Caller')?>
          </a>
          <a type="button" class="btn btn-lg btn-default" href="open_tickets.php">
            <i class="fa fa-ticket" aria-hidden="true"></i> <?=l('Tickets')?>
          </a>
          <a type="button" class="btn btn-lg btn-default" href="stats.php">
            <i class="fa fa-bar-chart" aria-hidden="true"></i> <?=l('Statistics')?>
          </a>
          <a type="button" class="btn btn-lg btn-default" href="employee_directory.php">
            <i class="fa fa-address-card" aria-hidden="true"></i> <?=l('Employee Directory')?>
          </a>
        </div>
      </div>
      <!-- Modal -->
      <div class="modal fade" id="form_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
          <form class="modal-content" method="post" action="make_call.php" data-toggle="validator" role="form">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel"><?=l('New Caller Information')?></h4>
            </div>
            <!-- content -->
            <div class="modal-body">
              <div class="form-horizontal" id="caller_form" autocomplete="yes">
                    <!-- First Name -->
                <div class="form-group">
                  <label for="form_first_name" class="col-md-4 control-label" align="right"><?=l('First Name')?>:</label>
                  <div class="col-md-6" align="left">
                    <input required id="form_first_name" class="form-control input-md" type="text" data-provide="typeahead" name="first_name">
                  </div>
                </div>
                    <!-- Last Name -->
                <div class="form-group">
                  <label class="col-md-4 control-label" align="right"><?=l('Last Name')?>:</label>
                  <div class="col-md-6" align="left">
                    <input required id="form_last_name" class="form-control input-md" type="text" data-provide="typeahead" name="last_name">
                  </div>
                </div>
                     <!-- Employee ID -->
                <div class="form-group">
                  <label for="form_employee_id" class="col-md-4 control-label" align="right"><?=l('Employee ID')?>:</label>
                  <div class="col-md-6" align="left">
                    <input pattern="^[0-9]*$" id="form_employee_id" class="form-control input-md" type="text" data-provide="typeahead" name="employee_id">
                  </div>
                </div>
                    <!-- Phone Number -->
                <div class="form-group">
                  <label for="form_phone_no" class="col-md-4 control-label" align="right"><?=l('Phone Number')?>:</label>
                  <div class="col-md-6" align="left">
                    <input required pattern="^[+ 0-9]*$" id="form_phone_no" class="form-control input-md" type="text" data-provide="typeahead" name="phone_no">
                  </div>
                </div>
                    <!-- Email -->
                <div class="form-group">
                  <label for="form_email" class="col-md-4 control-label" align="right"><?=l('Email')?>:</label>
                  <div class="col-md-6" align="left">
                    <input required id="form_email" class="form-control input-md" type="email" data-provide="typeahead" name="email">
                  </div>
                </div>
                <!-- Job Title -->
                <div class="form-group">
                  <label for="form_job_title" class="col-md-4 control-label" align="right"><?=l('Job Title')?>:</label>
                  <div class="col-md-6" align="left">
                    <input required id="form_job_title" class="form-control input-md" type="text" data-provide="typeahead" name="job_title">
                  </div>
                </div>
                <!-- Office Location -->
                <div class="form-group">
                  <label for="form_country" class="col-md-4 control-label" align="right"><?=l('Office Location')?>:</label>
                  <div class="col-md-6" align="left">
                    <select class="form-control selectpicker" data-live-search="true" id="form_country" name="country" title="<?=l('Any')?>">
                    </select>
                  </div>
                </div>
                <!-- Department -->
                <div class="form-group">
                  <label for="form_department" class="col-md-4 control-label" align="right"><?=l('Department')?>:</label>
                  <div class="col-md-6" align="left">
                    <select required class="form-control selectpicker" data-live-search="true" id="form_department" name="department" title="<?=l('Any')?>">
                    </select>
                  </div>
                </div>
              </div>
            </div>
                <!-- footer -->
            <div class="modal-footer">
              <button type="button" id="clear_button" class="btn btn-default"><?=l('Clear')?></button>
              <button type="submit" class="btn btn-default" name="submit" value="end_call"><?=l('End Call')?></button>
              <button type="submit" class="btn btn-primary" name="submit" value="new_ticket"><?=l('New Ticket')?></button>
              <button type="submit" class="btn btn-primary" name="submit" value="existing_ticket"><?=l('Existing Ticket')?></button>
            </div>
          </form>
        </div>
      </div>
      <!-- Newsfeed -->
      <div class="row">
        <hr>
          <ul class="media-list">
              <li class="media">
                  <div class="media-body">
                    <!-- gets all of the tickets, slices the last 7 of the elements of the array, reverses it and prints it in html
                    Done by Eduardas Verba -->
                    <?php
                      $all_tickets = \db\get_all_tickets();
                      $last_tickets = array_slice($all_tickets, -7);
                      $ordered_last_tickets = array_reverse($last_tickets);
                      foreach ($ordered_last_tickets as $last_ticket)
                      {
                        $ticket_result = \db\get_ticket($last_ticket);
                        echo '<a class="dashboard_ticket" href="ticket.php?ticket_id=' . $ticket_result["ticket_id"] .
                          '"><h3><strong class="text-danger">' . l('Ticket #') .
                          htmlspecialchars($ticket_result["ticket_id"]) . '&ensp;<small>' .
                          htmlspecialchars(ldt($ticket_result["first_mentioned"])) . '</small></strong></h3>' . '<p>' .
                          htmlspecialchars($ticket_result["notes"]) . '</p></a>';
                      }
                    ?>
                  </div>
              </li>
          </ul>
      </div>
    </div>
    <script>
      autosize(document.querySelectorAll("textarea"));

      const fieldIDs = ['first_name', 'last_name', 'employee_id', 'phone_no', 'email', 'department', 'job_title', 'country'];

      function fillSelect(id)
      {
        return function(items)
        {
          var field = $('#form_'+id);
          field.empty();
          field.append($('<option>').text("<?=l('Any')?>").attr('value', ''));
          items.forEach(function (item, index) {
            field.append($('<option>').text(item).attr('value', item));
          })
          field.selectpicker('refresh');
          field.selectpicker('render');
        };
      }

      $("#clear_button").click(function() {
        $("form input, form select").val("");
        fetchCallOptions('department', fillSelect('department'));
        fetchCallOptions('country', fillSelect('country'));
        $('select').selectpicker('refresh');
        $('select').selectpicker('render');
      });

      $("#form_country").change(function() {
        $.get('PHP-API/TypeAhead.php', {
            'page' : 'call', 'fill_field' : 'department', 'country' : $("#form_country").val()
          },
          function(response) {
            fillSelect('department')(JSON.parse(response));
          }
        );
      });

      fetchCallOptions('department', fillSelect('department'));
      fetchCallOptions('country', fillSelect('country'));

      fieldIDs.forEach(function (id) {
        var field = $('#form_'+id);
        /*fetchCallOptions(id, function (items) {
          var field = $('#form_'+id);
          trySetTypeahead(id, items)
          if (field.selectpicker != null) {
            field.empty();
            items.forEach(function (item, index) {
              field.append($('<option>').text(item).attr('value', item));
            })
            field.selectpicker('refresh');
            field.selectpicker('render');
          }
        });*/
        if (field.typeahead != null)
        {
          field.typeahead({
            source : function (query, process) {
              fetchCallOptions(id, process);
            },
            afterSelect : function (item) {
              tryCallAutofill();
            },
            displayText: function(item) {
              return item.toString();
            },
            matcher: function(item) {
              return true
            }
          });
        }
      });

      function trySetTypeahead(id, items) {
        var field = $("#form_"+id)
        if (field.typeahead != null) {
          field.typeahead('destroy')
          field.typeahead({
            "source" : items,
            "afterSelect" : tryCallAutofill,
            "matcher" : function(item) {
              return true
            }
          });
        }
      }

      function fetchCallOptions(id, handleResult) {
        $.ajax({
          dataType: "json",
          url:'PHP-API/TypeAhead.php?page=call&fill_field='+id+'&'+urlQueryForCurrentFieldValues(),
          beforeSend : function(xhr, settings)
          {
            settings.url = 'PHP-API/TypeAhead.php?page=call&fill_field='+id+'&'+urlQueryForCurrentFieldValues();
            console.log(settings.url);
          },
          complete: function (response) {
            console.log(response['responseText']);
            handleResult(JSON.parse(response['responseText']));
          },
          error: function () {
            handleResult([]);
          }
        });
        return false;
      }
      function tryCallAutofill() {
        $.ajax({
          dataType: "json",
          url:'PHP-API/AutoComplete.php?page=call&'+urlQueryForCurrentFieldValues(),
          complete: function (response) {
            var arr = JSON.parse(response['responseText']);
            fieldIDs.forEach(function (id) {
              if (id in arr) {
                var field = $("#form_"+id)
                field.val(arr[id])
                field.selectpicker('refresh')
                field.selectpicker('render')
              }
            })
            $('form').validator('validate')
          },
          error: function () {}
        });
        return false;
      }

      function urlQueryForCurrentFieldValues() {
        return fieldIDs
            .map(function (name) {
              var value = $('#form_'+name).val(); //expects 'form_' + field_name as the textfield id
              return name + "=" + (value == null ? '' : encodeURIComponent(value));
            })
            .reduce(function (prev, next) {return prev+'&'+next});
      }
    </script>
  </body>
</html>
