<div class="container-fluid">
  <div class="row">
      <div class="input-group">
        <span class="input-group-btn">
          <button class="btn btn-default" type="button"><span  class="glyphicon glyphicon-search"></span></button>
        </span>
        <input id="search" type="text" class="input-group form-control" placeholder="<?=l('Search for Ticket...')?>">
      </div>
  </div>
  <div class="container-fluid" id="results_box">
  </div>
</div>

<script type="text/javascript">
  function search(query, callback)
  {
    var data = {'query' : query};
    if (typeof fill_category_and_specialist_for_search === "function")
      fill_category_and_specialist_for_search(data);

    $.ajax(
    {
      method : 'GET',
      data : data,
      url : 'PHP-API/DynamicSearch.php',
      success : function (response)
      {
        console.log(data);
        console.log(JSON.parse(response));
        callback(JSON.parse(response));
      }
    });
  }

  function get_url_for(call_id, selected_ticket)
  {
    if (typeof override_get_url_for === 'function')
    {
      return override_get_url_for(call_id, selected_ticket);
    }
    else return "home.php";
  }

  function processSearchResults(results)
  {
    var call_id = "<?= isset($call_id) ? $call_id : '' ?>";
    $('#results_box').html('');
    for (var i in results)
    {
      var ticket_id = results[i].ticket_id;
      var date = results[i].first_mentioned;
      var notes = results[i].notes;
      var result_html = '<a class="plain result_box" href="' + get_url_for(call_id, ticket_id) + '">' +
        '<div class="col-xs-12 col-sm-12 col-md-6">' +
        ' <h3><strong class="text-danger"><?=l('Ticket #')?>' + ticket_id + '<small> ' + date + ' </small></strong></h3>' +
        ' <p>' + notes + '</p>' +
        '</div>' +
      '</a>';
      $('#results_box').append(result_html);
    }
  }

  $(document).ready(function()
  {
    search("", processSearchResults);
  });

  $("#search").keyup(function()
  {
    search($("#search").val(), processSearchResults);
  });
</script>
