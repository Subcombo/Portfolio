<?php
  include_once 'login-toolkit/login_setup.php';
  if (!isset($this_ticket) || !isset($ticket_details))
    die('$this_ticket not set.');
?>

<script type="text/javascript">
function mark_ticket_as(status, callback)
{
  $.ajax(
  {
    'method' : 'POST',
    'data' : { 'ticket_id' : <?=$this_ticket?>, 'set_status' : status },
    'url' : 'PHP-API/Solutions.php',
    'success' : function(response)
    {
      refresh_open_pending_buttons();
      if (typeof callback === 'function') callback();
    }
  });
}

function refresh_open_pending_buttons()
{
  $.ajax(
  {
    'method' : 'GET',
    'data' : { 'status_for_ticket' : <?=$this_ticket?> },
    'url' : 'PHP-API/Similar.php',
    'success' : function(response)
    {
      var status = JSON.parse(response).status;
      if (status == 1)
      {
        $("#mark_open").css("display", "none");
        $("#mark_pending").css("display", "inline-block");
      }
      else if (status == 2)
      {
        $("#mark_open").css("display", "inline-block");
        $("#mark_pending").css("display", "none");
      }
      else
      {
        $("#mark_open").css("display", "none");
        $("#mark_pending").css("display", "none");
      }
    }
  });
}

$(document).ready(function()
{
  $("#post_solution").click(function()
  {
    if ($("#solution_input").val() != "")
    {
      $.ajax(
      {
        'method' : 'POST',
        'data' : { 'ticket_id' : <?=$this_ticket?>, 'solution' : '' },
        'url' : 'PHP-API/Solutions.php',
        'beforeSend' : function(xhr, settings)
        {
          settings.data += encodeURIComponent($("#solution_input").val());
        },
        'success' : function(response)
        {
          set_up_confirmed_solutions();
          mark_ticket_as(0, function() { window.location.reload(); });
        }
      });
    }
  });

  $("#mark_open").click(function() { mark_ticket_as(1) });
  $("#mark_pending").click(function() { mark_ticket_as(2) });

  refresh_open_pending_buttons();
});
</script>

<div class="col-md-8 col-md-offset-2 col-xs-12">
  <div class="form-group"> <!-- Solution -->
    <label class="col-md-2 control-label" for="solution_input"><?=l('Add solution:')?></label>
    <div class="col-sm-10">
      <textarea id="solution_input" name="solution_input" class="form-control input-md" ></textarea>
    </div>
  </div>
  <div class="text-right">
    <button type="button" class="btn btn-default btn-sm" id="mark_pending" style="display: none;">
      <span class="glyphicon glyphicon-edit"></span>&ensp;<?=l("Mark as pending")?>
    </button>
    <button type="button" class="btn btn-default btn-sm" id="mark_open" style="display: none;">
      <span class="glyphicon glyphicon-ok"></span>&ensp;<?=l("Mark as open")?>
    </button>
    <button type="button" class="btn btn-primary btn-sm" id="post_solution">
      <span class="glyphicon glyphicon-send"></span>&ensp;<?=l("Post solution & close")?>
    </button>
  </div>
</div>
