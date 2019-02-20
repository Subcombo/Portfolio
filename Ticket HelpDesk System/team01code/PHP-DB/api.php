<?php
namespace db;
// VERY IMPORTANT:
// No strict type checking on return, as sometimes null needs to be returned to signify an
// error/no results -- an arrow symbol ``->'' used to show expected return type

// ``ENUM'' DEFINITIONS:
// Ticket priority level
abstract class priority_level
{
  const High = 0;
  const Normal = 1;
  const Low = 2;
}

// Ticket status levels, 1xx are used for filtering search results
abstract class status
{
  // Specialist filtering additional values
  const AllClosed = 101;
  const All = 100;
  // Permitted database values
  const Pending = 2;
  const Open = 1;
  const Closed = 0;
}

// Type of the logged in user, with exception of 0, which means invalid
abstract class login_status
{
  const Specialist = 2;
  const Operator = 1;
  const NoSuchUser = 0;
}

// Marks the support status of equipment
abstract class supported
{
  const No = 0;
  const Yes = 1;
  const Unknown = 2;
}

// FIELDS (defined as strings) valid values:
//  - call_fields       first_name, last_name, employee_id, phone_no, email, department, job_title, country
//  - hardware_fields   type, make, model, serial_no
//  - software_fields   name, version, registration_no

// REMARKS/ASSUMPTIONS:
//  - there are no piece of software and hardware that have the same id,
//    i.e. you can uniquely identify equipment and its type by its id)

function meta_open_db()
{
  $conn = new \mysqli("localhost", "team01", "8a1hsMw5La", "team01");
  if ($conn->connect_errno)
  {
    printf("Database down: %s\n", $mysqli->connect_error);
    exit();
  }
  else
  {
    return $conn;
  }
}

function meta_dt2s(\DateTime $timestamp)
{
  return $timestamp->format('Y-m-d H:i:s');
}

function meta_s2dt(string $timestamp_str)
{
  return \DateTime::createFromFormat('Y-m-d H:i:s', $timestamp_str);
}

function meta_d2s(\DateTime $timestamp)
{
  return $timestamp->format('Y-m-d');
}

function meta_s2d(string $timestamp_str)
{
  return \DateTime::createFromFormat('Y-m-d', $timestamp_str);
}

function meta_escape($query)
{
  return strtr($query, array("\x00" => '\x00', "\n" => '\n', "\r" => '\r', '\\' => '\\\\', "'" => "\'", '"' => '\"', "\x1a" => '\x1a'));
}

function validate_user_credentials(string $user, string $password, &$user_id): int
{
  $query = "SELECT EmployeeID, PasswordHash, 1 FROM Operators WHERE Username = ?
            UNION
            SELECT EmployeeID, PasswordHash, 2 FROM Specialist WHERE Username = ?";
  $conn = meta_open_db();
  $status = login_status::NoSuchUser;
  if ($stmt = $conn->prepare($query));
  {
    $stmt->bind_param("ss", $user, $user);
    $stmt->execute();
    $stmt->bind_result($uid, $hash, $utype);
    if ($stmt->fetch() && password_verify($password, $hash))
    {
      $status = $utype;
      $user_id = $uid;
    }
    $stmt->close();
  }
  $conn->close();
  return $status;
}

// PROBLEM TAGS:
// integer create_problem_tag(string name, integer parent_tag_id)
function create_problem_tag(string $name, int $parent_tag_id) // -> int
{
  $conn = meta_open_db();
  $inserted_id = null;
  if ($stmt = $conn->prepare("INSERT INTO ProblemTag (ParentID, TagName) VALUES (?, ?)"))
  {
    $stmt->bind_param("is", $parent_tag_id, $name);
    if (!$stmt->execute()) return null;
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }
  $conn->close();
  return $inserted_id;
}

// void delete_problem_tag(integer tag_id)
function delete_problem_tag(int $tag_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM ProblemTag WHERE ID = ?"))
  {
    $stmt->bind_param("i", $tag_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// array<integer> search_problem_tags(string query)
function search_problem_tags(string $query) // -> array
{
  $conn = meta_open_db();
  $results = array();
  $regex = "%{$query}%";
  if ($stmt = $conn->prepare("SELECT `ID` FROM `ProblemTag` WHERE `TagName` LIKE ?"))
  {
    $stmt->bind_param("s", $regex);
    $stmt->execute();
    $stmt->bind_result($id);
    while ($stmt->fetch())
    {
      array_push($results, $id);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// hashmap<integer tag_id, string name, integer parent_tag_id> get_problem_tag(integer tag_id)
function get_problem_tag(int $tag_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `TagName`, `ParentID` FROM `ProblemTag` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $tag_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $tag_name, $parent_id);
    while ($stmt->fetch())
    {
      $results["tag_id"] = $id;
      $results["parent_tag_id"] = $parent_id;
      $results["name"] = $tag_name;
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// CALLS & PROBLEMS:

// integer create_call(timestamp call_timestamp, emp_id operator, emp_id reporting_employee)
function create_call(\DateTime $call_timestamp, int $operator, int $reporting_employee) // -> int
{
  $conn = meta_open_db();
  $inserted_id = null;
  $formatted = meta_dt2s($call_timestamp);
  if ($stmt = $conn->prepare("INSERT INTO `Call`(`Timestamp`, `OperatorEmployeeID`, `EmployeeID`) VALUES (?, ?, ?)"))
  {
    $stmt->bind_param("sii", $formatted, $operator, $reporting_employee);
    if (!$stmt->execute()) return null;
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }
  $conn->close();
  return $inserted_id;
}

// integer assign_ticket_to_call(integer ticket_id, integer call_id)
function assign_ticket_to_call(int $ticket_id, int $call_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `ProblemReport` (`CallID`, `ProblemTicketID`) VALUES (?, ?)"))
  {
    $stmt->bind_param("ii", $call_id, $ticket_id);
    if (!$stmt->execute()) die("Fatal Error: Cannot create a call binding for $inserted_id.");
    // TODO: Cleanup needed after failure
    $stmt->close();
  }
  $conn->close();
}

// integer unbind_ticket_from_call(integer ticket_id, integer call_id)
function unbind_ticket_from_call(int $ticket_id, int $call_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `ProblemReport` WHERE `CallID`=? AND `ProblemTicketID`=?"))
  {
    $stmt->bind_param("ii", $call_id, $ticket_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// integer create_ticket(integer call_id, priority_level priority, string notes, integer problem_tag_id)
function create_ticket(int $call_id, int $priority, string $notes, int $problem_tag_id) // -> int
{
  $conn = meta_open_db();
  $inserted_id = null;
  $status = status::Open;
  if ($stmt = $conn->prepare("INSERT INTO `Problem` (`Open`, `Priority`, `Notes`, `CallID`, `ProblemTagID`) VALUES (?, ?, ?, ?, ?)"))
  {
    $stmt->bind_param("iisis", $status, $priority, $notes, $call_id, $problem_tag_id);
    if (!$stmt->execute());
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }
  $conn->close();

  assign_ticket_to_call($inserted_id, $call_id);
  return $inserted_id;
}

// void delete_call(integer call_id)
function delete_call(int $call_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Call` WHERE ID = ?"))
  {
    $stmt->bind_param("i", $call_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// array<int> get_all_calls()
function get_all_calls()
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID` FROM `Call` WHERE 1"))
  {
    $stmt->execute();
    $stmt->bind_result($id);
    while ($stmt->fetch())
    {
      array_push($results, $id);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<int> get_all_tickets()
function get_all_tickets()
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `TicketID` FROM `Problem` WHERE 1"))
  {
    $stmt->execute();
    $stmt->bind_result($id);
    while ($stmt->fetch())
    {
      array_push($results, $id);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// void delete_ticket(integer ticket_id)
function delete_ticket(int $ticket_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Problem` WHERE TicketID = ?"))
  {
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void amend_ticket(integer ticket_id, priority_level priority, string notes, int problem_tag_id, status status, emp_id specialist)
// * when given null, doesn't overwrite -- to delete specialist use -1
function amend_ticket(int $ticket_id, $priority, $notes, $problem_tag_id, $status, $specialist)
{
  $current_state = get_ticket($ticket_id);
  if (empty($current_state)) return null;
  if ($priority === null) $priority = $current_state["priority"];
  if ($notes === null) $notes = $current_state["notes"];
  if ($problem_tag_id === null) $problem_tag_id = $current_state["problem_tag_id"];
  if ($status === null) $status = $current_state["status"];
  if ($specialist === null) $specialist = $current_state["assigned_specialist"];

  $conn = meta_open_db();
  if ($specialist === -1 && $stmt = $conn->prepare("UPDATE `Problem` SET `Notes`=?, `Priority`=?, `ProblemTagID`=?, `Open`=?, `SpecialistEmployeeID`=NULL WHERE `TicketID`=?"))
  {
    $stmt->bind_param("siiii", $notes, $priority, $problem_tag_id, $status, $ticket_id);
    $stmt->execute();
    $stmt->close();
  }
  else if ($stmt = $conn->prepare("UPDATE `Problem` SET `Notes`=?, `Priority`=?, `ProblemTagID`=?, `Open`=?, `SpecialistEmployeeID`=? WHERE `TicketID`=?"))
  {
    $stmt->bind_param("siiiii", $notes, $priority, $problem_tag_id, $status, $specialist, $ticket_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}
// void hashmap<alist_to_tag(integer tag_id, emp_id specialist)
function assign_specialist_to_tag(int $tag_id, int $specialist)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("REPLACE INTO `SpecialistField`(`ProblemTagID`, `SpecialistEmployeeID`) VALUES (?, ?)"))
  {
    $stmt->bind_param("ii", $tag_id, $specialist);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void unbind_specialist_from_tag(integer tag_id, emp_id specialist)
function unbind_specialist_from_tag(integer $tag_id, int $specialist)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `SpecialistField` WHERE `ProblemTagID`=? AND `SpecialistEmployeeID`=?"))
  {
    $stmt->bind_param("ii", $tag_id, $specialist);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void set_ticket_status(integer ticket_id, status status)
function set_ticket_status(int $ticket_id, int $status)
{
  amend_ticket($ticket_id, null, null, null, $status, null);
}

// hashmap<integer call_id, timestamp call_timestamp, emp_id operator, emp_id reporting_employee> get_call(integer call_id)
function get_call(int $call_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Timestamp`, `OperatorEmployeeID`, `EmployeeID` FROM `Call` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $call_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $timestamp, $operator, $employee);
    while ($stmt->fetch())
    {
      $results["call_id"] = $id;
      $results["call_timestamp"] = meta_s2dt($timestamp);
      $results["operator"] = $operator;
      $results["reporting_employee"] = $employee;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// hashmap<integer ticket_id,
//       integer call_id,
//       priority_level priority,
//       string notes,
//       integer problem_tag_id,
//       emp_id assigned_specialist,
//       status status,
//       timestamp first_mentioned> get_ticket(integer ticket_id)
function get_ticket(int $ticket_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `TicketID`, `Notes`, `Priority`, `ProblemTagID`, `CallID`, `Open`, `SpecialistEmployeeID`, `Timestamp` FROM `Problem` LEFT JOIN `Call` ON `CallID`=`ID` WHERE `TicketID`=?"))
  {
    $stmt->bind_param("i", $ticket_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $notes, $priority, $problem_tag_id, $call_id, $open, $specialist_employee_id, $fm);
    while ($stmt->fetch())
    {
      $results["ticket_id"] = $id;
      $results["call_id"] = $call_id;
      $results["priority"] = $priority;
      $results["notes"] = $notes;
      $results["problem_tag_id"] = $problem_tag_id;
      $results["assigned_specialist"] = $specialist_employee_id;
      $results["status"] = $open;
      $results["first_mentioned"] = meta_s2dt($fm);
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// COMMENTS:
// integer create_comment(integer ticket_id, timestamp comment_timestamp, string comment, emp_id reporting_user)
function create_comment(int $ticket_id, \DateTime $comment_timestamp, string $comment, string $reporting_user_id) // -> int
{
  $conn = meta_open_db();
  $inserted_id = null;
  $formatted = meta_dt2s($comment_timestamp);
  if ($stmt = $conn->prepare("INSERT INTO `Comment`(`Comment`, `Timestamp`, `EmployeeID`, `ProblemTicketID`) VALUES (?,?,?,?)"))
  {
    $stmt->bind_param("ssii", $comment, $formatted, $reporting_user_id, $ticket_id);
    if (!$stmt->execute()) return null;
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }
  $conn->close();
  return $inserted_id;
}

// void delete_comment(integer comment_id)
function delete_comment(int $comment_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Comment` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// hashmap<integer comment_id, integer ticket_id, timestamp comment_timestamp, string comment, emp_id reporting_user_id> get_comment(integer comment_id)
function get_comment(int $comment_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Comment`, `Timestamp`, `EmployeeID`, `ProblemTicketID` FROM `Comment` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $comment_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $comment, $timestamp, $emp_id, $ticket_id);
    while ($stmt->fetch())
    {
      $results["comment_id"] = $id;
      $results["ticket_id"] = $ticket_id;
      $results["comment_timestamp"] = meta_s2dt($timestamp);
      $results["comment"] = $comment;
      $results["reporting_user_id"] = $emp_id;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// SOLUTIONS:
// integer create_solution(integer ticket_id, emp_id authoring_specialist, string description, timestamp solution_timestamp)
function create_solution(int $ticket_id, string $authoring_specialist, string $description, \DateTime $solution_timestamp) // -> int
{
  $conn = meta_open_db();
  $inserted_id = null;
  $formatted = meta_dt2s($solution_timestamp);
  if ($stmt = $conn->prepare("INSERT INTO `Solution`(`Description`, `Timestamp`, `ProblemTicketID`, `SpecialistEmployeeID`) VALUES (?,?,?,?)"))
  {
    $stmt->bind_param("ssii", $description, $formatted, $ticket_id, $authoring_specialist);
    if (!$stmt->execute()) return null;
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }

  if ($stmt = $conn->prepare("INSERT INTO `ProblemSolutions` (`SolutionID`, `ProblemID`) VALUES (?, ?)"))
  {
    $stmt->bind_param("ii", $inserted_id, $ticket_id);
    if (!$stmt->execute()) die("Fatal Error: Cannot create a call binding for $inserted_id.");
    // TODO: Cleanup needed after failure
    $stmt->close();
  }
  $conn->close();

  return $inserted_id;
}

// void delete_solution(integer solution_id)
function delete_solution(int $solution_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Solution` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void amend_solution(integer solution_id, string description, timestamp solution_timestamp)
function amend_solution(int $solution_id, string $description)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `Solution` SET `Description`=? WHERE `ID`=?"))
  {
    $stmt->bind_param("si", $description, $solution_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// hashmap<integer solution_id, integer ticket_id, emp_id authoring_specialist, string description, timestamp solution_timestamp> get_solution(integer solution_id)
function get_solution(int $solution_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Description`, `Timestamp`, `ProblemTicketID`, `SpecialistEmployeeID` FROM `Solution` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $solution_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $desc, $timestamp, $ticket_id, $spec_id);
    while ($stmt->fetch())
    {
      $results["solution_id"] = $id;
      $results["ticket_id"] = $ticket_id;
      $results["solution_timestamp"] = meta_s2dt($timestamp);
      $results["description"] = $desc;
      $results["authoring_specialist"] = $spec_id;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// void assign_solution_to_specialist(integer solution_id, integer $specialist_id)
function assign_solution_to_specialist(int $solution_id, int $specialist_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `Solution` SET `SpecialistEmployeeID`=? WHERE `ID`=?"))
  {
    $stmt->bind_param("ii", $specialist_id, $solution_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void assign_solution_to_ticket(integer solution_id, integer ticket_id)
function assign_solution_to_ticket(int $solution_id, int $ticket_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `ProblemSolutions` (`SolutionID`, `ProblemID`) VALUES (?, ?)"))
  {
    $stmt->bind_param("ii", $solution_id, $ticket_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void unbind_solution_from_ticket(integer solution_id, integer ticket_id)*
function unbind_solution_from_ticket(int $solution_id, int $ticket_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `ProblemSolutions` WHERE `SolutionID`=? AND `ProblemID`=?"))
  {
    $stmt->bind_param("ii", $solution_id, $ticket_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// EQUIPMENT:
// integer create_hardware_entry(string type, string make, string model, string serial_no, supported status = unknown)
function create_hardware_entry(string $type, string $make, string $model, string $serial_no, int $status) // -> integer
{
  $inserted_id = null;
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `HardwareEquipment`(`Type`, `Make`, `Model`, `SerialNumber`, `Supported`) VALUES (?,?,?,?,?)"))
  {
    $stmt->bind_param("ssssi", $type, $make, $model, $serial_no, $status);
    if (!$stmt->execute()) return null;
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }
  $conn->close();
  return (2 * $inserted_id);
}

// integer create_software_entry(string name, string version, string registration_no, supported status = unknown)
function create_software_entry(string $name, string $version, string $registration_no, int $status) // -> integer
{
  $inserted_id = null;
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `SoftwareEquipment`(`Name`, `Version`, `RegistrationNumber`, `Supported`) VALUES (?,?,?,?)"))
  {
    $stmt->bind_param("sssi", $name, $version, $registration_no, $status);
    if (!$stmt->execute()) return null;
    $inserted_id = $conn->insert_id;
    $stmt->close();
  }
  $conn->close();
  return (2 * $inserted_id + 1);
}

// void delete_equipment_entry(integer equipment_id, bool $hardware)*
function delete_equipment_entry(int $equipment_id)
{
  $tablename = ($equipment_id % 2 == 0) ? "HardwareEquipment" : "SoftwareEquipment";
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `$tablename` WHERE `ID`=?"))
  {
    $raw_id = intdiv($equipment_id, 2);
    $stmt->bind_param("i", $raw_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void set_supported_status(integer equipment_id, supported status)
function set_supported_status(int $equipment_id, int $status)
{
  $tablename = ($equipment_id % 2 == 0) ? "HardwareEquipment" : "SoftwareEquipment";
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `$tablename` SET `Supported`=? WHERE `ID`=?"))
  {
    $raw_id = intdiv($equipment_id, 2);
    $stmt->bind_param("ii", $status, $raw_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// hashmap<integer equipment_id, string type, string make, string model, string serial_no, supported status> get_hardware_entry(integer equipment_id)
function get_hardware_entry(int $equipment_id) // -> array
{
  $equipment_id = intdiv($equipment_id, 2);
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Type`, `Make`, `Model`, `SerialNumber`, `Supported` FROM `HardwareEquipment` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $equipment_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $type, $make, $model, $serial_no, $status);
    while ($stmt->fetch())
    {
      $results["equipment_id"] = 2 * $id;
      $results["type"] = $type;
      $results["make"] = $make;
      $results["model"] = $model;
      $results["serial_no"] = $serial_no;
      $results["status"] = $status;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// hashmap<integer equipment_id, string name, string version, string registration_no, supported status> get_software_entry(integer equipment_id)
function get_software_entry(int $equipment_id) // -> array
{
  $equipment_id = intdiv($equipment_id, 2);
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Name`, `Version`, `RegistrationNumber`, `Supported` FROM `SoftwareEquipment` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $equipment_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $name, $version, $registration_no, $status);
    while ($stmt->fetch())
    {
      $results["equipment_id"] = 2 * $id + 1;
      $results["name"] = $name;
      $results["version"] = $version;
      $results["registration_no"] = $registration_no;
      $results["status"] = $status;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// array<hashmap<integer equipment_id, string type, string make, string model, string serial_no, supported status>> get_hardware_for_ticket(integer ticket_id)
function get_hardware_for_ticket(int $ticket_id)
{
  $conn = meta_open_db();
  $results2 = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Type`, `Make`, `Model`, `SerialNumber`, `Supported`
                              FROM `HardwareEquipment`
                              RIGHT JOIN `ProblemHardware` ON `HardwareEquipmentID`=`ID`
                              WHERE `ProblemTicketID`=?"))
  {
    $stmt->bind_param("i", $ticket_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $type, $make, $model, $serial_no, $status);
    while ($stmt->fetch())
    {
      $results = array();
      $results["equipment_id"] = 2 * $id;
      $results["type"] = $type;
      $results["make"] = $make;
      $results["model"] = $model;
      $results["serial_no"] = $serial_no;
      $results["status"] = $status;
      array_push($results2, $results);
    }
    $stmt->close();
  }
  $conn->close();
  return $results2;
}

// array<hashmap<integer equipment_id, string name, string version, string registration_no, supported status>> get_software_for_ticket(integer equipment_id)
function get_software_for_ticket(int $ticket_id) // -> array
{
  $conn = meta_open_db();
  $results2 = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `Name`, `Version`, `RegistrationNumber`, `Supported`
                              FROM `SoftwareEquipment`
                              RIGHT JOIN `ProblemSoftware` ON `SoftwareEquipmentID`=`ID`
                              WHERE `ProblemTicketID`=?"))
  {
    $stmt->bind_param("i", $ticket_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $name, $version, $registration_no, $status);
    while ($stmt->fetch())
    {
      $results = array();
      $results["equipment_id"] = 2 * $id + 1;
      $results["name"] = $name;
      $results["version"] = $version;
      $results["registration_no"] = $registration_no;
      $results["status"] = $status;
      array_push($results2, $results);
    }
    $stmt->close();
  }
  $conn->close();
  return $results2;
}

// void amend_hardware_entry(integer equipment_id, string type, string make, string model, string serial_no, supported status = unknown)
//  * if null -- no change
function amend_hardware_entry(int $equipment_id, $type, $make, $model, $serial_no, $status)
{
  $current_state = get_hardware_entry($equipment_id);
  $equipment_id = intdiv($equipment_id, 2);

  if (empty($current_state)) return null;
  if ($type === null) $type = $current_state["type"];
  if ($make === null) $make = $current_state["make"];
  if ($model === null) $model = $current_state["model"];
  if ($serial_no === null) $serial_no = $current_state["serial_no"];
  if ($status === null) $status = $current_state["status"];

  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `HardwareEquipment` SET `Type`=?,`Make`=?,`Model`=?,`SerialNumber`=?,`Supported`=? WHERE `ID`=?"))
  {
    $stmt->bind_param("ssssii", $type, $make, $model, $serial_no, $status, $equipment_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void amend_software_entry(integer equipment_id, string name, string version, string registration_no, supported status = unknown)
//  * if null -- no change
function amend_software_entry(int $equipment_id, $name, $version, $registration_no, $status)
{
  $current_state = get_software_entry($equipment_id);
  $equipment_id = intdiv($equipment_id, 2);

  if (empty($current_state)) return null;
  if ($name === null) $name = $current_state["name"];
  if ($version === null) $version = $current_state["version"];
  if ($registration_no === null) $registration_no = $current_state["registration_no"];
  if ($status === null) $status = $current_state["status"];

  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `SoftwareEquipment` SET `Name`=?,`Version`=?,`RegistrationNumber`=?,`Supported`=? WHERE `ID`=?"))
  {
    $stmt->bind_param("sssii", $name, $version, $registration_no, $status, $equipment_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void assign_equipment_to_ticket(integer ticket_id, integer equipment_id) /* does hw/sw resolution automaticaly */
function assign_equipment_to_ticket(int $ticket_id, int $equipment_id)
{
  $tablename = ($equipment_id % 2 == 0) ? "ProblemHardware" : "ProblemSoftware";
  $equipment_id_fieldname = ($equipment_id % 2 == 0) ? "HardwareEquipmentID" : "SoftwareEquipmentID";
  $equipment_id = intdiv($equipment_id, 2);

  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `$tablename`(`ProblemTicketID`, `$equipment_id_fieldname`) VALUES (?,?)"))
  {
    $stmt->bind_param("ii", $ticket_id, $equipment_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void unbind_equipment_from_ticket(integer ticket_id, integer equipment_id) /* ditto */
function unbind_equipment_from_ticket(int $ticket_id, int $equipment_id)
{
  $tablename = ($equipment_id % 2 == 0) ? "ProblemHardware" : "ProblemSoftware";
  $equipment_id_fieldname = ($equipment_id % 2 == 0) ? "HardwareEquipmentID" : "SoftwareEquipmentID";
  $equipment_id = intdiv($equipment_id, 2);

  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `$tablename` WHERE `ProblemTicketID`=? AND `$equipment_id_fieldname`=?"))
  {
    $stmt->bind_param("ii", $ticket_id, $equipment_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// SPECIALISTS:
// void assign_specialist_to_ticket(integer specialist, integer ticket_id)
function assign_specialist_to_ticket(int $specialist, int $ticket_id)
{
  amend_ticket($ticket_id, null, null, null, null, $specialist);
}

// void unbind_specialist_from_ticket(integer specialist, integer ticket_id)
function unbind_specialist_from_ticket(int $specialist, int $ticket_id)
{
  amend_ticket($ticket_id, null, null, null, null, -1);
}

// void create_unavailability(emp_id specialist, date day)
function create_unavailability(int $specialist, \DateTime $day)
{
  $day_str = meta_d2s($day);
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `Unavailability`(`Date`, `SpecialistEmployeeID`) VALUES (?,?)"))
  {
    $stmt->bind_param("si", $day_str, $specialist);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void delete_unavailabilies_for_specialist(emp_id specialist)
function delete_unavailabilies_for_specialist(int $specialist)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Unavailability` WHERE `SpecialistEmployeeID`=?"))
  {
    $stmt->bind_param("i", $specialist);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// array<hashmap<date day, emp_id specialist>> get_unavailities()
function get_unavailities()
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `Date`, `SpecialistEmployeeID` FROM `Unavailability` WHERE 1"))
  {
    $stmt->execute();
    $stmt->bind_result($day, $specialist);
    while ($stmt->fetch())
    {
      $unavail = array();
      $unavail["day"] = meta_s2d($day);
      $unavail["specialist"] = $specialist;
      array_push($results, $unavail);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<hashmap<date day>> get_unavailities_for_specialist(emp_id specialist_id)
function get_unavailities_for_specialist(int $specialist_id)
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `Date` FROM `Unavailability` WHERE `SpecialistEmployeeID`=?"))
  {
    $stmt->bind_param("i", $specialist_id);
    $stmt->execute();
    $stmt->bind_result($day);
    while ($stmt->fetch())
    {
      array_push($results, $day);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// HR/ADMIN/MANAGEMENT STUFF:
// emp_id create_employee(string job_title, string phone_no, string first_name, string last_name, string email, string dept_code)
function create_employee(string $job_title, string $phone_no, string $first_name, string $last_name, string $email, string $dept_code) // -> string
{
  $inserted_id = null;
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `Employee`(`JobTitle`, `PhoneNumber`, `FirstName`, `LastName`, `Email`, `DepartmentCode`) VALUES (?,?,?,?,?,?)"))
  {
   $stmt->bind_param("ssssss", $job_title, $phone_no, $first_name, $last_name, $email, $dept_code);
   if (!$stmt->execute()) return null;
   $inserted_id = $conn->insert_id;
   $stmt->close();
  }
  $conn->close();
  return $inserted_id;
}

// void amend_employee(emp_id employee_id, string job_title, string phone_no, string first_name, string last_name, string email, string dept_code)
// * as always -- null = no change
function amend_employee(int $employee_id, $job_title, $phone_no, $first_name, $last_name, $email, $dept_code)
{
  $current_state = get_employee($employee_id);

  if (empty($current_state)) return null;
  if ($job_title === null) $job_title = $current_state["job_title"];
  if ($phone_no === null) $phone_no = $current_state["phone_no"];
  if ($first_name === null) $first_name = $current_state["first_name"];
  if ($last_name === null) $last_name = $current_state["last_name"];
  if ($email === null) $email = $current_state["email"];
  if ($dept_code === null) $dept_code = $current_state["dept_code"];

  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `Employee` SET `JobTitle`=?, `PhoneNumber`=?, `FirstName`=?, `LastName`=?, `Email`=?, `DepartmentCode`=? WHERE `ID`=?"))
  {
    $stmt->bind_param("ssssssi", $job_title, $phone_no, $first_name, $last_name, $email, $dept_code, $employee_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void delete_employee(emp_id employee_id)
function delete_employee(int $employee_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Employee` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// hashmap<emp_id employee_id, string job_title, string phone_no, string first_name, string last_name, string email, string dept_code> get_employee(emp_id employee_id)
function get_employee(int $employee_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ID`, `JobTitle`, `PhoneNumber`, `FirstName`, `LastName`, `Email`, `DepartmentCode` FROM `Employee` WHERE `ID`=?"))
  {
    $stmt->bind_param("i", $employee_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $jt, $pn, $fn, $ln, $em, $dc);
    while ($stmt->fetch())
    {
      $results["employee_id"] = $id;
      $results["job_title"] = $jt;
      $results["phone_no"] = $pn;
      $results["first_name"] = $fn;
      $results["last_name"] = $ln;
      $results["email"] = $em;
      $results["dept_code"] = $dc;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// login_status get_user_type(integer employee_id)
function get_user_type(int $employee_id)
{
  $query = "SELECT 1 FROM Operators WHERE EmployeeID = ?
            UNION
            SELECT 2 FROM Specialist WHERE EmployeeID = ?";
  $conn = meta_open_db();
  $status = login_status::NoSuchUser;
  if ($stmt = $conn->prepare($query));
  {
    $stmt->bind_param("ss", $employee_id, $employee_id);
    $stmt->execute();
    $stmt->bind_result($utype);
    if ($stmt->fetch())
    {
      $status = $utype;
    }
    $stmt->close();
  }
  $conn->close();
  return $status;
}

// void change_operator_password(integer employee_id, string password)
function change_operator_password(int $employee_id, string $password)
{
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `Operators` SET `PasswordHash`=? WHERE `EmployeeID`=?"))
  {
    $stmt->bind_param("si", $password_hash, $employee_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void change_specialist_password(integer employee_id, string password)
function change_specialist_password(int $employee_id, string $password)
{
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `Specialist` SET `PasswordHash`=? WHERE `EmployeeID`=?"))
  {
    $stmt->bind_param("si", $password_hash, $employee_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void make_employee_operator(integer employee_id, string username, string password)
function make_employee_operator(int $employee_id, string $username, string $password)
{
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("REPLACE INTO `Operators`(`EmployeeID`, `Username`, `PasswordHash`) VALUES (?,?,?)"))
  {
    $stmt->bind_param("iss", $employee_id, $username, $password_hash);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void make_employee_specialist(integer employee_id, string username, string password)
function make_employee_specialist(int $employee_id, string $username, string $password)
{
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("REPLACE INTO `Specialist`(`EmployeeID`, `Username`, `PasswordHash`) VALUES (?,?,?)"))
  {
    $stmt->bind_param("iss", $employee_id, $username, $password_hash);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void remove_operator_privileges(integer employee_id)
function remove_operator_privileges(int $employee_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Operators` WHERE `EmployeeID`=?"))
  {
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void remove_specialist_privileges(integer employee_id)
function remove_specialist_privileges(int $employee_id)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Specialist` WHERE `EmployeeID`=?"))
  {
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// string create_department(string dept_code, string name, string city, string country, string locale_code)
function create_department(string $dept_code, string $name, string $city, string $country, string $locale_code) // -> string
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("INSERT INTO `Department`(`Code`, `Name`, `Country`, `City`, `LocaleCode`) VALUES (?,?,?,?,?)"))
  {
    $stmt->bind_param("sssss", $dept_code, $name, $country, $city, $locale_code);
    if (!$stmt->execute()) return null;
    $stmt->close();
  }
  $conn->close();
  return $dept_code;
}

// void delete_department(string dept_code)
function delete_department(string $dept_code)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `Department` WHERE `Code`=?"))
  {
    $stmt->bind_param("s", $dept_code);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// void amend_department(string dept_code, string name, string city, string country, string locale_code)
//  * null -- no change
function amend_department(string $dept_code, $name, $city, $country, $locale_code)
{
  $current_state = get_department($dept_code);

  if (empty($current_state)) return null;
  if ($name === null) $name = $current_state["name"];
  if ($city === null) $city = $current_state["city"];
  if ($country === null) $country = $current_state["country"];
  if ($locale_code === null) $locale_code = $current_state["locale_code"];

  $conn = meta_open_db();
  if ($stmt = $conn->prepare("UPDATE `Department` SET `Name`=?, `Country`=?, `City`=?, `LocaleCode`=? WHERE `Code`=?"))
  {
    $stmt->bind_param("sssss", $name, $country, $city, $locale_code, $dept_code);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

// hashmap<string dept_code, string name, string city, string country, string locale_code> get_department(string dept_code)
function get_department(string $dept_code) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `Code`, `Name`, `Country`, `City`, `LocaleCode` FROM `Department` WHERE `Code`=?"))
  {
    $stmt->bind_param("s", $dept_code);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($code, $name, $country, $city, $locale);
    while ($stmt->fetch())
    {
      $results["dept_code"] = $code;
      $results["name"] = $name;
      $results["city"] = $city;
      $results["country"] = $city;
      $results["locale_code"] = $locale;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

// array<string> get_all_departments()
function get_all_departments() // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `Code` FROM `Department` WHERE 1"))
  {
    $stmt->execute();
    $stmt->bind_result($code);
    while ($stmt->fetch())
    {
      array_push($results, $code);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// HIGER-LEVEL/MISC FUNCTIONS:
// array<hashmap<integer comment_id, integer ticket_id, timestamp comment_timestamp, string comment, emp_id reporting_user_id>> comments_on_problem(integer ticket_id)
function comments_on_problem(int $ticket_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `Comment`.`ID`, `Comment`, `Timestamp`, `EmployeeID`, `ProblemTicketID`,
    CONCAT(`Employee`.`FirstName`, ' ', `Employee`.`LastName`)
    FROM `Comment`
    LEFT JOIN `Employee`
    ON `Employee`.`ID`=`EmployeeID`
    WHERE `ProblemTicketID`=?
    ORDER BY `Timestamp` ASC"))
  {
    $stmt->bind_param("i", $ticket_id);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id, $comment, $timestamp, $emp_id, $ticket_id, $name);
    while ($stmt->fetch())
    {
      $result = [];
      $result["comment_id"] = $id;
      $result["ticket_id"] = $ticket_id;
      $result["comment_timestamp"] = meta_s2dt($timestamp);
      $result["comment"] = $comment;
      $result["reporting_user_id"] = $emp_id;
      $result["reporting_user_name"] = $name;
      array_push($results, $result);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<hashmap<solution_id, name, description, timestamp, ticket_id, phone_no, email>> get_solutions_for_problem(integer ticket_id)
function get_solutions_for_problem(int $ticket_id) // -> array
{
  $query = "SELECT
      `SolutionID`,
      CONCAT(`Employee`.`FirstName`, ' ', `Employee`.`LastName`),
      `Description`,
      `Timestamp`,
      `Solution`.`ProblemTicketID`,
      `PhoneNumber`,
      `Email`
    FROM `ProblemSolutions`
    LEFT JOIN `Solution` ON `Solution`.`ID`=`SolutionID`
    LEFT JOIN `Employee` ON `Employee`.`ID`=`Solution`.`SpecialistEmployeeID`
    WHERE `ProblemID`=?";

  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $stmt->bind_result($sid, $nm, $ds, $tm, $tid, $pn, $em);
    while ($stmt->fetch())
    {
      array_push($results, [
        "solution_id" => $sid,
        "name" => $nm,
        "description" => $ds,
        "timestamp" => meta_s2dt($tm),
        "ticket_id" => $tid,
        "phone_no" => $pn,
        "email" => $em
      ]);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<integer> get_problems_for_solution(integer solution_id)
function get_problems_for_solution(int $solution_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ProblemID` FROM `ProblemSolutions` WHERE `SolutionID`=?"))
  {
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $stmt->bind_result($id);
    while ($stmt->fetch())
    {
      array_push($results, $id);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<integer> get_tickets_for_call(integer call_id)
function get_tickets_for_call(int $call_id) // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `ProblemTicketID` FROM `ProblemReport` WHERE `CallID`=?"))
  {
    $stmt->bind_param("i", $call_id);
    $stmt->execute();
    $stmt->bind_result($id);
    while ($stmt->fetch())
    {
      array_push($results, $id);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<hashmap<employee_name, job_title, location, email, phone_no, operator, timestamp>> get_calls_for_ticket(integer ticket_id)
function get_calls_for_ticket(int $ticket_id) // -> array
{
  $query = "SELECT
      `Call`.`ID`,
      CONCAT(employee.`FirstName`, ' ', employee.`LastName`),
      employee.`JobTitle`,
      CONCAT(`Department`.`Name`, ', ', `Department`.`City`),
      employee.`Email`,
      employee.`PhoneNumber`,
      CONCAT(operator.`FirstName`, ' ', operator.`LastName`),
      `Call`.`Timestamp`
  FROM `ProblemReport`
  LEFT JOIN `Call`
  ON `Call`.`ID`=`ProblemReport`.`CallID`
  LEFT JOIN `Employee` AS employee
  ON `Call`.`EmployeeID` = employee.`ID`
  LEFT JOIN `Employee` AS operator
  ON `Call`.`OperatorEmployeeID` = operator.`ID`
  LEFT JOIN `Department`
  ON employee.`DepartmentCode` = `Department`.`Code`
  WHERE `ProblemTicketID`=?";
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $stmt->bind_result($id, $nm, $jt, $dp, $em, $pn, $op, $tm);
    while ($stmt->fetch())
    {
      $results[$id] = [
        "employee_name" => $nm,
        "job_title" => $jt,
        "location" => $dp,
        "email" => $em,
        "phone_no" => $pn,
        "operator" => $op,
        "timestamp" => meta_s2dt($tm)
      ];
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// array<string> get_hashtags()
function get_hashtags() // -> array
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `Text` FROM `Hashtags` WHERE 1"))
  {
    $stmt->execute();
    $stmt->bind_result($hashtag);
    while ($stmt->fetch())
    {
      array_push($results, $hashtag);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

function meta_insert_hashtag(string $hashtag)
{
  // Inserts a hashtag if it already doesn't exist, if it does increments its reference count
  $query = "REPLACE INTO `Hashtags`(`Text`, `RefrenceCount`)
            VALUES (?, IFNULL(
              (
                SELECT `RefrenceCount`
                FROM (SELECT * FROM `Hashtags`) AS ht
                WHERE ht.`Text` = ?
              ), 0) + 1)";
  $conn = meta_open_db();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("ss", $hashtag, $hashtag);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

function meta_delete_hashtag(string $hashtag)
{
  // Decrements reference count on a hashtag, and deletes it if it's zero
  $query_delete = "DELETE FROM `Hashtags` WHERE `Text`=? AND `RefrenceCount`=1";
  $query_decrement = "UPDATE `Hashtags` SET `RefrenceCount`=(`RefrenceCount`-1) WHERE `Text`=?";
  $conn = meta_open_db();
  if ($stmt1 = $conn->prepare($query_delete))
  {
    $stmt1->bind_param("s", $hashtag);
    $stmt1->execute();
    $stmt1->close();
  }
  if ($stmt2 = $conn->prepare($query_decrement))
  {
    $stmt2->bind_param("s", $hashtag);
    $stmt2->execute();
    $stmt2->close();
  }
  $conn->close();
}

function meta_tag_strings_for_specialist(int $specialist_id)
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `TagName`
    FROM `SpecialistField`
    LEFT JOIN `ProblemTag`
    ON `SpecialistField`.`ProblemTagID`=`ProblemTag`.`ID`
    WHERE `SpecialistField`.`SpecialistEmployeeID`=?"))
  {
    $stmt->bind_param("i", $specialist_id);
    $stmt->execute();
    $stmt->bind_result($tag);
    while ($stmt->fetch())
    {
      array_push($results, $tag);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

/* array<hashmap<emp_id specialist_id,
               string name,
               string site,
               integer workload,
               array<string> relevant_tags,
               array<date> unavailability>> get_specialist_suggestions_for_ticket(integer ticket_id)
*/
function get_specialist_suggestions_for_ticket(int $ticket_id) // -> array
{
  $ticket = get_ticket($ticket_id);
  if ($ticket === null) return array();
  $tag_id = $ticket["problem_tag_id"];
  $tags = meta_get_problem_tag_path_array($tag_id);

  $query = "SELECT DISTINCT `Employee`.`ID`,
    	CONCAT(`FirstName`, ' ', `LastName`) AS name,
        `DepartmentCode`,
        CONCAT(`Department`.`Name`, ', ', `Department`.`City`, ', ', `Department`.`Country`) as Location,
    	COUNT(`Problem`.`SpecialistEmployeeID`) AS workload,
        IF(`Unavailability`.`Date` >= NOW() AND `Unavailability`.`Date` <= NOW() + INTERVAL 2 WEEK, `Unavailability`.`Date`, NULL)
    FROM Employee
    RIGHT JOIN Specialist
    ON `Employee`.`ID`=`Specialist`.`EmployeeID`
    LEFT JOIN `Department`
    ON `Employee`.`DepartmentCode`=`Department`.`Code`
    LEFT JOIN `Problem`
    ON `Problem`.`SpecialistEmployeeID`=`Employee`.`ID`
    LEFT JOIN `Unavailability`
    ON `Unavailability`.`SpecialistEmployeeID`=`Employee`.`ID`
    GROUP BY `Employee`.`ID`, `Unavailability`.`Date`
    ORDER BY
    	CASE
            WHEN `Department`.`Code` = (
                SELECT `Department`.`Code`
                FROM `Problem`
                LEFT JOIN `Call` ON `Call`.`ID`=`Problem`.`CallID`
                LEFT JOIN `Employee` ON `Employee`.`ID`=`Call`.`OperatorEmployeeID`
                LEFT JOIN `Department` ON `Department`.`Code`=`Employee`.`DepartmentCode`
                WHERE `Problem`.`TicketID` = ?
            ) THEN 0
            WHEN `Department`.`LocaleCode` = (
                SELECT `Department`.`LocaleCode`
                FROM `Problem`
                LEFT JOIN `Call` ON `Call`.`ID`=`Problem`.`CallID`
                LEFT JOIN `Employee` ON `Employee`.`ID`=`Call`.`OperatorEmployeeID`
                LEFT JOIN `Department` ON `Department`.`Code`=`Employee`.`DepartmentCode`
                WHERE `Problem`.`TicketID` = ?
            ) THEN 1
            ELSE 2
        END ASC, workload ASC, `Employee`.`ID` ASC";
  $conn = meta_open_db();
  $last_id = -1;
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("ii", $ticket_id, $ticket_id);
    $stmt->execute();
    $stmt->bind_result($id, $name, $dept_code, $loc, $workload, $date);
    while ($stmt->fetch())
    {
      if (sizeof($results) > 0 && $id === $last_id)
      {
        if ($date !== null)
          $results[sizeof($results) - 1]["unavailability"][] = $date;
      }
      else
      {
        $spec_tags = meta_tag_strings_for_specialist($id);
        $common_tags = array_intersect($spec_tags, $tags);
        array_push($results, [
          "specialist_id" => $id,
          "name" => $name,
          "site" => $loc,
          "workload" => $workload,
          "relevant_tags" => array_values($common_tags),
          "unavailability" => $date !== null ? [ $date ] : []
        ]);
      }
      $last_id = $id;
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

/* array<hashmap<int solution_id,
               string description,
               timestamp solution_timestamp,
               int ticket_id,
               int problem_tag_id,
               string notes,
               status status,
               string specialist_name,
               string operator_name,
               string first_mentioned>> get_solutions_for_similar(integer ticket_id)
*/
function get_solutions_for_similar(int $ticket_id) // -> array
{
  $query = "SELECT
      `Solution`.`ID`,
      `Solution`.`Description`,
      `Solution`.`Timestamp`,
      `Solution`.`ProblemTicketID`,
      `ProblemTag`.`ID`,
      `ProblemTag`.`TagName`,
      `Notes`,
      `Open`,
	     CONCAT(spec.`FirstName`, ' ', spec.`LastName`),
       CONCAT(oper.`FirstName`, ' ', oper.`LastName`),
       `Call`.`Timestamp`
    FROM `Solution`
    LEFT JOIN `Problem`
    ON `Problem`.`TicketID`=`Solution`.`ProblemTicketID`
    LEFT JOIN `ProblemTag`
    ON `ProblemTag`.`ID`=`Problem`.`ProblemTagID`
    LEFT JOIN `Call`
    ON `Call`.`ID`=`Problem`.`CallID`
    LEFT JOIN `Employee` AS oper
    ON oper.`ID`=`Call`.`OperatorEmployeeID`
    LEFT JOIN `Employee` AS spec
    ON spec.`ID`=`Problem`.`SpecialistEmployeeID`
    WHERE `ProblemTag`.`ID` IN (SELECT lvl0.`ID`
    FROM `ProblemTag` AS lvl0
    LEFT JOIN `ProblemTag` AS lvl1
    ON lvl1.`ParentID`=lvl0.`ID`
    LEFT JOIN `ProblemTag` AS lvl2
    ON lvl2.`ParentID`=lvl1.`ID`
    WHERE lvl0.`ID` = (SELECT `Problem`.`ProblemTagID` FROM `Problem` WHERE `Problem`.`TicketID`=?)
      OR lvl1.`ID` = (SELECT `Problem`.`ProblemTagID` FROM `Problem` WHERE `Problem`.`TicketID`=?)
      OR lvl2.`ID` = (SELECT `Problem`.`ProblemTagID` FROM `Problem` WHERE `Problem`.`TicketID`=?))";

    $conn = meta_open_db();
    $results = array();
    if ($stmt = $conn->prepare($query))
    {
      $stmt->bind_param("iii", $ticket_id, $ticket_id, $ticket_id);
      $stmt->execute();
      $stmt->bind_result($id, $desc, $timestamp, $ticket_id, $tag_id, $tag_name, $notes, $open, $spec, $oper, $call_timestamp);
      while ($stmt->fetch())
      {
        array_push($results, [
          "solution_id" => $id,
          "description" => $desc,
          "solution_timestamp" => $timestamp,
          "ticket_id" => $ticket_id,
          "problem_tag_id" => $tag_id,
          "problem_tag_name" => $tag_name,
          "notes" => $notes,
          "status" => $open,
          "specialist_name" => $spec,
          "operator_name" => $oper,
          "first_mentioned" => $call_timestamp
        ]);
      }
      $stmt->close();
    }
    $conn->close();
    return $results;
}

function meta_get_problem_tag_path(int $tag_id)
{
  $tag_details = get_problem_tag($tag_id);
  $path_string = $tag_details["name"];
  while ($tag_id = $tag_details["parent_tag_id"])
  {
    $tag_details = get_problem_tag($tag_id);
    $path_string = $tag_details["name"] . ' ' . $path_string;
  }
  return $path_string;
}

function meta_get_problem_tag_path_array(int $tag_id)
{
  $tag_details = get_problem_tag($tag_id);
  $path_ary = [];
  while ($tag_id !== null)
  {
    $tag_details = get_problem_tag($tag_id);
    $path_ary[] = $tag_details["name"];
    $tag_id = $tag_details["parent_tag_id"];
  }
  return $path_ary;
}

function meta_search_results_compare($a, $b)
{
  return $a["rank"] < $b["rank"];
}

// array<hashmap<integer ticket_id, timestamp first_mentioned, string notes>> search_for_problems(string query)
function search_for_problems(string $query, int $category = status::All, int $specialist_id = 0) // -> array
{
  // TODO: Add more search criteria, like comments
  $ticket_ids = get_all_tickets();
  $search_results = array();

  foreach ($ticket_ids as $id)
  {
    $ticket_details = get_ticket($id);

    // If we want all, just skip all the checks
    if ($category === status::All) {}
    // If we want all closed, skip those that aren't
    elseif ($category === status::AllClosed
      && $ticket_details["status"] === status::Closed) {}
    // If we want anything else, it has to match the logged in specialist
    elseif ($specialist_id !== $ticket_details["assigned_specialist"]
      || $category !== $ticket_details["status"]) continue;

    $search_string = "#" . $ticket_details["ticket_id"]
      . " " . $ticket_details["notes"]
      . " " . meta_get_problem_tag_path($ticket_details["problem_tag_id"])
      . " " . meta_dt2s($ticket_details["first_mentioned"]);

    if ($query != "")
    {
      $relevance_count = 0;
      $query_words = explode(" ", $query);
      foreach ($query_words as $w)
      {
        if ($w[0] == "#")
        {
          $relevance_count = $relevance_count + (("#" . $ticket_details["ticket_id"]) == $w) * 100;
        }
        else
        {
          $relevance_count = $relevance_count + substr_count(strtolower($search_string), strtolower($w));
        }
      }
      $rank = $relevance_count/sizeof(explode(" ", $search_string));
    }
    else {
      $rank = 1;
    }
    if ($rank > 0)
    {
      array_push($search_results, [
        "ticket_id" => $ticket_details["ticket_id"],
        "first_mentioned" => $ticket_details["first_mentioned"],
        "notes" => $ticket_details["notes"],
        "rank" => $rank
      ]);
    }
  }
  usort($search_results, "\db\meta_search_results_compare");
  return $search_results;
}

// FOR TYPEAHEAD:
// array<string> suggestions_in_call(call_fields suggestions_for, string first_name, string last_name, string employee_id, string phone_no, string email, string department, string dept_code, string job_title, string country)
function suggestions_in_call(
  string $suggestions_for,
  array $params
) // -> array
{
  $columns = array(
    "first_name" => "`FirstName`",
    "last_name" => "`LastName`",
    "employee_id" => "`ID`",
    "phone_no" => "REPLACE(`PhoneNumber`, ' ', '')",
    "email" => "`Email`",
    "department" => "CONCAT(`Name`, ' [', `Code`, ']')",
    "dept_code" => "`Code`",
    "job_title" => "`JobTitle`",
    "country" => "`Country`"
  );

  $field = $columns[$suggestions_for];
  if ($suggestions_for === 'phone_no') $field = "`PhoneNumber`";
  if ($params === null || $field === null) return null;

  $where = "1";
  foreach ($columns as $key=>$value)
  {
    $f = $value;
    $v = isset($params[$key]) ? meta_escape($params[$key]) : '';
    if ($key === 'phone_no')
    {
      $v = ltrim($v, '0');
      $v = str_replace(' ', '', $v);
      $where = $where . " AND ($f LIKE '%{$v}%')";
    }
    else
      $where = $where . " AND ($f LIKE '{$v}%')";
  }

  $query = "SELECT DISTINCT $field FROM `Employee` LEFT JOIN `Department` ON `DepartmentCode`=`Code` WHERE $where";
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->execute();
    $stmt->bind_result($vals);
    while ($stmt->fetch())
    {
      array_push($results, $vals);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// hashmap<string first_name, string last_name, string employee_id, string phone_no, string email, string department, string dept_code, string job_title, string country> autofill_call(string first_name, string last_name, string employee_id, string phone_no, string email, string department, string dept_code, string job_title, string country)
//  * returns an employee record if the given information is enough to uniquely identify a person
function autofill_call(array $params) // -> array
{
  $query = "SELECT `ID`, `FirstName`, `LastName`,`PhoneNumber`, `Email`, CONCAT(`Name`, ' [', `Code`, ']'), `Code`, `JobTitle`, `Country`
            FROM `Employee` LEFT JOIN `Department` ON `DepartmentCode` = `Code`
            WHERE (`ID` LIKE ?)
              AND (`FirstName` LIKE ?)
              AND (`LastName` LIKE ?)
              AND (`PhoneNumber` LIKE ?)
              AND (`Email` LIKE ?)
              AND (CONCAT(`Name`, ' [', `Code`, ']') LIKE ?)
              AND (`Code` LIKE ?)
              AND (`JobTitle` LIKE ?)
              AND (`Country` LIKE ?)";

	$format = function($name) use($params) {
		return (isset($params[$name]) ? $params[$name] : '') . "%";
	};
  $first_name = $format('first_name');
  $last_name = $format('last_name');
  $employee_id = $format('employee_id');
  $phone_no = $format('phone_no');
  $email = $format('email');
  $department = $format('department');
  $dept_code = $format('dept_code');
  $job_title = $format('job_title');
  $country = $format('country');

  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("sssssssss", $employee_id, $first_name, $last_name, $phone_no, $email, $department, $dept_code, $job_title, $country);
    $stmt->execute();
    $stmt->bind_result($id, $fn, $ln, $pn, $em, $dt, $cd, $jt, $ct);
    while ($stmt->fetch())
    {
      $record = array(
        "first_name" => $fn,
        "last_name" => $ln,
        "employee_id" => $id,
        "phone_no" => $pn,
        "email" => $em,
        "department" => $dt,
        "dept_code" => $cd,
        "job_title" => $jt,
        "country" => $ct
      );
      array_push($results, $record);
    }
    $stmt->close();
  }
  $conn->close();
  return count($results) == 1 ? $results[0] : array();
}

// array<string> suggestions_in_hardware(hardware_fields suggestions_for, string type, string make, string model, string serial_no)
function suggestions_in_hardware(string $suggestions_for, array $params) // -> array
{
  $columns = array(
    "type" => "`Type`",
    "make" => "`Make`",
    "model" => "`Model`",
    "serial_no" => "`SerialNumber`",
  );

  $field = $columns[$suggestions_for];
  if ($params === null || $field === null) return null;

  $where = "1";
  foreach ($columns as $key=>$value)
  {
    $f = $value;
    $v = isset($params[$key]) ? meta_escape($params[$key]) : '';
    $where = $where . " AND ($f LIKE '{$v}%')";
  }

  $query = "SELECT DISTINCT $field FROM `HardwareEquipment` WHERE $where";
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->execute();
    $stmt->bind_result($vals);
    while ($stmt->fetch())
    {
      array_push($results, $vals);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// hashmap<integer hardware_id, string type, string make, string model, string serial_no> autofill_hardware(string type, string make, string model, string serial_no)
function autofill_hardware(array $params) // -> array
{
  $query = "SELECT `ID`, `Type`, `Make`, `Model`, `SerialNumber`
            FROM `HardwareEquipment`
            WHERE (`Type` LIKE ?)
              AND (`Make` LIKE ?)
              AND (`Model` LIKE ?)
              AND (`SerialNumber` LIKE ?)";

	$format = function($name) use($params) {
		return (isset($params[$name]) ? $params[$name] : '') . "%";
	};
  $type = $format('type');
  $make = $format('make');
  $model = $format('model');
  $serial_no = $format('serial_no');

  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("ssss", $type, $make, $model, $serial_no);
    $stmt->execute();
    $stmt->bind_result($id, $tp, $mk, $md, $sn);
    while ($stmt->fetch())
    {
      $record = array(
        "hardware_id" => 2 * $id,
        "type" => $tp,
        "make" => $mk,
        "model" => $md,
        "serial_no" => $sn
      );
      array_push($results, $record);
    }
    $stmt->close();
  }
  $conn->close();
  return count($results) == 1 ? $results[0] : array();
}

// array<string> suggestions_in_software(software_fields suggestions_for, string name, string version, string registration_no)
function suggestions_in_software(string $suggestions_for, array $params) // -> array
{
  $columns = array(
    "name" => "`Name`",
    "version" => "`Version`",
    "registration_no" => "`RegistrationNumber`",
  );

  $field = $columns[$suggestions_for];
  if ($params === null || $field === null) return null;

  $where = "1";
  foreach ($columns as $key=>$value)
  {
    $f = $value;
    $v = isset($params[$key]) ? meta_escape($params[$key]) : '';
    $where = $where . " AND ($f LIKE '{$v}%')";
  }

  $query = "SELECT DISTINCT $field FROM `SoftwareEquipment` WHERE $where";
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->execute();
    $stmt->bind_result($vals);
    while ($stmt->fetch())
    {
      array_push($results, $vals);
    }
    $stmt->close();
  }
  $conn->close();
  return $results;
}

// hashmap<integer software_id, string name, string version, string registration_no> autofill_software(string name, string version, string registration_no)
function autofill_software(array $params) // -> array
{
  $query = "SELECT `ID`, `Name`, `Version`, `RegistrationNumber`
            FROM `SoftwareEquipment`
            WHERE (`Name` LIKE ?)
              AND (`Version` LIKE ?)
              AND (`RegistrationNumber` LIKE ?)";

	$format = function($name) use($params) {
		return (isset($params[$name]) ? $params[$name] : '') . "%";
	};
  $name = $format('name');
  $version = $format('version');
  $registration_no = $format('registration_no');

  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare($query))
  {
    $stmt->bind_param("sss", $name, $version, $registration_no);
    $stmt->execute();
    $stmt->bind_result($id, $nm, $vr, $rn);
    while ($stmt->fetch())
    {
      $record = array(
        "software_id" => 2 * $id + 1,
        "name" => $nm,
        "version" => $vr,
        "registration_no" => $rn
      );
      array_push($results, $record);
    }
    $stmt->close();
  }
  $conn->close();
  return count($results) == 1 ? $results[0] : array();
}

function get_user_preference(int $user_id, string $name)
{
  $value = null;
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("SELECT `Value` FROM `UserPreferences` WHERE `UserID`=? AND `Name`=?"))
  {
    $stmt->bind_param("is", $user_id, $name);
    $stmt->execute();
    $stmt->bind_result($value);
    $stmt->close();
  }
  $conn->close();
  return $value;
}

function get_user_preference_ID(string $value)
{
  $conn = meta_open_db();
  $results = array();
  if ($stmt = $conn->prepare("SELECT `UserID` FROM `UserPreferences` WHERE `Value`=?"))
  {
    $stmt->bind_param("s", $value);
    if (!$stmt->execute()) return null;
    $stmt->bind_result($id);
    while ($stmt->fetch())
    {
      $results["user_id"] = $id;
    }
    $stmt->close();
  }
  $conn->close();
  return empty($results) ? null : $results;
}

function set_user_preference(int $user_id, string $name, string $value)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("REPLACE INTO `UserPreferences`(`UserID`, `Name`, `Value`) VALUES (?,?,?)"))
  {
    $stmt->bind_param("iss", $user_id, $name, $value);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}

function delete_user_preference(int $user_id, string $name)
{
  $conn = meta_open_db();
  if ($stmt = $conn->prepare("DELETE FROM `UserPreferences` WHERE `UserID`=? AND `Name`=?"))
  {
    $stmt->bind_param("is", $user_id, $name);
    $stmt->execute();
    $stmt->close();
  }
  $conn->close();
}
?>
