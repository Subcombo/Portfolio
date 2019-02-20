<?php include_once 'login-toolkit/login_setup.php'; ?>
<script type="text/javascript">
var confirmed_solutions = [];
function confirmed_solutions_display_data(data)
{
  for (var value in data)
  {
    if (value != 'name')
      $("#sol_" + value).html(data[value]);
  }
  $("#sol_email").attr('href', 'mailto:' + encodeURI(data.name + ' <' + data.email + '>'));
  $("#sol_phone_no").attr('href', 'tel:' + encodeURI(data.phone_no));
}

function set_up_confirmed_solutions()
{
  $("#confirmed_solutions_dropdown").empty();
  $.ajax({
    'method' : 'GET',
    'data' : { ticket_id : <?=$this_ticket?> },
    'url' : 'PHP-API/Solutions.php',
    'success' : function(response)
    {
      confirmed_solutions = JSON.parse(response);
      for (var id in confirmed_solutions)
      {
        var sol = confirmed_solutions[id];
        $("#confirmed_solutions_dropdown").append(
          $("<option>")
            .html('<?=l("Solution")?> ' + sol.solution_id + ' <?=l("for ticket #")?>' + sol.ticket_id + " <?=l("by")?> " + sol.name)
            .val(id)
        );
      }
      $("#confirmed_solutions_dropdown").selectpicker('refresh');
      $("#confirmed_solutions_dropdown").selectpicker('render');
    }
  });
}

$(document).ready(function()
{
  $("#confirmed_solutions_dropdown").change(function()
  {
    var caller = confirmed_solutions[$(this).val()];
    $("#confirmed_solutions_div").css('display', 'block');
    confirmed_solutions_display_data(caller);
  });
  set_up_confirmed_solutions();
});
</script>
<style type="text/css">
@media (max-width: 760px)
{
  #confirmed_solutions tr
  {
    display: block;
    width: 100%;
  }

  #confirmed_solutions tr:hover { background-color: #ffffffff; }

  #confirmed_solutions th { width: 35%; }
  #confirmed_solutions td { width: 65%; }

  #confirmed_solutions th,
  #confirmed_solutions td
  {
    display: block;
    float: <?= is_language_rtl() ? 'right' : 'left' ?>;
    margin: 0px;
  }
}
</style>
<div class="form-group"> <!-- Previous calls about this problem -->
  <label class="col-md-4 control-label" for="confirmed_solutions_dropdown"><?=l('Solutions:')?></label>
  <div class="col-md-8">
    <select id="confirmed_solutions_dropdown" name="confirmed_solutions_dropdown" class="form-control selectpicker" title="<?=l('None selected')?>">
    </select>
  </div>
</div>

<div class="table-responsive" style="display: none" id="confirmed_solutions_div">
  <table class="table table-hover table-condensed" id="confirmed_solutions">
    <tbody>
      <tr>
        <th><?=l('Description')?></th>
        <td id="sol_description" class="confirmed_solutions_entry" colspan="3"></td>
      </tr>
      <tr>
        <th><?=l('Email')?></th>
        <td><a href="#" id="sol_email" class="confirmed_solutions_entry"></a></td>
        <th><?=l('For Ticket #')?></th>
        <td id="sol_ticket_id" class="confirmed_solutions_entry"></td>
      </tr>
      <tr>
      <th><?=l('Phone No.')?></th>
      <td>&lrm;<a href="#" id="sol_phone_no" class="confirmed_solutions_entry"></a>&rlm;</td>
      <th><?=l('Timestamp')?></th>
      <td id="sol_timestamp" class="confirmed_solutions_entry"></td>
      </tr>
    </tbody>
  </table>
</div>
