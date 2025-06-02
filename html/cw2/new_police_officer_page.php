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
  if(mysqli_connect_errno()) 
  {
     echo "Failed to connect to MySQL: ".mysqli_connect_error();
     die();
  } 
  // else echo "MySQL connection OK<br>";  // useful for testing


  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["officer_name"]) && !empty($_POST["officer_name"]) &&
      isset($_POST["officer_cred"]) && !empty($_POST["officer_cred"]) &&
      isset($_POST["officer_username"]) && !empty($_POST["officer_username"]) &&
      isset($_POST["officer_password"]) && !empty($_POST["officer_password"])) {

    // New Officer Variables
    $officer_name = $_POST["officer_name"];
    $officer_cred = $_POST["officer_cred"];
    $officer_username = $_POST["officer_username"];
    $officer_password = $_POST["officer_password"];

    $insert_sql = "INSERT INTO Officers (Officer_name, Officer_credentials, Username, Password) 
                  VALUES ('$officer_name', '$officer_cred', '$officer_username', '$officer_password');";

    if (mysqli_query($conn, $insert_sql)) {
      $officer_id = mysqli_insert_id($conn);
      $message = "New Officer with ID: $officer_id has been added successfully";

      $report = "Officer with ID: $officer_id, was created";

      // Check if user is logged in before logging audit
      if(isset($_SESSION['id'])) {
        // Log the audit trail
        logAuditTrail(
            $_SESSION['id'],        // Current user ID
            'Officers',            // Table name
            $officer_id,          // Record ID
            'INSERT',              // Action type
            $report                // Additional details
        );
      }
    } else {
      $message = "Error: Unable to add officer";
    }
    // Store the message in the session
      $_SESSION['message'] = $message;

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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add New Officer</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
  <div class="main-content">
    <div class="page-header">
      <h1>Add New Officer</h1>
      <p>Fill in all the fields below</p>   
    </div>

    <div class="form-container">
      <form method="POST">
        <div class="form-grid">

          <div class="form-group">
            <label class="form-label" for="officer_name">Full Name:</label>
            <input type="text" name="officer_name" id="officer_name" class="form-input" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="officer_cred">Credentials:</label>
            <div>
              <input type="radio" id="officer" name="officer_cred" value="Officer" required>
              <label for="officer">Officer</label>  

              <input type="radio" id="administrator" name="officer_cred" value="Administrator" required>
              <label for="administrator">Administrator</label>  

              <input type="radio" id="detective" name="officer_cred" value="Detective" required>
              <label for="detective">Detective</label>  

              <input type="radio" id="sergeant" name="officer_cred" value="Sergeant" required>
              <label for="sergeant">Sergeant</label>  
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="officer_username">Username:</label>
            <input type="text" name="officer_username" id="officer_username" class="form-input" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="officer_password">Password:</label>
            <input type="password" name="officer_password" id="officer_password" class="form-input" required>
          </div>

        </div>

        <button type="submit" name="new_officer_submit" class="form-button">Add Officer</button>
      </form>

      <!-- Message Display -->
      <?php
      if (isset($_SESSION['message'])) {
          echo "<div class='alert-success'>" . $_SESSION['message'] . "</div>";
          unset($_SESSION['message']); // Clear the message so it doesn't show again
      }
      ?>
    </div>
  </div>
</body>
</html>

