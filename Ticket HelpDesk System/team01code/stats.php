<!DOCTYPE html>
<html lang="en-us">
  <head>
    <?php include 'login-toolkit/login_setup.php';?>
    <?php include 'PHP-Reusables/header.php';?>

    <?php
    $conn = new \mysqli("localhost", "team01", "8a1hsMw5La", "team01");
    if ($conn->connect_errno)
    {
      printf("Database down: %s\n", $mysqli->connect_error);
      exit();
    }
    //Graph 1 data request (Number of Solved Problems Per Specialist)
    $result = $conn->query("SELECT Employee.FirstName, Employee.LastName, COUNT(*) AS `num` FROM Solution INNER JOIN Employee ON Solution.SpecialistEmployeeID=Employee.ID GROUP BY `SpecialistEmployeeID`");
    $php_var = "";
    while($row = $result->fetch_assoc()) {
      $php_var = $php_var. "['" .$row['FirstName']. " ". $row['LastName']. "'," . $row['num']. "],";
    }
    //Graph 2 data request (Number of Problems and their Problem Tags)
    $result2 = $conn->query("SELECT ProblemTag.TagName, COUNT(*) AS `num` FROM Problem INNER JOIN ProblemTag ON Problem.ProblemTagID=ProblemTag.ID GROUP BY `ProblemTagID`");
    $php_var2 = "";
    while($row2 = $result2->fetch_assoc()) {
      $php_var2 = $php_var2. "['" . $row2['TagName']. "'," . $row2['num']. "],";
    }
    //Average time data request
    $result3 = $conn->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, Call.Timestamp, Solution.Timestamp)/(60*24)) AS 'wait' FROM `Call` INNER JOIN ProblemReport ON Call.ID = ProblemReport.CallID INNER JOIN Solution ON ProblemReport.ProblemTicketID = Solution.ProblemTicketID");
    $avg_wait_time = 0;
    while($row3 = $result3->fetch_assoc()) {
      $avg_wait_time = $row3['wait'];
    }
    //Graph 3 (Number of Calls Per Country)
    $result4 = $conn->query("SELECT Count(*), Department.Country FROM `Call` INNER JOIN Employee ON Call.EmployeeID = Employee.ID LEFT JOIN Department ON Department.Code=Employee.DepartmentCode GROUP BY Department.Country");
    $php_var3 = "";
    while($row4 = $result4->fetch_assoc()) {
      $php_var3 = $php_var3. "['" . $row4['Country']. "'," . $row4['Count(*)']. "],";
    }
    //Graph 4 (Availability)
    $result5 = $conn->query("SELECT CONCAT(Employee.FirstName, ' ', Employee.LastName) As Name, Count(*) FROM `Unavailability` LEFT JOIN Employee ON Unavailability.SpecialistEmployeeID= Employee.ID Group BY`SpecialistEmployeeID`");
    $name = [];
    $number = [];
    while($row5 = $result5->fetch_assoc()) {
      array_push($name,$row5['Name']);
      array_push($number,$row5['Count(*)']);
    }
    $conn->close();
    ?>
    <!--AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart']});
    google.charts.load('current', {'packages':['geochart']});


    // callback functions
    google.charts.setOnLoadCallback(drawSpecialistChart);
    google.charts.setOnLoadCallback(drawProblemTagChart);
    google.charts.setOnLoadCallback(drawRegionsMap);


    $(window).resize(function(){
      drawSpecialistChart();
      drawProblemTagChart();
      drawRegionsMap();
    });

    // draw chart runs when div requires it
    function drawSpecialistChart() {

      // create/add data
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Specialist');
      data.addColumn('number', 'Number of Solved Problems');
      data.addRows(<?php echo '['.$php_var.']' ?>);

      //chart options
      var options = { };

      var chart = new google.visualization.PieChart(document.getElementById('specialist_chart_div'));
      chart.draw(data, options);
    }

    function drawProblemTagChart() {

      // create/add data
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Problem Tag');
      data.addColumn('number', 'Number');
      data.addRows(<?php echo '['.$php_var2.']' ?>);

      //chart options
      var options = { 'legend' : {'position' : 'none'} };

      var chart = new google.visualization.BarChart(document.getElementById('problemtag_chart_div'));
      chart.draw(data, options);
    }

    //store javascript variables for function in body
    var nameArray= <?php echo json_encode($name); ?>;
    var numberArray= <?php echo json_encode($number); ?>;
    avg = <?= $avg_wait_time ?>;

    window.onload = function(){
      //write average days on page
      document.getElementById('avgTime').innerHTML = "<?=l('Average Wait for a Solution: ')?>" + Math.round(avg * 100) / 100 + "<?=l(' days.')?>";
    }

    function drawRegionsMap() {
      // create/add data
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Country');
      data.addColumn('number', 'Number of Calls');
      data.addRows(<?php echo '['.$php_var3.']' ?>);

      var options = { };

      var chart = new google.visualization.GeoChart(document.getElementById('regions_map_div'));

      chart.draw(data, options);
    }



    </script>

    <style type="text/css">
    .chart {
      width: 100%;
      min-height: 450px;
    }
    </style>
    <title>Stats</title>
  </head>
  <body>
    <?php $PAGE_TITLE = 'Statistics'; ?>
    <?php include 'PHP-Reusables/navbar.php'; ?>
    <div class="row">
      <div class="clearfix"></div>
      <div class="col-md-5 col-md-offset-1 col-xs-12">
        <h3><?=l('Number of Solved Problems Per Specialist')?></h3>
        <div id="specialist_chart_div" class="chart"></div>
      </div>
      <div class="col-md-5 col-xs-12">
        <h3><?=l('Number of Problems and their Problem Tags')?></h3>
        <div id="problemtag_chart_div" class="chart"></div>
      </div>
      <div class="col-md-5 col-md-offset-1 col-xs-12">
        <h3><?=l('Number of Calls Per Country')?></h3>
        <div id="regions_map_div" class="chart"></div>
      </div>
      <div class="col-md-5 col-xs-12">
        <div id="availability_div" class="chart">
          <h3><?=l('Number of Booked Off Days Per Specialist')?></h3>
          <table class="table table-hover" id="table">
            <thead>
              <tr>
                 <th><?=l('Name')?></th>
                 <th><?=l('Number')?></th>
              </tr>
            </thead>
            <tbody id='tbody'>
              <script>
              for( i = 0; i < nameArray.length; i++)
              {
                document.write("<tr><td>" + nameArray[i]+"</td><td>"+numberArray[i]+"</td></tr>");
              }
              </script>
            </tbody>
          </table>
          <h4 id="avgTime"></h4>
        </div>
      </div>
    </div>
  </body>
</html>
