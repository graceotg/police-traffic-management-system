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
    if (mysqli_connect_errno()) {
        $error = 'Failed to connect to MySQL: " . mysqli_connect_error() . "'; 
        die();
    }

    if (
        isset($_POST['vehicle_make']) && $_POST['vehicle_make'] !== "" &&
        isset($_POST['vehicle_model']) && $_POST['vehicle_model'] !== "" &&
        isset($_POST['vehicle_colour']) && $_POST['vehicle_colour'] !== "" &&
        isset($_POST['vehicle_plate']) && $_POST['vehicle_plate'] !== "") 
    {
        // Vehicle details
        $vehicle_plate = $_POST['vehicle_plate'];
        $vehicle_make = $_POST['vehicle_make'];
        $vehicle_model = $_POST['vehicle_model'];
        $vehicle_colour = $_POST['vehicle_colour'];


        //Checking to see if the vehicle plate already exists
        $check_sql = "SELECT * FROM Vehicle WHERE Vehicle_plate = '$vehicle_plate'";
        $check_result = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Vehicle with plate number: '$vehicle_plate' already exists in the system";
        }
        else{

            // Insert vehicle
            $vehicle_insert_sql = "INSERT INTO Vehicle (Vehicle_plate, Vehicle_make, Vehicle_model, Vehicle_colour) 
                VALUES ('$vehicle_plate', '$vehicle_make', '$vehicle_model', '$vehicle_colour')";
            
            if (mysqli_query($conn, $vehicle_insert_sql)) {
                $vehicle_id = mysqli_insert_id($conn);
                $success = "Vehicle with ID: '$vehicle_id', has been added to the system";

                $report = "Vehicle ID: $vehicle_id, was added";

                // Check if user is logged in before logging audit
                if(isset($_SESSION['id'])) {
                  // Log the audit trail
                  logAuditTrail(
                      $_SESSION['id'],        // Current user ID
                      'Vehicle',            // Table name
                      $vehicle_id,          // Record ID
                      'INSERT',              // Action type
                      $report                // Additional details
                  );
                }
            }
            else {
                $error = 'Error: " . mysqli_error($conn) . "';
            }
            
        }
        
        // Store the message in the session
        $_SESSION['message'] = $message;
        $_SESSION['success'] = $success;
        $_SESSION['error'] = $error;


        // Redirect to the same page to avoid resubmission
        ob_end_clean();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;

    }
    ob_end_flush();   
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Add Vehicle</h1>
            <p>Fill out all the required fields below to add a vehicle to the system</p>
        </div>
        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_make">Make</label>
                        <input type="text" name="vehicle_make" id="vehicle_make" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vehicle_model">Model</label>
                        <input type="text" name="vehicle_model" id="vehicle_model" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vehicle_colour">Colour</label>
                        <input type="text" name="vehicle_colour" id="vehicle_colour" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vehicle_plate">Vehicle Plate</label>
                        <input type="text" name="vehicle_plate" id="vehicle_plate" class="form-input" required>
                    </div>

                </div>
                <button type="submit" name="add_vehicle_submit" class="form-button">Add Vehicle</button>
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
            ?>
        </div>
    </div>
</body>
</html>
