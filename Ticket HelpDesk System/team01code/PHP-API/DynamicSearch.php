<?php
include_once '../login-toolkit/login_setup.php';
include_once dirname(__FILE__, 2) . '/PHP-DB/api.php';
include_once dirname(__FILE__, 2) . '/PHP-API/Localisation.php';
if(!isset($_GET['query']))
  http_response_code(400); // Bad Request
else
{
  $results = [];
  if (isset($_GET['category']) && ($_GET['specialist_id']))
    $results = \db\search_for_problems($_GET['query'], $_GET['category'], $_GET['specialist_id']);
  else
    $results = \db\search_for_problems($_GET['query']);

  foreach ($results as &$result)
  {
    $result["first_mentioned"] = ldt($result["first_mentioned"]);
  }
  echo json_encode($results);
}
?>
