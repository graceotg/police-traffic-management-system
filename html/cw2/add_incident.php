<?php
    ob_start(); // Start output buffering
    include('nav_bar.php');
    require('audit_logger.php');
?>

<?php 
    require("db.info.php"); 
        
    // Open the database connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
                           
    // Check connection
    if(mysqli_connect_errno()) {
      $error = 'Failed to connect to MySQL: " . mysqli_connect_error() . "'; 
       // echo "Failed to connect to MySQL: ".mysqli_connect_error();
      die();
    } 

    if(isset($_POST['offence_id']) && $_POST['offence_id']!="" &&
        isset($_POST['incident_date']) && $_POST['incident_date']!="" &&
        isset($_POST['incident_time']) && $_POST['incident_time']!="" &&
        isset($_POST['incident_report']) && $_POST['incident_report']!="" &&
        isset($_POST['offender_licence']) && $_POST['offender_licence']!="" &&
        isset($_POST['vehicle_plate']) && $_POST['vehicle_plate']!="") {

        // Incident details
        $offence_id = $_POST['offence_id'];
        $incident_date = $_POST['incident_date'];
        $incident_time = $_POST['incident_time'];
        $incident_report = $_POST['incident_report'];
        $offender_licence = $_POST['offender_licence'];
        $vehicle_plate = $_POST['vehicle_plate'];

        // Vehicle details
        $vehicle_make = $_POST['vehicle_make'];
        $vehicle_model = $_POST['vehicle_model'];
        $vehicle_colour = $_POST['vehicle_colour'];

        // Offender details
        $offender_name = $_POST['offender_name'];
        $offender_address = $_POST['offender_address'];


        // Getting the ID of the owner of the car
        $person_sql = "SELECT People_ID FROM People WHERE People_licence = '$offender_licence';";
        $person_result = mysqli_query($conn, $person_sql);
        if (mysqli_num_rows($person_result) > 0) {
            $row = mysqli_fetch_assoc($person_result);
            $people_id = $row['People_ID'];
        }

        // Getting the vehicle ID of the owner of the car
        $vehicle_sql = "SELECT Vehicle_ID FROM Vehicle WHERE Vehicle_plate = '$vehicle_plate';";
        $vehicle_result = mysqli_query($conn, $vehicle_sql);
        if (mysqli_num_rows($vehicle_result) > 0) {
            $row = mysqli_fetch_assoc($vehicle_result);
            $vehicle_id = $row['Vehicle_ID'];
        }

        if (mysqli_num_rows($person_result) === 0 && mysqli_num_rows($vehicle_result) === 0){
          $message = "Both the person and the vehicle are not in the system.";
          $link1 = "Click here to add new person";
          $link2 = "Click here to add new vehicle";
        }

        elseif (mysqli_num_rows($person_result) === 0) {
          $message = "The person is not in the system.";
          $link1 = "Click here to add new person";
        }

        elseif (mysqli_num_rows($vehicle_result) === 0) {
          $message = "The vehicle is not in the system.";
          $link2 = "Click here to add new vehicle";
        }
        else {
          $insert_sql = "INSERT INTO Incident (Vehicle_ID, People_ID, Incident_Date, Incident_Time, Incident_Report, Offence_ID) 
                         VALUES ('$vehicle_id', '$people_id', '$incident_date','$incident_time','$incident_report','$offence_id')";
  
          if (mysqli_query($conn, $insert_sql)){
            $incident_id = mysqli_insert_id($conn);
            $success = "New incident has been added with Incident ID: $incident_id";

            $report = "Incident ID: $incident_id, was created";

            // Check if user is logged in before logging audit
            if(isset($_SESSION['id'])) {
              // Log the audit trail
              logAuditTrail(
                  $_SESSION['id'],        // Current user ID
                  'Incident',            // Table name
                  $incident_id,          // Record ID
                  'INSERT',              // Action type
                  $report                // Additional details
              );
            }
          }
          else{
            $error = 'Error linking ownership: " . mysqli_error($conn) . "';
          }
        }
        // Store the message in the session
        $_SESSION['message'] = $message;
        $_SESSION['success'] = $success;
        $_SESSION['error'] = $error;
        $_SESSION['link1'] = $link1;
        $_SESSION['link2'] = $link2;


        // Redirect to the same page to avoid resubmission
        ob_end_clean();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
      }
      ob_end_flush(); 
   ?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Incident</title>
  <!-- Importing the CSS file -->
  <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
  <div class="main-content">
    <div class="page-header">
    <h1>Add Incident</h1>
    <p>Fill out all required fields below</p>
    <p>Only fill out the additional vehicle and offender information if they are not already in the system</p>
  </div>
    <div class="form-container">
      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="offence_id"> Offence ID</label>
                    <select name="offence_id" id="offence_id" class="form-input" onchange="this.form.submit()" required>
                        <option value="">Select Offence</option>
                        <?php
                        // SQL query to fetch Incident_IDs that are not linked to any Fine
                        $offence_sql = "SELECT Offence_ID, Offence_description FROM `Offence`;";  
                        $offence_result = mysqli_query($conn, $offence_sql);
                        if (mysqli_num_rows($offence_result) > 0) {
                            while ($offence_row = mysqli_fetch_assoc($offence_result)) {
                                $selected = (isset($_POST['offence_id']) && $_POST['offence_id'] == $offence_row['Offence_ID']) ? 'selected' : '';
                                echo "<option value='" . $offence_row['Offence_ID'] . "' $selected>" . $offence_row['Offence_ID'] . " - " . $offence_row['Offence_description'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No offences available</option>";
                        }
                        ?>
                    </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="incident_date">Incident Date:</label>
            <input class="form-input" type="date" name="incident_date" id="incident_date" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="incident_time">Incident Time:</label>
            <input class="form-input" type="time" name="incident_time" id="incident_time" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="incident_report">Incident Report:</label>
            <input class="form-input" type="text" name="incident_report" id="incident_report" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="offender_licence">Offender's Licence:</label>
            <input class="form-input" type="text" name="offender_licence" id="offender_licence" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="vehicle_plate">Vehicle Plate:</label>
            <input class="form-input" type="text" name="vehicle_plate" id="vehicle_plate" required>
          </div>
        </div>
        <button type="submit" name="add_vehicle_submit" class="form-button">Add Incident</button>
      </form>
      <!-- Message Display -->
      <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert-error'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']); // Clear the message so it doesn't show again
        }
        if (isset($_SESSION['success'])) {
            echo "<div class='alert-success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']); // Clear the message so it doesn't show again
        }
        if (isset($_SESSION['error'])) {
            echo "<div class='alert-error'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']); // Clear the message so it doesn't show again
        }
        if (isset($_SESSION['link1']) && isset($_SESSION['link2'])) {
            echo "<div class='alert-error'><a href='add_person.php'>" . $_SESSION['link1'] . "</a></div>";
            echo "<div class='alert-error'><a href='add_vehicle.php'>" . $_SESSION['link2'] . "</a></div>";
            unset($_SESSION['link1']); // Clear the message so it doesn't show again
            unset($_SESSION['link2']); // Clear the message so it doesn't show again
        }
        elseif (isset($_SESSION['link1'])) {
            echo "<div class='alert-error'><a href='add_person.php'>" . $_SESSION['link1'] . "</a></div>";
            unset($_SESSION['link1']); // Clear the message so it doesn't show again
        }
        elseif (isset($_SESSION['link2'])) {
            echo "<div class='alert-error'><a href='add_vehicle.php'>" . $_SESSION['link2'] . "</a></div>";
            unset($_SESSION['link2']); // Clear the message so it doesn't show again
        }
      ?>
    </div>
  </div>
</body>
</html>
