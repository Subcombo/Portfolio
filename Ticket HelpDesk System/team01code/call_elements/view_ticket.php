<?php
  include_once 'login-toolkit/login_setup.php';
  if (!isset($this_ticket) || !isset($ticket_details))
    die('$this_ticket not set.');
?>

<form class="form-horizontal">
  <div class="col-md-6">
    <div class="form-group"> <!-- Problem Tag -->
      <label class="col-md-4 text-right problem_details" for="problem_tags"><?=l('Problem Tags:')?></label>
      <div class="col-md-8">
        <p id="problem_tags"><?php
          echo implode(" / ", \db\meta_get_problem_tag_path_array($ticket_details["problem_tag_id"]));
        ?></p>
      </div>
    </div>

    <div class="form-group"> <!-- Notes -->
      <label class="col-md-4 text-right problem_details" for="notes"><?=l('Notes:')?></label>
      <div class="col-md-8">
        <p id="notes"><?=$ticket_details["notes"]?></p>
      </div>
    </div>

    <script type="text/javascript">
    function post_comment(callback)
    {
      var comment_value = $("#comment").val();
      $.ajax(
      {
        'method' : 'POST',
        'data' : {
          'ticket_id' : <?=$this_ticket?>,
          'comment_content' : comment_value
        },
        'url' : 'PHP-API/Comments.php',
        'success' : function(response)
        {
          $("#comment").val("");
          refresh_comments();
          if (typeof callback === 'function')
            callback();
        }
      });
    }

    function build_comment(content, timestamp, author)
    {
      return $("<div class='comment_entry'>").css('margin-bottom', '8px')
        .append($("<p>").css('margin', '0px 0px 0px').html(content))
        .append($("<small>").html('<?=l("by")?> <b>' + author + '</b> <?=l("on")?> ' + timestamp));
    }

    function refresh_comments()
    {
      $.ajax(
      {
        'method' : 'GET',
        'data' : { 'ticket_id' : <?=$this_ticket?> },
        'url' : 'PHP-API/Comments.php',
        'success' : function(response)
        {
          var data = JSON.parse(response);
          $("#comments_box > .comment_entry").remove();
          for (var idx in data)
          {
            var comment = data[idx];
            var comment_box = build_comment(
              comment.comment,
              comment.comment_timestamp,
              comment.reporting_user_name
            );
            comment_box.insertBefore($("#comment"));
          }
        }
      });
    }

    $(document).ready(function()
    {
      $("#add_comment").click(post_comment);
      refresh_comments();
    });
    </script>

    <div class="form-group"> <!-- Notes -->
      <label class="col-md-4 text-right" for="comments_box"><?=l("Comments:")?></label>
      <div class="col-md-8" id="comments_box">
        <textarea id="comment" name="comment" placeholder="" class="form-control input-md"></textarea>
      </div>
    </div>
    <div class="text-right">
      <select class="selectpicker" data-style="btn-default btn-sm" data-width="fit" id="priority_selection">
        <option value="0" data-icon="glyphicon-chevron-up" <?= $ticket_details["priority"] === \db\priority_level::High ? "selected" : "" ?>><?=l('High priority')?></option>
        <option value="1" data-icon="glyphicon-record" <?= $ticket_details["priority"] === \db\priority_level::Normal ? "selected" : "" ?>><?=l('Normal priority')?></option>
        <option value="2" data-icon="glyphicon-chevron-down" <?= $ticket_details["priority"] === \db\priority_level::Low ? "selected" : "" ?>><?=l('Low priority')?></option>
      </select>
      <button type="button" class="btn btn-primary btn-sm" id="add_comment">
        <span class="glyphicon glyphicon-send"></span>&ensp;<?=l("Add comment")?>
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

  <div class="col-md-6">
    <hr class="visible-sm visible-xs" />
    <?php
      if ($user_id == $ticket_details["assigned_specialist"] || $user_type === \db\login_status::Operator)
      {
    ?>
      <?php include 'similar.php' ?>
      <hr />
      <div class="form-group" id="refer"> <!-- Send to specialist -->
        <label class="col-md-4 control-label" for="notes"><?=l('Transfer to Specialist:')?></label>
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
    <hr />

    <?php } ?>

    <?php if (!isset($HIDE_DELETE_BUTTON)) { ?>
    <script type="text/javascript">
    $(document).ready(function() {
      $("#delete_ticket").click(function()
      {
        if(confirm("<?=l('Are you sure you want to remove this ticket from the call?')?>'"))
        {
          window.location.replace("call.php?call_id=<?=$call_id?>&selected_ticket=<?=$this_ticket?>&action=delete");
        }
      });
    });
    </script>
    <div class="text-right"> <!-- Delete ticket -->
      <a href="" class="btn btn-danger btn-sm" type="button" id="delete_ticket">
        <span class="glyphicon glyphicon-trash"></span> <?=l('Remove this Ticket')?>
      </a>
    </div>
    <hr />
    <?php } ?>

    <?php include 'previous_calls.php' ?>
    <hr />
    <?php include 'solutions.php' ?>

  </div>

  <?php
    if ($user_type === \db\login_status::Specialist
      && $user_id == $ticket_details["assigned_specialist"]
      && $ticket_details["status"] !== \db\status::Closed)
    {
      echo '<hr class="col-md-12"/>';
      include 'solution_input.php';
    }
  ?>
</form>
