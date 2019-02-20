<?php
  if (!isset($this_ticket) || !isset($ticket_details))
    die('$this_ticket not set.');
?>

<script type="text/javascript">
function depth_first_sort(data)
{
  var sorted = [];
  var keyed_data = {};
  for (idx in data)
  {
    var parent = data[idx].parent_tag_id;
    if (keyed_data[parent] === undefined) keyed_data[parent] = [];
    keyed_data[parent].push(data[idx]);
  }
  var queue = [keyed_data[null][0]];
  while (queue.length > 0)
  {
    var top = queue.pop();
    sorted.push(top);
    for (e in keyed_data[top.tag_id])
    {
      queue.push(keyed_data[top.tag_id][e]);
    }
  }
  return sorted;
}

function update_tag_list(on_done_before_select_refresh)
{
  $.ajax({
    'method' : 'GET',
    'url' : 'PHP-API/GetTags.php',
    'success' : function(response)
    {
      var data = depth_first_sort(JSON.parse(response));
      console.log(data);
      $('#general_tag').empty();
      for (var idx in data)
      {
        var tag_id = data[idx].tag_id;
        var parent_path = data[idx].parent_path.join(' / ');
        var name = data[idx].tag_name;
        parent_path = parent_path != '' ? ('(in ' + parent_path + ')') : '';
        $("#general_tag").append("<option value='" + tag_id + "' data-subtext='" + parent_path + "' " +
          "title='"+ name + "'>" + '&ensp;&ensp;&ensp;'.repeat(data[idx].parent_path.length) + name + "</option>");
      }
      on_done_before_select_refresh();
      $("#general_tag").selectpicker('refresh');
      $("#general_tag").selectpicker('render');
    }
  });
}

function refresh_problem_details()
{
  $.ajax({
    'method' : 'GET',
    'data' : { 'ticket_id' : <?=$this_ticket?> },
    'url' : 'PHP-API/TicketDetails.php',
    'success' : function(response)
    {
      var data = JSON.parse(response);
      $("#specific_tag").val("");
      $("#notes").val(data.notes);
      $("#general_tag").selectpicker('val', data.problem_tag_id);
      autosize.update($("#notes"));
    }
  });
}

function apply_problem_detail_updates()
{
  $.ajax({
    'method' : 'POST',
    'data' : {
      'ticket_id' : <?=$this_ticket?>,
      'notes' : $("#notes").val(),
      'parent_tag_id' : $("#general_tag").selectpicker('val'),
      'new_tag' : $("#specific_tag").val()
    },
    'url' : 'PHP-API/TicketDetails.php',
    'success' : function()
    {
      $('#ticket_update_btn')
        .html('<span class="glyphicon glyphicon-save"></span> Update ticket <small>(<?=l("last updated at ")?>' + (new Date().toLocaleTimeString()) + ')</small>');
      update_tag_list(refresh_problem_details);
      update_similar_problems();
    },
    'error' : function()
    {
      $('#ticket_update_btn');
    }
  });
}

$(document).ready(function()
{
  update_tag_list(refresh_problem_details);
  $('#ticket_update_btn').click(apply_problem_detail_updates);
});
</script>

<form class="form-horizontal">
  <div class="col-md-6">
    <div class="form-group"> <!-- General Problem Tag -->
      <label class="col-md-4 control-label problem_details" for="general_tag"><?=l('General Problem Tag:')?></label>
      <div class="col-md-8">
        <select id="general_tag" name="general_tag" class="form-control selectpicker" data-live-search="true">
        </select>
      </div>
    </div>

    <div class="form-group"> <!-- Specific Problem Tag -->
      <label class="col-md-4 control-label problem_details" for="specific_tag"><?=l('New Child Tag:')?></label>
      <div class="col-md-8">
        <input id="specific_tag" name="specific_tag" type="text" placeholder="<?=l('Printer shredding paper')?>" class="form-control input-md">
      </div>
    </div>

    <div class="form-group"> <!-- Notes -->
      <label class="col-md-4 control-label problem_details" for="notes"><?=l('Notes:')?></label>
      <div class="col-md-8">
        <textarea id="notes" name="notes" placeholder="<?=l('Printer shreds only prime numbered pages')?>" class="form-control input-md"></textarea>
      </div>
    </div>

    <div class="text-right"> <!-- Save ticket & change priority -->
      <select class="selectpicker" data-style="btn-default btn-sm" data-width="fit" id="priority_selection">
        <option value="0" data-icon="glyphicon-chevron-up" <?= $ticket_details["priority"] === \db\priority_level::High ? "selected" : "" ?>><?=l('High priority')?></option>
        <option value="1" data-icon="glyphicon-record" <?= $ticket_details["priority"] === \db\priority_level::Normal ? "selected" : "" ?>><?=l('Normal priority')?></option>
        <option value="2" data-icon="glyphicon-chevron-down" <?= $ticket_details["priority"] === \db\priority_level::Low ? "selected" : "" ?>><?=l('Low priority')?></option>
      </select>
      <button class="btn btn-primary btn-sm" type="button" id="ticket_update_btn">
        <span class="glyphicon glyphicon-save"></span> <?=l('Update ticket')?>
      </button>
    </div>

    <hr />

    <?php include 'equipment_tables.php' ?>
    <?php include 'priority.php' ?>

    <div class="text-right">
      <div class="btn-group" role="group" aria-label="<?=l('Add software or hardware button group')?>">
        <a class="btn btn-default btn-sm btn-fill" data-toggle="modal" data-target="#software_modal">
          <span class="glyphicon glyphicon-plus"></span> <?=l('Add software')?>
        </a>
        <a class="btn btn-default btn-sm btn-fill" data-toggle="modal" data-target="#hardware_modal">
          <span class="glyphicon glyphicon-plus"></span> <?=l('Add hardware')?>
        </a>
      </div>
    </div>
  </div>

  <hr class="visible-sm-block">
  <div class="col-md-6">

    <?php include 'similar.php' ?>

    <hr>
    <div class="form-group" id="refer"> <!-- Send to specialist -->
      <label class="col-md-4 control-label" for="notes"><?=l('Refer to Specialist:')?></label>
      <div class="col-md-8">
        <div class="input-group">
          <span class="form-control" id="specialist_choice"><?=l('No specialist selected')?></span>
          <span class="input-group-btn">
            <button id="spec_choose_btn" class="btn btn-default" type="button" data-toggle="modal" data-target="#specialist_modal"><?=l('Choose...')?></button>
          </span>
        </div>
      </div>
    </div>
    <div class="text-right">
      <button class="btn btn-primary btn-sm" type="button" id="send_spec_btn" disabled>
        <span class="glyphicon glyphicon-share-alt"></span> <?=l('Send to Specialist')?>
      </button>
    </div>
    <hr>

    <script type="text/javascript">
    $(document).ready(function() {
      autosize($('#notes'));
      $("#delete_ticket").click(function()
      {
        if(confirm("<?=l('Are you sure you want to delete this ticket?')?>'"))
        {
          window.location.replace("call.php?call_id=<?=$call_id?>&selected_ticket=<?=$this_ticket?>&action=delete");
        }
      });
    });
    </script>
    <div class="text-right"> <!-- Delete ticket -->
      <button class="btn btn-danger btn-sm" type="button" id="delete_ticket">
        <span class="glyphicon glyphicon-trash"></span> <?=l('Delete this Ticket')?>
      </button>
    </div>
  </div>
</form>
