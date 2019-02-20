<script type="text/javascript">
var similar = [];
function update_similar_problems()
{
  $.ajax(
  {
    method : 'GET',
    data : { "ticket_id" : <?=$this_ticket?> },
    url : 'PHP-API/Similar.php',
    success : function (response)
    {
      console.log(response);
      $("#similar_dropdown").empty();
      similar = data = JSON.parse(response);
      for (var idx in data)
      {
        id = data[idx].solution_id;
        ticket_id = data[idx].ticket_id;
        tag_name = data[idx].problem_tag_name;
        $("#similar_dropdown").append("<option value='" + idx + "'><?=l('Solution')?> "
          + id + " <?=l('for #')?>" + ticket_id + ": " + tag_name  + "</option>");
      }
      $("#similar_dropdown").selectpicker({title: '' + data.length + "<?=l(' found')?>"});
      $("#similar_dropdown").selectpicker('refresh');
      $("#similar_dropdown").selectpicker('render');
    }
  });
}

function update_close_button()
{
  $.ajax({
    'method' : 'GET',
    'data' : {'status_for_ticket' : <?= $this_ticket ?> },
    'url' : 'PHP-API/Similar.php',
    'success' : function(response)
    {
      var obj = JSON.parse(response);
      if (obj.status == 0)
      {
        console.log("Ticket closed.");
        $("#close_ticket_button").html('<span class="glyphicon glyphicon-remove"></span> <?=l('Reopen Ticket')?>')
          .removeClass("btn-primary")
          .addClass("btn-default")
          .removeAttr('disabled');
      }
      else if (obj.status == 1)
      {
        console.log("Ticket reopened.");
        $("#close_ticket_button").html('<span class="glyphicon glyphicon-ok"></span> <?=l('Close Ticket')?>')
          .addClass("btn-primary")
          .removeClass("btn-default");
      }
    }
  });
}

$(document).ready(function()
{
  $("#similar_dropdown").on('change', function(x)
  {
    var selected = $(this).find("option:selected").val();
    var obj = similar[selected];
    $("#similar_desc_formgroup").css("display", "block");
    $("#solution_formgroup").css("display", "block");
    $("#notes_problem").html(obj.notes);
    $("#problem_metadata").html("<?=l('registered by')?> <b>" + obj.operator_name + "</b> <?=l('on')?> " + obj.first_mentioned);
    $("#notes_solution").html(obj.description);
    $("#solution_metadata").html("<?=l('provided by')?> <b>" + obj.specialist_name + "</b> <?=l('on')?> " + obj.solution_timestamp);
    $("#close_ticket_button").removeAttr('disabled');
  });
  update_similar_problems();

  $("#close_ticket_button").click(function()
  {
    var selected = $("#similar_dropdown").find("option:selected").val();
    var obj = similar[selected];
    if ($(this).hasClass("btn-primary"))
    {
      console.log(obj);
      console.log(selected);
      $.ajax(
      {
        method : 'GET',
        data : { "ticket_id" : <?=$this_ticket?>, "solution_id" :  obj.solution_id },
        url : 'PHP-API/Similar.php',
        success : function (response)
        {
          update_close_button();
        }
      });
    }
    else
    {
      $.ajax(
      {
        method : 'GET',
        data : { "open_ticket" : <?=$this_ticket?> },
        url : 'PHP-API/Similar.php',
        success : function (response)
        {
          update_close_button();
        }
      });
    }
  });

  update_close_button();
});
</script>

<div class="form-group"> <!-- Similar problems -->
  <label class="col-md-4 control-label" for="similar_dropdown"><?=l('Similar&nbsp;problems:')?></label>
  <div class="col-md-8">
    <select id="similar_dropdown" name="similar_dropdown" class="form-control selectpicker" title="None found">
    </select>
  </div>
</div>
<div class="form-group" id="similar_desc_formgroup" style="display: none;"> <!-- Similar problems -->
  <label class="col-md-4 text-right" for="notes"><?=l('Notes:')?></label>
  <div class="col-md-8">
    <p id="notes_problem"></p>
    <small id="problem_metadata"></small>
  </div>
</div>
<div class="form-group" id="solution_formgroup" style="display: none;"> <!-- Similar problems -->
  <label class="col-md-4 text-right" for="notes"><?=l('Solution:')?></label>
  <div class="col-md-8">
    <p id="notes_solution"></p>
    <small id="solution_metadata"></small>
  </div>
</div>
<div class="text-right">
  <button class="btn btn-primary btn-sm" id="close_ticket_button" type="button" disabled>
    <span class="glyphicon glyphicon-ok"></span> <?=l('Close Ticket')?>
  </button>
</div>
