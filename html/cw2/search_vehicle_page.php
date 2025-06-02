<?php
  include('nav_bar.php');
  require("db.info.php");
        
  // Open the database connection
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  
                        
  // Check connection
  if(mysqli_connect_errno()) 
  {
     echo "Failed to connect to MySQL: ".mysqli_connect_error();
     die();
  } 
  // else echo "MySQL connection OK<br>";  // useful for testing

  // Initialize variables 
  $vehicle_result = null;

  if(isset($_POST['v_licence_number']) && $_POST['v_licence_number']!=""){
    $vehicle_sql= "SELECT v.Vehicle_plate, p_owner.People_name AS Owner_Name, p_owner.People_licence AS Owner_Licence, v.Vehicle_make, v.Vehicle_model, v.Vehicle_colour, p_incident.People_name AS Incident_Person_Name, p_incident.People_licence AS Incident_Person_Licence, i.Incident_ID, i.Incident_Date, i.Incident_Report FROM Vehicle v LEFT JOIN Ownership o ON v.Vehicle_ID = o.Vehicle_ID LEFT JOIN People p_owner ON o.People_ID = p_owner.People_ID LEFT JOIN Incident i ON v.Vehicle_ID = i.Vehicle_ID LEFT JOIN People p_incident ON i.People_ID = p_incident.People_ID WHERE v.Vehicle_plate = '".$_POST['v_licence_number']."';";
    
    $vehicle_result = mysqli_query($conn, $vehicle_sql);
}
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Vehicle</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<style>

    #addOwnerTitle, #addOwnerDescription, #addOwnerForm {
        display: none;
    }

</style>
<body>

    <div class="main-content">
        <div class="page-header">
            <h1>Search Vehicle</h1>
            <p>Enter vehicle plate number</p>
        </div>

        <div class="form-container">
            <form id="searchVehicleForm" method="POST">
                <div class="form-group">
                    <label class="form-label" for="v_licence_number">Plate Number</label>
                    <input type="text" name="v_licence_number" id="v_licence_number" class="form-input" required>
                </div>

                <button type="submit" name="lookup_vehicle_submit" class="form-button">Look Up Vehicle</button>
            </form>
        </div>
        <?php
            if ($vehicle_result !== null) {
                if (mysqli_num_rows($vehicle_result) > 0) 
                {
                   echo "<table>";  
                   echo "<tr>";
                   echo "<th>Vehicle Plate</th>";
                   echo "<th>Owner's Name</th>";
                   echo "<th>Owner's Licence</th>";
                   echo "<th>Vehicle Make</th>";
                   echo "<th>Vehicle Model</th>";
                   echo "<th>Vehicle Colour</th>";
                   echo "<th>Offender's Name</th>";
                   echo "<th>Offender's Licence</th>";
                   echo "<th>Incident ID</th>";
                   echo "<th>Incident Date</th>";
                   echo "<th>Incident Report</th>";
                   echo "</tr>";
                   
                   while($row = mysqli_fetch_assoc($vehicle_result)) 
                   {
                     echo "<tr>";
                     echo "<td>" . (!empty($row["Vehicle_plate"]) ? $row["Vehicle_plate"] : '<span style="color: gray;">unknown</span>') . "</td>"; 
                     echo "<td>" . (!empty($row["Owner_Name"]) ? $row["Owner_Name"] : '<span style="color: gray;">unknown</span>') . "</td>"; 
                     echo "<td>" . (!empty($row["Owner_Licence"]) ? $row["Owner_Licence"] : '<span style="color: gray;">unknown</span>') . "</td>";
                     echo "<td>" . (!empty($row["Vehicle_make"]) ? $row["Vehicle_make"] : '<span style="color: gray;">unknown</span>') . "</td>";
                     echo "<td>" . (!empty($row["Vehicle_model"]) ? $row["Vehicle_model"] : '<span style="color: gray;">unknown</span>') . "</td>";
                     echo "<td>" . (!empty($row["Vehicle_colour"]) ? $row["Vehicle_colour"] : '<span style="color: gray;">unknown</span>') . "</td>";
                     echo "<td>" . (!empty($row["Incident_Person_Name"]) ? $row["Incident_Person_Name"] : '<span style="color: gray;">null</span>') . "</td>";
                     echo "<td>" . (!empty($row["Incident_Person_Licence"]) ? $row["Incident_Person_Licence"] : '<span style="color: gray;">null</span>') . "</td>";
                     echo "<td>" . (!empty($row["Incident_ID"]) ? $row["Incident_ID"] : '<span style="color: gray;">null</span>') . "</td>";
                     echo "<td>" . (!empty($row["Incident_Date"]) ? $row["Incident_Date"] : '<span style="color: gray;">null</span>') . "</td>";
                     echo "<td>" . (!empty($row["Incident_Report"]) ? $row["Incident_Report"] : '<span style="color: gray;">null</span>') . "</td>";
                     echo "</tr>";
                   } 
                   echo "</table>"; 
                }
                else 
                {
                    echo "<div class='alert-error'>No vehicle was found</div>";
                    echo '<div class="alert-error"><a href="add_vehicle.php">Click here to add a new vehicle</a></div>';
                }
            } 
        ?>

    </div>

</body>
</html>
