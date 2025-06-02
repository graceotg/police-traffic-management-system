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
        isset($_POST['person_licence']) && $_POST['person_licence'] !== "" &&
        isset($_POST['vehicle_plate']) && $_POST['vehicle_plate'] !== "") 
    {
        // Vehicle details
        $vehicle_plate = $_POST['vehicle_plate'];
        $person_licence = $_POST['person_licence'];


        //Checking to see if the vehicle plate exists
        $check_plate_sql = "SELECT * FROM Vehicle WHERE Vehicle_plate = '$vehicle_plate'";
        $check_plate_result = mysqli_query($conn, $check_plate_sql);

        //Checking to see if the person's licence exists
        $check_licence_sql = "SELECT * FROM People WHERE People_licence = '$person_licence'";
        $check_licence_result = mysqli_query($conn, $check_licence_sql);


        if (mysqli_num_rows($check_plate_result) === 0 && mysqli_num_rows($check_licence_result) === 0){
            $message = "Vehicle plate: '$vehicle_plate' and licence number: '$person_licence', does not exist in the system";
            $link1 = "Click here to add a new person";
            $link2 = "Click here to add a new vehicle";
        }

        elseif (mysqli_num_rows($check_plate_result) === 0) {
            $message = "Vehicle with plate number: '$vehicle_plate' does not exist in the system";
            $link2 = "Click here to add a new vehicle";
        }
        elseif (mysqli_num_rows($check_licence_result) === 0) {
            $message = "Person with licence number: '$person_licence' does not exist in the system";
            $link1 = "Click here to add a new person";
        }

        else{

            // Get People_ID and Vehicle_ID
            $people_sql = "SELECT People_ID FROM People WHERE People_licence = '$person_licence'";
            $vehicle_sql = "SELECT Vehicle_ID FROM Vehicle WHERE Vehicle_plate = '$vehicle_plate'";

            $people_result = mysqli_query($conn, $people_sql);
            $vehicle_result = mysqli_query($conn, $vehicle_sql);


            if (mysqli_num_rows($vehicle_result) > 0 && mysqli_num_rows($people_result) > 0) {
                
                $people_row = mysqli_fetch_assoc($people_result);
                $vehicle_row = mysqli_fetch_assoc($vehicle_result);
                
                $people_id = $people_row["People_ID"];
                $vehicle_id = $vehicle_row["Vehicle_ID"];

                $join_sql = "INSERT INTO Ownership (People_ID, Vehicle_ID) VALUES ('$people_id', '$vehicle_id')";
                
                if (mysqli_query($conn, $join_sql)) {
                    $success = "Ownership successfully linked (People_ID: $people_id, Vehicle_ID: $vehicle_id)";


                    //Getting ownership ID:
                    $ownership_sql = "SELECT Ownership_ID FROM Ownership WHERE Vehicle_ID = '$vehicle_id' AND People_ID = '$people_id'";
                    
                    $ownership_result = mysqli_query($conn, $ownership_sql);
                    
                    if ($ownership_result) {
                        $ownership_row = mysqli_fetch_assoc($ownership_result);
                        $ownership_id = $ownership_row["Ownership_ID"];
                    }
        
                    //Logging Audit
                    if(isset($_SESSION['id'])) {
                      // Log the audit trail
                      logAuditTrail(
                          $_SESSION['id'],       // Current user ID
                          'Ownership',           // Table name
                          $ownership_id,        // Record ID
                          'INSERT',            // Action type
                          $success             // Additional details
                      );
                    }
                } else {
                    $error = 'Error linking ownership: " . mysqli_error($conn) . "';
                }
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
            <h1>Link Vehicle</h1>
            <p>Fill out all the required fields below to link a vehicle to a person</p>
        </div>
        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="person_licence">Person Licence</label>
                        <input type="text" name="person_licence" id="person_licence" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="vehicle_plate">Vehicle Plate</label>
                        <input type="text" name="vehicle_plate" id="vehicle_plate" class="form-input" required>
                    </div>
                </div>
                <button type="submit" name="add_vehicle_submit" class="form-button">Link Vehicle</button>
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
