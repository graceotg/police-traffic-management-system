<?php 
include('nav_bar.php');
require('audit_logger.php');
?>


<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Search Incident</title>
  <link rel="stylesheet" href="stylesheet.css">
  <script>
    function toggleEditMode(button) {
      // Find the parent row
      const row = button.closest('tr');
      
      // Toggle between view and edit modes
      if (button.value === 'Edit') {
        // Switch to edit mode
        row.classList.add('edit-mode');
        button.value = 'Cancel';
        button.textContent = 'Cancel';
        
        // Show save button, hide edit button
        const saveButton = row.querySelector('.save-button');
        if (saveButton) {
          saveButton.style.display = 'inline-block';
          button.style.display = 'none';
        }
      } else {
        // Switch back to view mode
        row.classList.remove('edit-mode');
        button.value = 'Edit';
        button.textContent = 'Edit';
        
        // Hide save button, show edit button
        const saveButton = row.querySelector('.save-button');
        if (saveButton) {
          saveButton.style.display = 'none';
          button.style.display = 'inline-block';
        }
      }
    }

    function confirmDelete(incidentId) {
      if (confirm('Are you sure you want to delete this incident and all related records?')) {
        window.location.href = 'search_incident.php?delete=' + incidentId;
      }
    }
  </script>
</head>
<body>
  <div class="main-content">
    <div class="page-header">
      <h1>Search Incident</h1>
      <p>Give the incident ID</p>
    </div>
    <div class="form-container">
      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label for="incident_id" class="form-label">Incident ID:</label>
            <input type="text" id="incident_id" name="incident_id" required class="form-input">
          </div>
          <div class="form-group">
            <input type="submit" name="incident_id_submit" value="Submit" class="form-button">
          </div>
        </div>
      </form>
    </div>
    <?php
    require("db.info.php");
    // Open the database connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // Check connection
    if(mysqli_connect_errno()) {
      echo "Failed to connect to MySQL: ".mysqli_connect_error();
      die();   
    } 

    // Handle deletion with cascade
    if(isset($_GET['delete'])) {
      $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
      
      // Start a transaction to ensure all deletes happen or none
      mysqli_begin_transaction($conn);
      
      try {
        // Delete related records in Fines table first
        $delete_fines_sql = "DELETE FROM Fines WHERE Incident_ID = '$delete_id'";
        mysqli_query($conn, $delete_fines_sql);
        
        // Then delete the incident
        $delete_incident_sql = "DELETE FROM Incident WHERE Incident_ID = '$delete_id'";
        mysqli_query($conn, $delete_incident_sql);
        
        // Commit the transaction
        mysqli_commit($conn);
        
        echo "<div class='alert-success'>Incident and related records assocites with ID: $delete_id are deleted successfully</div>";

        $report = "Incident ID: $delete_id, was deleted";
        
        if(isset($_SESSION['id'])) {
          // Log the audit trail
          logAuditTrail(
              $_SESSION['id'],       // Current user ID
              'Incident',           // Table name
              $delete_id,          // Record ID
              'DELETE',            // Action type
              $report             // Additional details
          );
        }
      } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        mysqli_rollback($conn);
        echo "<div class='alert-error'>Error deleting incident: " . $e->getMessage() . "</div>";
      }
    }

    // Handle edit form submission
    if(isset($_POST['edit_incident'])) {
      $incident_id = mysqli_real_escape_string($conn, $_POST['Incident_ID']);
      $offence_id = mysqli_real_escape_string($conn, $_POST['Offence_ID']);
      $vehicle_id = mysqli_real_escape_string($conn, $_POST['Vehicle_ID']);
      $people_id = mysqli_real_escape_string($conn, $_POST['People_ID']);
      $incident_date = mysqli_real_escape_string($conn, $_POST['Incident_Date']);
      $incident_time = mysqli_real_escape_string($conn, $_POST['Incident_Time']);
      $incident_report = mysqli_real_escape_string($conn, $_POST['Incident_Report']);

      $update_sql = "UPDATE Incident SET 
                     Offence_ID = '$offence_id', 
                     Vehicle_ID = '$vehicle_id', 
                     People_ID = '$people_id', 
                     Incident_Date = '$incident_date', 
                     Incident_Time = '$incident_time', 
                     Incident_Report = '$incident_report' 
                     WHERE Incident_ID = '$incident_id'";
      
      if(mysqli_query($conn, $update_sql)) {
        // echo "<div class='alert-success'>Incident updated successfully</div>";
        echo "<div class='alert-success'>Incident with ID: '$incident_id' was updated successfully</div>";

        $report = "Incident ID: $incident_id, was updated";

        // Check if user is logged in before logging audit
        if(isset($_SESSION['id'])) {
          // Log the audit trail
          logAuditTrail(
              $_SESSION['id'],        // Current user ID
              'Incident',            // Table name
              $incident_id,          // Record ID
              'UPDATE',              // Action type
              $report                // Additional details
          );
        }
      } else {
        echo "<div class='alert-error'>Error updating incident: " . mysqli_error($conn) . "</div>";
      }
    }

    if(isset($_POST['incident_id']) && $_POST['incident_id']!=""){
      $incident_id = mysqli_real_escape_string($conn, $_POST['incident_id']);
      $sql = "SELECT * FROM Incident WHERE Incident_ID = '$incident_id'";
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        
        // Display table
        echo "<table>";
        echo "<tr>
                <th>Incident ID</th>
                <th>Offence ID</th>
                <th>Vehicle ID</th>
                <th>People ID</th>
                <th>Incident Date</th>
                <th>Incident Time</th>
                <th>Incident Report</th>
                <th>Actions</th>
              </tr>";
        
        echo "<tr class='view-mode'>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='Incident_ID' value='".$row["Incident_ID"]."'>";
        
        // View mode cells
        echo "<td class='view-cell'>".$row["Incident_ID"]."</td>";
        echo "<td class='view-cell'>".$row["Offence_ID"]."</td>";  
        echo "<td class='view-cell'>".$row["Vehicle_ID"]."</td>"; 
        echo "<td class='view-cell'>".$row["People_ID"]."</td>";
        echo "<td class='view-cell'>".$row["Incident_Date"]."</td>";
        echo "<td class='view-cell'>".$row["Incident_Time"]."</td>"; 
        echo "<td class='view-cell'>".$row["Incident_Report"]."</td>";
        
        // Edit mode cells (hidden by default)
        echo "<td class='edit-cell'>".$row["Incident_ID"]."</td>";
        echo "<td class='edit-cell'><input type='text' name='Offence_ID' value='".$row["Offence_ID"]."'></td>";  
        echo "<td class='edit-cell'><input type='text' name='Vehicle_ID' value='".$row["Vehicle_ID"]."'></td>"; 
        echo "<td class='edit-cell'><input type='text' name='People_ID' value='".$row["People_ID"]."'></td>";
        echo "<td class='edit-cell'><input type='date' name='Incident_Date' value='".$row["Incident_Date"]."'></td>";
        echo "<td class='edit-cell'><input type='time' name='Incident_Time' value='".$row["Incident_Time"]."'></td>"; 
        echo "<td class='edit-cell'><textarea name='Incident_Report'>".$row["Incident_Report"]."</textarea></td>";
        
        // Action buttons
        echo "<td>
                <input type='button' value='Edit' class='form-button edit-button' onclick='toggleEditMode(this)'>
                <input type='submit' name='edit_incident' value='Save' class='form-button save-button' style='display:none;'>
                <button type='button' class='form-button' onclick='confirmDelete(\"".$row["Incident_ID"]."\")'>Delete</button>
              </td>";
        echo "</form>";
        echo "</tr>";
        echo "</table>";
      } else {
        echo "<div class='alert-error'>Incident does not exist</div>";
        echo '<div class="alert-error"><a href="add_incident.php">Click here to add a new incident</a></div>';
      }
    }
    ?>
  </div>
</body>
</html>