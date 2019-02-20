<!DOCTYPE html>
<html lang="en-us">
  <head>
    <?php include_once 'login-toolkit/login_setup.php';?>
    <?php include_once 'PHP-API/Localisation.php';?>
    <?php include_once 'PHP-Reusables/header.php';?>
    <?php include_once 'PHP-DB/api.php';?>
    <title><?=l('Home Page')?></title>
  </head>
  <body>
    <?php $PAGE_TITLE = l('Employee Directory'); ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>
    <!-- Main body -->
    <div class="col-xs-12 col-md-10 col-md-offset-1 col-xl-8 col-xl-offset-2">
      <div class="table-responsive">
        <table class="table table-hover">
          <tr>
            <th><?=l('ID')?></th>
            <th><?=l('Name')?></th>
            <th><?=l('Job Title')?></th>
            <th><?=l('Email')?></th>
            <th><?=l('Phone No.')?></th>
            <th><?=l('Department')?></th>
          </tr>
          <!-- gets all of the employee data, and puts it into the table for the operator or the specialist to see
          Done by Eduardas Verba -->
          <?php
            $conn = \db\meta_open_db();
            if ($stmt = $conn->prepare("SELECT `ID`, CONCAT(`FirstName`, ' ', `LastName`), `JobTitle`, `Email`, `PhoneNumber`, CONCAT(`Department`.`Name`, ', ', `Department`.`City`)
            FROM `Employee`
            LEFT JOIN `Department`
            ON `Department`.`Code`=`DepartmentCode`
            WHERE 1"))
            {
              $stmt->execute();
              $stmt->bind_result($id, $full_name, $job_title, $email, $phone_number, $department_location);
              while ($stmt->fetch())
              {
                echo '<tr>' .
                "<td>" . htmlspecialchars($id) . "</td>" .
                "<td>" . htmlspecialchars($full_name) . "</td>" .
                "<td>" . htmlspecialchars($job_title) . "</td>" .
                "<td><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></td>" .
                "<td><a href='tel:" . htmlspecialchars($phone_number) . "'>"
                  . (is_language_rtl() ? '&lrm;' : '')
                  . htmlspecialchars($phone_number) . "</a></td>"
                  . (is_language_rtl() ? '&rlm;' : '') .
                "<td>" . htmlspecialchars($department_location) . "</td>" .
                '</tr>';
              }
              $stmt->close();
            }
            $conn->close();
          ?>
        </table>
      </div>
    </div>
  </body>
</html>
