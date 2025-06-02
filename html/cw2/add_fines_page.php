<?php
  ob_start(); // Start output buffering
  // session_start(); // Start the session
  include('nav_bar.php');
  require('audit_logger.php');
?>

<?php
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


  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if form is submitted with all required inputs
    if (!empty($_POST['incident_id']) && !empty($_POST['fine_amount']) && !empty($_POST['fine_points'])) {
      $incident_id = $_POST['incident_id'];
      $fine_amount = $_POST['fine_amount'];
      $fine_points = $_POST['fine_points'];

      // SQL to get max fine and points for the selected incident
      $sql = "SELECT O.Offence_maxFine, O.Offence_maxPoints 
              FROM Offence O 
              INNER JOIN Incident I ON O.Offence_ID = I.Offence_ID 
              WHERE I.Incident_ID = '$incident_id';";
      $result = mysqli_query($conn, $sql);
      if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $offence_maxFine = $row["Offence_maxFine"];
        $offence_maxPoints = $row["Offence_maxPoints"];

        // Check if fine and points exceed maximum
        if ($fine_amount > $offence_maxFine && $fine_points > $offence_maxPoints) {
          $message = "Both the fine amount and fine points have exceeded their maximum value.";
        } elseif ($fine_amount > $offence_maxFine) {
          $message = "Fine amount given has exceeded maximum value.";
        } elseif ($fine_points > $offence_maxPoints) {
          $message = "Fine points given has exceeded maximum value.";
        } else {
          // Insert fine into the Fines table
          $insert_sql = "INSERT INTO Fines (Fine_Amount, Fine_Points, Incident_ID) 
                         VALUES ('$fine_amount', '$fine_points', '$incident_id')";
          if (mysqli_query($conn, $insert_sql)) {
            $fine_id = mysqli_insert_id($conn);
            $success = "New fine has been added with Fine ID: $fine_id.";

            $report = "Fine ID: $fine_id, was created";

            // Check if user is logged in before logging audit
            if(isset($_SESSION['id'])) {
              // Log the audit trail
              logAuditTrail(
                  $_SESSION['id'],        // Current user ID
                  'Fines',            // Table name
                  $fine_id,          // Record ID
                  'INSERT',              // Action type
                  $report                // Additional details
              );
            }
          } else {
            $message = "Error inserting fine: " . mysqli_error($conn);
          }
        }

        // Storing the messages in the session
        $_SESSION['message'] = $message;
        $_SESSION['success'] = $success;

        // Redirect to the same page to avoid resubmission
        ob_end_clean();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
      }
    }

    // If only the incident is selected, display its max fine and max points
    if (!empty($_POST['incident_id']) && (empty($_POST['fine_amount']) || empty($_POST['fine_points']))) {
      $incident_id = $_POST['incident_id'];

      // SQL to get max fine and points for the selected incident
      $sql = "SELECT O.Offence_maxFine, O.Offence_maxPoints 
              FROM Offence O 
              INNER JOIN Incident I ON O.Offence_ID = I.Offence_ID 
              WHERE I.Incident_ID = '$incident_id';";
      $result = mysqli_query($conn, $sql);
      if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $offence_maxFine = $row["Offence_maxFine"];
        $offence_maxPoints = $row["Offence_maxPoints"];
      }
    }
  }
  ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Fine</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>

    <div class="main-content">
        <div class="page-header">
            <h1>Add a Fine</h1>
            <p>Select an incident and enter fine details</p>
        </div>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="incident_id">Incident ID</label>
                    <select name="incident_id" id="incident_id" class="form-input" onchange="this.form.submit()" required>
                        <option value="">Select Incident</option>
                        <?php
                        // SQL query to fetch Incident_IDs that are not linked to any Fine
                        $incident_sql = "
                            SELECT Incident.Incident_ID, Incident.Incident_Report 
                            FROM Incident
                            LEFT JOIN Fines ON Incident.Incident_ID = Fines.Incident_ID
                            WHERE Fines.Incident_ID IS NULL";  
                        $incident_result = mysqli_query($conn, $incident_sql);
                        if (mysqli_num_rows($incident_result) > 0) {
                            while ($incident_row = mysqli_fetch_assoc($incident_result)) {
                                $selected = (isset($_POST['incident_id']) && $_POST['incident_id'] == $incident_row['Incident_ID']) ? 'selected' : '';
                                echo "<option value='" . $incident_row['Incident_ID'] . "' $selected>" . $incident_row['Incident_ID'] . " - " . $incident_row['Incident_Report'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No incidents available</option>";
                        }
                        ?>
                    </select>
                </div>

                <?php if (isset($offence_maxFine) && isset($offence_maxPoints)): ?>
                    <div class="alert-success">
                        <p>Max fine is: £<?= $offence_maxFine ?></p>
                        <p>Max points are: <?= $offence_maxPoints ?></p>
                    </div>
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="fine_amount">Fine Amount</label>
                        <input type="text" name="fine_amount" id="fine_amount" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="fine_points">Fine Points</label>
                        <input type="text" name="fine_points" id="fine_points" class="form-input" required>
                    </div>
                </div>

                <button type="submit" name="search_fine_submit" class="form-button">Add Fine</button>
            </form>

            <!-- Message Display -->
            <?php
            if (isset($_SESSION['message'])) {
                echo "<div class='alert-error'>" . $_SESSION['message'] . "</div>";
                unset($_SESSION['message']); // Clear the message so it doesn't show again
            }
            if (isset($_SESSION['success'])) {
                echo "<div class='alert-success'>" . $_SESSION['success'] . "</div>";
                unset($_SESSION['message']); // Clear the message so it doesn't show again
            }
            ?>
        </div>
    </div>
</body>
</html>


































