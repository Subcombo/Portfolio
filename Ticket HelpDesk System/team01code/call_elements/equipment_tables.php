<?php
  if (!isset($this_ticket) || !isset($ticket_details))
    die('$this_ticket not set.');
?>

<script type="text/javascript">
function equip_ajax(method, data, callback)
{
  return $.ajax({
    url: 'PHP-API/Equipment.php',
    type: method,
    data: data,
    success: function(response) {
      callback(response);
    }
  });
}

function remove_equipment_by_id(id)
{
  equip_ajax('DELETE', {
    'ticket_id' : <?=$this_ticket?>,
    'equipment_id' : id
  }, function() {
    refresh_equipment_table();
  });
}

function refresh_equipment_table()
{
  console.log("Refresh initiated.");
  var status = {
    "0" : "<span class='glyphicon glyphicon-remove-sign icon-danger'></span>",
    "1" : "<span class='glyphicon glyphicon-ok-sign icon-success'></span>",
    "2" : "<span class='glyphicon glyphicon-question-sign icon-warning'></span>",
  };

  function populate_tables(data)
  {
    data = JSON.parse(data);
    console.log("Response: " + JSON.stringify(data));
    $("#software_list tr").not(':first').remove();
    for (var idx in data.software)
    {
      var s = data.software[idx];
      $("#software_list").find("tbody").append("<tr><td>" + s.name + "</td>" +
          "<td>" + s.version + "</td>" +
          "<td class='hidden-xs'>" + s.registration_no + "</td>" +
          "<td class='hidden-xs'>" + s.equipment_id + "</td>" +
          "<td class='text-center'>" + status[s.status] + "</td><td>" +
          "<a class='glyphicon glyphicon-trash' class='text-center' " +
          "href='javascript:remove_equipment_by_id(" + s.equipment_id + ")'></a>" +
        "</td></tr>");
    }

    $("#hardware_list tr").not(':first').remove();
    for (var idx in data.hardware)
    {
      var s = data.hardware[idx];
      $("#hardware_list").find("tbody").append("<tr><td class='hidden-xs'>" + s.type + "</td>" +
          "<td>" + s.make + "</td>" +
          "<td>" + s.model + "</td>" +
          "<td class='hidden-xs'>" + s.serial_no + "</td>" +
          "<td class='hidden-xs'>" + s.equipment_id + "</td>" +
          "<td class='text-center'>" + status[s.status] + "</td><td>" +
          "<a class='glyphicon glyphicon-trash' class='text-center' " +
          "href='javascript:remove_equipment_by_id(" + s.equipment_id + ")'></a>" +
        "</td></tr>");
    }

    if (data.hardware.length == 0) $("#hardware_div").css("display", "none");
    else $("#hardware_div").css("display", "block");
    if (data.software.length == 0) $("#software_div").css("display", "none");
    else $("#software_div").css("display", "block");

    if (typeof update_similar_problems === 'function')
      update_similar_problems();
  }

  equip_ajax('GET', { 'ticket_id' : <?= $this_ticket ?> }, populate_tables);
}

$(document).ready(function()
{
  refresh_equipment_table();
});
</script>

<div class="table-responsive" id="software_div">
  <table class="table table-hover table-condensed" id="software_list"> <!-- Software List -->
    <tr>
      <th><?=l('Software Name')?></th>
      <th><?=l('Version')?></th>
      <th class="hidden-xs"><?=l('Registration No.')?></th>
      <th class="hidden-xs"><?=l('ID No.')?></th>
      <th><abbr title="<?=l('Supported')?>"><?=l('Spt.')?></abbr></th>
      <th><?=l('Delete')?></th>
    </tr>
  </table>
</div>

<div class="table-responsive" id="hardware_div">
  <table class="table table-hover table-condensed" id="hardware_list" > <!-- Hardware List -->
    <tr>
      <th class="hidden-xs"><?=l('Hardware Type')?></th>
      <th><?=l('Make')?></th>
      <th><?=l('Model')?></th>
      <th class="hidden-xs"><?=l('Serial No.')?></th>
      <th class="hidden-xs"><?=l('ID No.')?></th>
      <th><abbr title="<?=l('Supported')?>"><?=l('Spt.')?></abbr></th>
      <th><?=l('Delete')?></th>
    </tr>
  </table>
</div>
