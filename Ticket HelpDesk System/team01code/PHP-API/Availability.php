<?php
include_once '../login-toolkit/login_setup.php';
include '../PHP-DB/api.php';
if (isset($_POST["specialist_id"]) && isset($_POST["dates"]))
{
  \db\delete_unavailabilies_for_specialist($_POST["specialist_id"]);
  $dates = explode(",", $_POST["dates"]);
  if (sizeof($dates) > 0)
  {
    foreach ($dates as $date)
    {
      $date_obj = \db\meta_s2d($date);
      \db\create_unavailability($_POST["specialist_id"], $date_obj);
    }
  }
  $dates = \db\get_unavailities_for_specialist($_POST["specialist_id"]);
  echo json_encode($dates);
}
elseif (isset($_GET["specialist_id"]))
{
  $dates = \db\get_unavailities_for_specialist($_GET["specialist_id"]);
  echo json_encode($dates);
}
else http_response_code(400);
?>
