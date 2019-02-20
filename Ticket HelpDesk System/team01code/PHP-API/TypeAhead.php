<?php
include '../PHP-DB/api.php';
include_once dirname(__FILE__, 2) . '/PHP-DB/api.php';
if(!isset($_GET['fill_field']) || !$_GET['page'])
  http_response_code(400); // Bad Request
else
{
  $fieldToFill = $_GET['fill_field'];
  switch ($_GET['page']) {
  case 'call': echo json_encode(\db\suggestions_in_call($fieldToFill, $_GET)); break;
  case 'software': echo json_encode(\db\suggestions_in_software($fieldToFill, $_GET)); break;
  case 'hardware': echo json_encode(\db\suggestions_in_hardware($fieldToFill, $_GET)); break;
  default: echo null;
  }
}

?>
