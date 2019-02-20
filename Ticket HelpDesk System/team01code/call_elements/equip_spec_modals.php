<?php include_once 'PHP-API/Localisation.php';?>
<!-- Software Modal-->
<div class="modal fade" id="software_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=l('Add New Piece of Software')?></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
          <div class="form-group"><!-- Software Name -->
            <label class="col-md-4 control-label" align="right"><?=l('Name:')?></label>
            <div class="col-md-7" align="left">
              <input id="name" class="form-control input-md sw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <div class="form-group"><!-- Version -->
            <label class="col-md-4 control-label" align="right"><?=l('Version:')?></label>
            <div class="col-md-7" align="left">
              <input id="version" class="form-control input-md sw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <div class="form-group"><!-- Registration No. -->
            <label class="col-md-4 control-label" align="right"><?=l('Registration No.:')?></label>
            <div class="col-md-7" align="left">
              <input id="registration_no" class="form-control input-md sw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <input type="hidden" id="software_id" />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=l('Close')?></button>
        <button type="button" class="btn btn-primary" onclick="add_equipment('.sw_input')"><?=l('Save changes')?></button>
      </div>
    </div>
  </div>
</div>

<!-- Hardware Modal-->
<div class="modal fade" id="hardware_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=l('Add New Piece of Hardware')?></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal">
          <div class="form-group"><!-- Type -->
            <label class="col-md-4 control-label" align="right"><?=l('Type:')?></label>
            <div class="col-md-7" align="left">
              <input id="type" class="form-control input-md hw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <div class="form-group"><!-- Make -->
            <label class="col-md-4 control-label" align="right"><?=l('Make:')?></label>
            <div class="col-md-7" align="left">
              <input id="make" class="form-control input-md hw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <div class="form-group"><!-- Modela -->
            <label class="col-md-4 control-label" align="right"><?=l('Model:')?></label>
            <div class="col-md-7" align="left">
              <input id="model" class="form-control input-md hw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <div class="form-group"><!-- Serial No. -->
            <label class="col-md-4 control-label" align="right"><?=l('Serial No.:')?></label>
            <div class="col-md-7" align="left">
              <input id="serial_no" class="form-control input-md hw_input" type="text" data-provide="typeahead">
            </div>
          </div>
          <input type="hidden" id="hardware_id" />
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=l('Close')?></button>
        <button type="button" class="btn btn-primary" onclick="add_equipment('.hw_input')"><?=l('Save changes')?></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function get_values_for_fields(field_class)
  {
    var data = {};
    $(field_class).each(function()
    {
      data[this.id] = $(this).val();
    });
    return data;
  }

  function check_if_all_values_empty(data)
  {
    var empty = true;
    for (key in data)
    {
      if (data[key] != "")
      {
        empty = false;
        break;
      }
    }
    return empty;
  }

  function add_equipment(field_class)
  {
    var equipment_type = (field_class == '.sw_input' ? "software" : "hardware");
    var data = get_values_for_fields(field_class);
    if ($("#" + equipment_type + "_id").val() != "")
    {
      var data = {
        "equipment_id" : $("#" + equipment_type + "_id").val(),
        "ticket_id" : <?= $this_ticket ?>
      }
      equip_ajax('PUT', data, refresh_equipment_table);
    }
    else if (!check_if_all_values_empty(data))
    {
      data["ticket_id"] = <?= $this_ticket ?>;
      data["equipment_type"] = equipment_type;
      data["status"] = 2;
      equip_ajax('POST', data, refresh_equipment_table);
    }
    else {
      alert('Can\'t create an entry, because the given fields are empty.');
      return true;
    }
    $(field_class).val("");
    $('.modal').modal('hide');
    return false;
  }

  function setup_suggest_for(field, field_class)
  {
    var _field_class = field_class;
    var equipment_type = _field_class == ".sw_input" ? "software" : "hardware";
    function get_data()
    {
      var data = get_values_for_fields(_field_class);
      data["fill_field"] = field.id;
      data["page"] = equipment_type;
      return data;
    }

    $(field).typeahead(
    {
      source : function (query, process)
      {
          return $.ajax(
          {
            method : 'GET',
            data : get_data(),
            beforeSend : function(xhr, settings)
            {
              settings.data = jQuery.param(get_data(), false);
            },
            url : 'PHP-API/TypeAhead.php',
            success : function (response)
            {
              $("#" + equipment_type + "_id").val("");
              console.log(get_data());
              console.log(response);
              process(JSON.parse(response));
            }
          });
        },
        afterSelect : function (item) {
          try_autocomplete(_field_class);
        }
    });
  }

  function try_autocomplete(field_class)
  {
    var _field_class = field_class;
    function get_data()
    {
      var data = get_values_for_fields(_field_class);
      data["page"] = _field_class == ".sw_input" ? "software" : "hardware";
      return data;
    }

    $.ajax(
    {
      method : 'GET',
      data : get_data(),
      beforeSend : function(xhr, settings)
      {
        settings.data = jQuery.param(get_data(), false);
      },
      url : 'PHP-API/AutoComplete.php',
      success : function (response)
      {
        var obj = JSON.parse(response);
        for (var key in obj) {
          $("#" + key).val(obj[key]);
        }
        console.log(obj);
      }
    });
  }

  $(document).ready(function()
  {
    $(".sw_input").each(function(input)
    {
      setup_suggest_for(this, ".sw_input");
    });
    $(".hw_input").each(function(input)
    {
      setup_suggest_for(this, ".hw_input");
    });
  });
</script>


<!-- Specialst Modal-->
<div class="modal fade" id="specialist_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?=l('Select a Specialist')?></h4>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-hover modal-body" id="spec_suggestion_table">
            <tr>
              <th></th>
              <th><?=l('Name')?></th>
              <th><?=l('Site')?></th>
              <th><?=l('Workload')?><br/><small><?=l('(open cases)')?></small></th>
              <th><?=l('Relevant tags')?></th>
              <th><?=l('Availability')?><br/><small><?=l('(next 10 working days)')?></small></th>
            </tr>
          </table>
        </div>
        <ul style="padding: 0px; list-style-type: none; display: block; float: right;">
          <li>⬛︎ – <?=l('on leave')?></li>
          <li>⬜︎ – <?=l('available')?></li>
        </ul>
        <div class="clearfix"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?=l('Cancel')?></button>
        <button type="button" class="btn btn-default" data-dismiss="modal" id="sel_spec"><?=l('Select Specialist')?></button>
        <button type="button" class="btn btn-primary" data-dismiss="modal" id="send_spec"><?=l('Select & Send')?></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function()
{
  function create_availability_pattern(dates)
  {
    var available_symbol = "⬜︎";
    var on_leave_symbol = "⬛︎";
    var date_idx = new Date();
    var result = "";
    for (var i = 0; i < 14; i++)
    {
      if (date_idx.getDay() == 6)
      {
        if (i != 0) result += "&emsp;";
        date_idx.setDate(date_idx.getDate() + 2);
        i++;
      }
      else if (date_idx.getDay() != 0)
      {
        var date_str = date_idx.toISOString().substr(0, 10);
        var busy = false;
        for (var day in dates)
        {
          if (dates[day] == date_str)
          {
            busy = true;
            break;
          }
        }
        result += busy == true ? on_leave_symbol : available_symbol;
        date_idx.setDate(date_idx.getDate() + 1);
      }
      else date_idx.setDate(date_idx.getDate() + 1);
    }
    return result;
  }

  function populate_spec_table(data)
  {
    $('#spec_suggestion_table tr').not(':first').remove();
    for (var idx in data)
    {
      var sel = idx == 0 ? "checked" : "";
      var s = data[idx];
      $("#spec_suggestion_table").find("tbody").append('<tr>' +
        '<td class="text-center"><input type="radio" name="selected_specialist" ' + sel +
        ' value="' + s.specialist_id + ';' + s.name + '"/></td>' +
        '<td>' + s.name + '</td>' +
        '<td>' + s.site + '</td>' +
        '<td>' + s.workload + '</td>' +
        '<td>' + s.relevant_tags.join(', ') + '</td>' +
        '<td>' + create_availability_pattern(s.unavailability) + '</td>' +
      '</tr>');
    }
    $('#spec_suggestion_table tr').click(function()
    {
        $(this).find('input[type=radio]').prop('checked', true);
    });
  }

  $('#spec_choose_btn').click(function()
  {
    $.ajax({
      'method' : 'GET',
      'data' : { 'ticket_id' : <?=$this_ticket?> },
      'url' : 'PHP-API/SpecialistSuggestion.php',
      'success' : function(response) {
        var data = JSON.parse(response);
        console.log(data);
        populate_spec_table(data);
      }
    });
  });

  function update_specialist_problem_send_button()
  {
    var is_spec = <?= $user_type === \db\login_status::Specialist ? 'true' : 'false' ?>;
    var user_id = <?= $user_id ?>;
    $.ajax({
      'method' : 'GET',
      'data' : { 'assigned_specialist_for' : <?=$this_ticket?> },
      'url' : 'PHP-API/SpecialistSuggestion.php',
      'success' : function(response) {
        var data = JSON.parse(response);
        if (response != '{}' && !(is_spec && data.specialist_id == user_id))
        {
          var spec_id = data.specialist_id;
          var spec_name = data.name;
          $('#specialist_choice').html('<?=l("Sent to")?> ' + spec_name);

          $('#send_spec_btn').html('<span class="glyphicon glyphicon-remove"></span> <?=l("Unsend to Specialist")?>')
            .removeClass("btn-primary")
            .addClass("btn-default")
            .removeAttr('disabled');
        }
        else
        {
          $('#specialist_choice').html('<?=l("No specialist selected")?>');
          $('#send_spec_btn').html('<span class="glyphicon glyphicon-share-alt"></span> <?=l("Send to Specialist")?>')
            .addClass("btn-primary")
            .removeClass("btn-default")
            .attr('disabled', '');
        }
      }
    });
  }


  $('#sel_spec').click(function()
  {
    if ($("input[name=selected_specialist]:checked").size() > 0)
    {
      var selected_spec = $("input[name=selected_specialist]:checked").val().split(';');
      var spec_id = selected_spec[0];
      var spec_name = selected_spec[1];
      $('#specialist_choice').html(spec_name);
      $('#send_spec_btn').removeAttr('disabled');
    }
  });

  $('#send_spec_btn, #send_spec').click(function()
  {
    if ($('#send_spec_btn').hasClass('btn-primary'))
    {
      if ($("input[name=selected_specialist]:checked").size() == 0) return;
      var selected_spec = $("input[name=selected_specialist]:checked").val().split(';');
      var spec_id = selected_spec[0];
      var spec_name = selected_spec[1];

      $.ajax({
        'method' : 'GET',
        'data' : { 'ticket_id' : <?=$this_ticket?>, 'send_to' : spec_id },
        'url' : 'PHP-API/SpecialistSuggestion.php',
        'success' : function(response) {
          console.log("Ticket sent to " + spec_id);
          if (<?= $user_type === \db\login_status::Specialist ? 'true' : 'false' ?>)
          {
            $("#comment").val("<?=l('Ticket transferred to')?> " + spec_name);
            post_comment(function() { window.location.reload(); });
          }
          update_specialist_problem_send_button();
        }
      });
    }
    else
    {
      $.ajax({
        'method' : 'GET',
        'data' : { 'unbind_specialist_from_ticket_id' : <?=$this_ticket?> },
        'url' : 'PHP-API/SpecialistSuggestion.php',
        'success' : function(response) {
          console.log("Specialist removed from ticket");
          update_specialist_problem_send_button();
        }
      });
    }
  });

  update_specialist_problem_send_button();
});
</script>
