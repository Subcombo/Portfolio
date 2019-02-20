<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include_once 'login-toolkit/login_setup.php';?>
    <?php include_once 'PHP-API/Localisation.php'; ?>
    <?php include_once 'PHP-Reusables/header.php'; ?>
    <link href="bootstrap-datepicker/css/bootstrap-datepicker3.css" rel="stylesheet">
    <script src='bootstrap-datepicker/js/bootstrap-datepicker.min.js'></script>
    <script src='bootstrap-datepicker/locales/bootstrap-datepicker.en-GB.min.js'></script>
    <script src='bootstrap-datepicker/locales/bootstrap-datepicker.zh-CN.min.js'></script>
    <script src='bootstrap-datepicker/locales/bootstrap-datepicker.de.min.js'></script>
    <script src='bootstrap-datepicker/locales/bootstrap-datepicker.ar.min.js'></script>
    <title><?=l('Home Page')?></title>
  </head>
  <body>
    <?php $PAGE_TITLE = l('Home') ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>
    <!-- Tabbed Container -->
    <div class="container">
      <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px;">
        <li class="active"><a data-toggle="tab" class="search_tab" href="#menu0" id="open_tickets"><?=l('Open')?></a></li>
        <li><a data-toggle="tab" class="search_tab" href="#menu1" id="pending_tickets"><?=l('Pending')?></a></li>
        <li><a data-toggle="tab" class="search_tab" href="#menu2" id="closed_tickets"><?=l('Solved')?></a></li>
        <li><a data-toggle="tab" class="search_tab" href="#menu3" id="all_closed_tickets"><?=l('All Solved')?></a></li>
        <li><a data-toggle="tab" class="search_tab" href="#menu4" id="all_tickets"><?=l('All')?></a></li>
        <li class="btn-group" role="group" style="float: <?= is_language_rtl() ? 'left' : 'right' ?>; line-height: 42px;">
          <button type="button" class="btn btn-default" onclick="window.location='employee_directory.php';">
            <span class="glyphicon glyphicon-user"></span> <?=l('Employee Directory')?>
          </button>
          <button type="button" class="btn btn-default" onclick="window.location='stats.php';">
            <span class="glyphicon glyphicon-stats"></span> <?=l('Statistics')?>
          </button>
          <button type="button" class="btn btn-default" data-toggle="modal" data-target="#availability_modal">
            <span class="glyphicon glyphicon-calendar"></span> <?=l('Availability')?>
          </button>
        </li>
      </ul>
      <!-- Tab content -->
      <div class="tab-content">
          <script type="text/javascript">
          function override_get_url_for(call_id, selected_ticket)
          {
            return "ticket.php?ticket_id=" + selected_ticket;
          }
          </script>
          <?php include 'call_elements/search_widget.php';?>
      </div>
    </div>
    <!-- Availability modal -->
    <div class="modal fade" id="availability_modal" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?=l('Select the days you\'re unavailable:')?></h4>
          </div>
          <div class="modal-body">
            <center>
              <div type="text" class='datepicker' name="datepicker_name" id='datepicker'></div>
            </center>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?=l('Cancel')?></button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" id='confirm'><?=l('Confirm')?></button>
          </div>
        </div>
      </div>
    </div>
    <script>
    /*-- when the Stats button is clicked it will go to a different page specified bellow
    Done by Eduardas Verba --*/
    $("#show_stats").click(function ()
    {
        location.href = "stats.php";
    });
    /*-- gets the dates from the database then puts it into the date picker (selects dates inside the datepicker for the specialist to see)
    where it coudl be reviewed and/or edited --*/
    function update_datepicker_with_dates(dates)
    {
      var print_dates = new Array();
      var specialist_dates = dates.toString().split(',');
      var reverse_array = new Array();
      for(var i = 0; i < dates.length; i++)
      {
        reverse_array = specialist_dates[i].split('-').reverse();
        print_dates[i] = '"'+reverse_array.join('-')+'"';
      }
      $('#datepicker').datepicker('setDates', print_dates);
      $(".today").click();
    }

    $(document).ready(function()
    {
      $.ajax(
      {
        method: 'GET',
        data: { 'specialist_id' : '<?=$user_id?>' },
        url: 'PHP-API/Availability.php',
        success: function(response)
        {
          var data = JSON.parse(response);
          update_datepicker_with_dates(data);
        }
      });
    });
    $('#datepicker').datepicker(
    {
      inline: true,
      showOtherMonths: true,
      todayBtn: true,
      clearBtn: true,
      multidate: true,
      todayHighlight: true,
      daysOfWeekDisabled: [0, 6],
      language: "<?=l('locale_code')?>",
      dateFormat: 'yy-mm-dd'
    });
    
    $("#confirm").click(function ()
    {
        var selected_dates = $('#datepicker').data('datepicker').getFormattedDate('yyyy-mm-dd');
        $.ajax(
        {
          method: 'POST',
          data:
          {
            'specialist_id' : '<?=$user_id?>',
            'dates' : selected_dates
          },
          url: 'PHP-API/Availability.php',
          success: function(response)
          {
            var data = JSON.parse(response);
            update_datepicker_with_dates(data);
          }
        });
    });
    /*-- checks if the search tab was clicked, if it was clicked it will then change the result window showing the information such as
    all tickets, tickets finished by a specific specialist, all tickets solved, tickets assigned to the specialist and not Solved
    Done by Eduardas Verba  --*/
    var category_value = 1;
    $('.search_tab').click(function()
    {
      switch(this.id)
      {
        case 'closed_tickets': category_value = 0; break;
        case 'open_tickets': category_value = 1; break;
        case 'pending_tickets': category_value = 2; break;
        case 'all_tickets': category_value = 100; break;
        case 'all_closed_tickets': category_value = 101; break;
      }
      search($("#search").val(), processSearchResults);
    });
    function fill_category_and_specialist_for_search(data)
    {
        data["category"] = category_value;
        data["specialist_id"] = <?php $employee['employee_id']?>;
    }
    </script>
  </body>
</html>
