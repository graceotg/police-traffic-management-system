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
        $message = 'Failed to connect to MySQL: " . mysqli_connect_error() . "'; 
        die();
    }

    if (
        isset($_POST['person_name']) && $_POST['person_name'] !== "" &&
        isset($_POST['person_address']) && $_POST['person_address'] !== "" &&
        isset($_POST['person_licence']) && $_POST['person_licence'] !== "" ) 
    {
        // People details
        $person_name = $_POST['person_name'];
        $person_address = $_POST['person_address'];
        $person_licence = $_POST['person_licence'];

        //Checking to see if the licence number already exists
        $check_sql = "SELECT * FROM People WHERE People_licence = '$person_licence'";
        $check_result = mysqli_query($conn, $check_sql);
        if (mysqli_num_rows($check_result) > 0) {
            $message = "Licence number: $person_licence already exists in the system";
        }

        else{
            $insert_sql = "INSERT INTO People (People_name, People_address, People_licence) VALUES ('$person_name', '$person_address', '$person_licence')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $people_id = mysqli_insert_id($conn);
                $success = "Person successfully added with ID number: $people_id";

                $report = "Person ID: $people_id, was created";

                // Check if user is logged in before logging audit
                if(isset($_SESSION['id'])) {
                  // Log the audit trail
                  logAuditTrail(
                      $_SESSION['id'],        // Current user ID
                      'People',            // Table name
                      $people_id,          // Record ID
                      'INSERT',              // Action type
                      $report                // Additional details
                  );
                }
            } else {
                $message = 'Error: " . mysqli_error($conn) . "';
            }

        }
        // Store the message in the session
        $_SESSION['message'] = $message;
        $_SESSION['success'] = $success;


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
    <title>Add Person</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Add Person</h1>
            <p>Fill out all the fields below to add a person to the system</p>
        </div>
        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="person_name">Name</label>
                        <input type="text" name="person_name" id="person_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="person_address">Address</label>
                        <input type="text" name="person_address" id="person_address" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="person_licence">Licence</label>
                        <input type="text" name="person_licence" id="person_licence" class="form-input" required>
                    </div>
                <button type="submit" name="add_vehicle_submit" class="form-button">Add Person</button>
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
            ?>
        </div>
    </div>
</body>
</html>
