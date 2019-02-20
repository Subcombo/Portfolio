<?php
  include_once 'login-toolkit/login_setup.php';
  include_once 'PHP-API/Localisation.php';
  include_once 'PHP-DB/api.php';

  function nset($post_field)
  {
    return !isset($_POST[$post_field]);
  }

  // POST comes in the form of Array (
  //   [first_name] => Andrea
  //   [last_name] => Weissmuller
  //   [employee_id] => 9
  //   [phone_no] => +49 30 3167 3273
  //   [email] => aweissmuller@make-it-all.de
  //   [job_title] => Manager
  //   [country] => Germany
  //   [department] => EU HQ [DE1]
  //   [submit] => existing_ticket
  //   )

  if (nset('first_name') || nset('last_name') || nset('phone_no') || nset('email') || nset('job_title') || nset('department'))
  {
    header("Location: " . $user_home);
    exit();
  }

  $first_name = $_POST["first_name"];
  $last_name = $_POST["last_name"];
  $employee_id = $_POST["employee_id"];
  $phone_no = $_POST["phone_no"];
  $email = $_POST["email"];
  $job_title = $_POST["job_title"];
  $department = $_POST["department"];
  $action = $_POST["submit"];

  preg_match("/.*\[(.*)\]/", $department, $matchings);
  $dept_code = $matchings[1];

  if ($employee_id === '')
  {
    // Create a new employee entry
    $employee_id = \db\create_employee($job_title, $phone_no, $first_name, $last_name, $email, $dept_code);
  }
  elseif (\db\get_employee($employee_id) != null)
  {
    // Amends the existing entry
    \db\amend_employee($employee_id, $job_title, $phone_no, $first_name, $last_name, $email, $dept_code);
  }
  else
  {
    // No employee of given ID number
    header("Location: " . $user_home . "?error=" . urlencode(l('An error occurred while creating the call. No employee with such ID exists.')));
    exit();
  }

  if ($employee_id === null)
  {
    // An error occurred that shouldn't have happened
    header("Location: " . $user_home . "?error=" . urlencode(l('An error occurred while creating the call.')));
    exit();
  }

  $call_id = \db\create_call(new DateTime(), $user_id, $employee_id);

  if ($call_id == null)
  {
    // An error occurred that shouldn't have happened
    header("Location: " . $user_home . "?error=" . urlencode(l('An error occurred while creating the call.')));
    exit();
  }

  if ($action == "end_call")
  {
    header("Location: " . $user_home);
  }
  else if ($action == "existing_ticket")
  {
    header("Location: call.php?call_id=" . urlencode($call_id));
  }
  else if ($action == "new_ticket")
  {
    header("Location: call.php?action=new_ticket&call_id=" . urlencode($call_id));
  }
?>
