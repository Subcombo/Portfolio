<?php
  include_once '../login-toolkit/login_setup.php';
  include '../PHP-DB/api.php';

  function die_with_header(int $http_status_code, string $message = "")
  {
    header('HTTP/1.1 ' . $http_status_code . ' ' . $message);
    die($message);
  }

  ////////////////////////////////////////////////////////////////////
  // GET REQUEST ==> Get equipment for ticket                       //
  ////////////////////////////////////////////////////////////////////
  if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['ticket_id']))
  {
    $sw = \db\get_software_for_ticket($_GET['ticket_id']);
    $hw = \db\get_hardware_for_ticket($_GET['ticket_id']);
    $results = [ "software" => $sw, "hardware" => $hw ];
    echo json_encode($results);
    exit();
  }
  ////////////////////////////////////////////////////////////////////
  // GET REQUEST ==> Get information about the equipment            //
  ////////////////////////////////////////////////////////////////////
  elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['equipment_id']))
  {
    $equipment_id = $_GET['equipment_id'];
    $is_hardware = ($equipment_id % 2 == 0);
    if ($is_hardware)
    {
      $hw = \db\get_hardware_entry($equipment_id);
      if ($hw === null) die_with_header(404, 'Hardware not found');
      $hw['equipment_type'] = 'hardware';
      echo json_encode($hw);
      // returns {equipment_id, type, make, model, serial_no, status}
    }
    else
    {
      $sw = \db\get_software_entry($equipment_id);
      if ($sw === null) die_with_header(404, 'Software not found');
      $hw['equipment_type'] = 'software';
      echo json_encode($sw);
      // returns {equipment_id, name, version, registration_no, status}
    }
  }
  ////////////////////////////////////////////////////////////////////
  // POST REQUEST ==> Create new piece of equipment in the database //
  ////////////////////////////////////////////////////////////////////
  elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['equipment_type']))
  {
    $equipment_type = $_POST['equipment_type'];
    $id = null;
    if ($equipment_type == 'software')
    {
      if (null !== ($name = $_POST['name'])
        && null !== ($version = $_POST['version'])
        && null !== ($reg_no = $_POST['registration_no'])
        && null !== ($status = $_POST['status']))
      {
        $id = \db\create_software_entry($name, $version, $reg_no, $status);
      }
      else die_with_header(400, 'Field missing out of name, version, registration_no, status, equipment_type');
    }
    elseif ($equipment_type == 'hardware')
    {
      if (null !== ($type = $_POST['type'])
        && null !== ($make = $_POST['make'])
        && null !== ($model = $_POST['model'])
        && null !== ($serial = $_POST['serial_no'])
        && null !== ($status = $_POST['status']))
      {
        $id = \db\create_hardware_entry($type, $make, $model, $serial, $status);
      }
      else die_with_header(400, 'Field missing out of type, make, model, serial_no, status, equipment_type');
    }
    else die_with_header(400, 'Only allowed equipment types are software and hardware');

    if ($id === null)
    {
      die_with_header(500, 'Server error, entry not created');
    }
    elseif (isset($_POST['ticket_id']))
    {
      \db\assign_equipment_to_ticket($_POST['ticket_id'], $id);
    }
    echo json_encode([ 'equipment_id' => $id ]);
  }
  ///////////////////////////////////////////////////////////////////
  // PUT REQUEST ==> Update an equipment record in the database    //
  ///////////////////////////////////////////////////////////////////
  elseif ($_SERVER['REQUEST_METHOD'] == 'PUT')
  {
    parse_str(file_get_contents("php://input"), $put_vars);
    error_log(json_encode($put_vars));
    if (isset($put_vars['equipment_type'])
      && isset($put_vars['equipment_id']))
    {
      $equipment_type = $put_vars['equipment_type'];
      $id = $put_vars['equipment_id'];
      if ($equipment_type == 'software')
      {
        $name = isset($put_vars['name']) ? $put_vars['name'] : null;
        $version = isset($put_vars['version']) ? $put_vars['version'] : null;
        $reg_no = isset($put_vars['registration_no']) ? $put_vars['registration_no'] : null;
        $status = isset($put_vars['status']) ? $put_vars['status'] : null;
        \db\amend_software_entry($id, $name, $version, $reg_no, $status);
      }
      elseif ($equipment_type == 'hardware')
      {
        $type = isset($put_vars['type']) ? $put_vars['type'] : null;
        $make = isset($put_vars['make']) ? $put_vars['make'] : null;
        $model = isset($put_vars['model']) ? $put_vars['model'] : null;
        $serial = isset($put_vars['serial_no']) ? $put_vars['serial_no'] : null;
        $status = isset($put_vars['status']) ? $put_vars['status'] : null;
        \db\amend_hardware_entry($id, $type, $make, $model, $serial, $status);
      }
      else
      {
        die_with_header(400, 'Only allowed equipment types are software and hardware');
      }
    }
    ///////////////////////////////////////////////////////////////////
    // PUT REQUEST ==> Bind equipment record to a ticket             //
    ///////////////////////////////////////////////////////////////////
    elseif (isset($put_vars['equipment_id'])
      && isset($put_vars['ticket_id']))
    {
      \db\assign_equipment_to_ticket($put_vars['ticket_id'], $put_vars['equipment_id']);
    }
    else
    {
      die_with_header('400', 'Request needs either (equipment_type, equipment_id) or (equipment_id, ticket_id)');
    }
  }
  ////////////////////////////////////////////////////////////////////
  // DELETE REQUEST ==> Remove a piece of equipment from the ticket //
  ////////////////////////////////////////////////////////////////////
  elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE')
  {
    parse_str(file_get_contents("php://input"), $del_vars);
    if (isset($del_vars['equipment_id']) && isset($del_vars['ticket_id']))
    {
      \db\unbind_equipment_from_ticket($del_vars['ticket_id'], $del_vars['equipment_id']);
    }
    ////////////////////////////////////////////////////////////////////
    // DELETE REQUEST ==> Remove a piece of equipment from the ticket //
    ////////////////////////////////////////////////////////////////////
    elseif (isset($del_vars['equipment_id']))
    {
      \db\delete_equipment_entry($del_vars['equipment_id']);
    }
    else die_with_header(400, 'Delete request has to specify equipment_id');
  }
  else
  {
    die_with_header(400, 'Request invalid');
  }
?>
