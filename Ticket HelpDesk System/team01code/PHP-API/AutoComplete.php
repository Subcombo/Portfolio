<?php
include_once '../login-toolkit/login_setup.php';
include_once dirname(__FILE__, 2) . '/PHP-DB/api.php';

if (!isset($_GET['page'])) http_response_code(400); // Bad Request

switch ($_GET['page']) {
case 'call': echo json_encode(\db\autofill_call($_GET)); break;
case 'software': echo json_encode(\db\autofill_software($_GET)); break;
case 'hardware': echo json_encode(\db\autofill_hardware($_GET)); break;
default: http_response_code(400);
}

?>
