<script type="text/javascript">
function refresh_priority_level()
{
  $.ajax({
    url: 'PHP-API/TicketDetails.php',
    type: 'GET',
    data: { 'priority_for_ticket' : <?=$this_ticket?> },
    success: function(response)
    {
      var priority = JSON.parse(response)['priority'];
      $("#priority_selection").selectpicker('val', priority);
    }
  });
}

$(document).ready(function()
{
  $("#priority_selection").change(function()
  {
    var priority = $(this).selectpicker('val');
    $.ajax({
      url: 'PHP-API/TicketDetails.php',
      type: 'POST',
      data: { 'ticket_id' : <?=$this_ticket?>, 'set_priority' : priority },
      success: function(response)
      {
        refresh_priority_level();
      }
    });
  });
});
</script>
