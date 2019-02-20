<?php include_once 'login-toolkit/login_setup.php'; ?>
<script type="text/javascript">
function previous_calls_display_data(data)
{
  for (var value in data)
  {
    if (value != 'employee_name')
      $("#prev_" + value).html(data[value]);
  }
  $("#prev_email").attr('href', 'mailto:' + encodeURI(data.name + ' <' + data.email + '>'));
  $("#prev_phone_no").attr('href', 'tel:' + encodeURI(data.phone_no));
}

$(document).ready(function()
{
  var previous_callers = [];
  $.ajax({
    'method' : 'GET',
    'data' : { ticket_id : <?=$this_ticket?> },
    'url' : 'PHP-API/PreviousCalls.php',
    'success' : function(response)
    {
      previous_callers = JSON.parse(response);
      for (var id in previous_callers)
      {
        var call = previous_callers[id];
        $("#previous_calls_dropdown").append($("<option>").html(call.employee_name).val(id));
      }
      $("#previous_calls_dropdown").selectpicker('refresh');
      $("#previous_calls_dropdown").selectpicker('render');
    }
  });

  $("#previous_calls_dropdown").change(function()
  {
    var caller = previous_callers[$(this).val()];
    $("#previous_calls_div").css('display', 'block');
    previous_calls_display_data(caller);
  });
});
</script>
<style type="text/css">
@media (max-width: 760px)
{
  #previous_calls tr
  {
    display: block;
    width: 100%;
  }

  #previous_calls tr:hover { background-color: #ffffffff; }

  #previous_calls th { width: 35%; }
  #previous_calls td { width: 65%; }

  #previous_calls th,
  #previous_calls td
  {
    display: block;
    float: <?= is_language_rtl() ? 'right' : 'left' ?>;
    margin: 0px;
  }
}
</style>
<div class="form-group"> <!-- Previous calls about this problem -->
  <label class="col-md-4 control-label" for="previous_calls_dropdown"><?=l('Previous callers:')?></label>
  <div class="col-md-8">
    <select id="previous_calls_dropdown" name="previous_calls_dropdown" class="form-control selectpicker" title="<?=l('None selected')?>">
    </select>
  </div>
</div>
<div class="table-responsive" style="display: none" id="previous_calls_div">
  <table class="table table-hover table-condensed" id="previous_calls">
    <tbody>
      <tr>
        <th><?=l('Job Title')?></th>
        <td id="prev_job_title" class="previous_calls_entry"></td>
        <th><?=l('Department')?></th>
        <td id="prev_location" class="previous_calls_entry"></td>
      </tr>
      <tr>
        <th><?=l('Email')?></th>
        <td><a href="#" id="prev_email" class="previous_calls_entry"></a></td>
        <th><?=l('Phone No.')?></th>
        <td>&lrm;<a href="#" id="prev_phone_no" class="previous_calls_entry"></a>&rlm;</td>
      </tr>
      <tr>
        <th><?=l('Operator')?></th>
        <td id="prev_operator" class="previous_calls_entry"></td>
        <th><?=l('Timestamp')?></th>
        <td id="prev_timestamp" class="previous_calls_entry"></td>
      </tr>
    </tbody>
  </table>
</div>
