<?php
include('nav_bar.php');
require("db.info.php");
require('audit_logger.php');


// Open the database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if(mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: ".mysqli_connect_error();
    die();
}

// Initialize variables 
$fine_result = null;  // Variable to check fine was found
$id_exists = false;   // Variable to check id was set
$message = "";  // Variable to hold success/error messages

// Handle search for Fine ID
if(isset($_POST['search_fine_id']) && $_POST['search_fine_id'] != ""){
    $fine_id = $_POST['search_fine_id'];
    $sql = "SELECT * FROM Fines WHERE Fine_ID = $fine_id";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $id_exists = true;
        $fine_sql = "SELECT F.Fine_ID, F.Fine_Amount, F.Fine_Points, F.Incident_ID, I.Incident_Date, I.Incident_Time, I.Incident_Report, O.Offence_ID, O.Offence_description 
                    FROM Fines F 
                    INNER JOIN Incident I ON F.Incident_ID = I.Incident_ID 
                    INNER JOIN Offence O ON I.Offence_ID = O.Offence_ID 
                    WHERE F.Fine_ID = '$fine_id';";
        $fine_result = mysqli_query($conn, $fine_sql);
    }
}

// Handle deletion
if(isset($_GET['delete'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Start a transaction to ensure all deletes happen or none
    mysqli_begin_transaction($conn);
    
    try {
        // Delete the fine
        $delete_fine_sql = "DELETE FROM Fines WHERE Fine_ID = '$delete_id'";
        mysqli_query($conn, $delete_fine_sql);
        
        // Commit the transaction
        mysqli_commit($conn);
        
        $message = "<div class='alert-error'>Fine and related records with ID: $delete_id are deleted successfully</div>";

        $report = "Fines ID: $delete_id, was deleted";
        
        if(isset($_SESSION['id'])) {
          // Log the audit trail
          logAuditTrail(
              $_SESSION['id'],       // Current user ID
              'Fines',           // Table name
              $delete_id,          // Record ID
              'DELETE',            // Action type
              $report             // Additional details
          );
        }
    } catch (Exception $e) {
        // Rollback the transaction if something went wrong
        mysqli_rollback($conn);
        echo "<div class='alert-error'>Error deleting fine: " . $e->getMessage() . "</div>";
    }
}

// Handle edit form submission
if(isset($_POST['edit_fine'])) {
    $fine_id = mysqli_real_escape_string($conn, $_POST['Fine_ID']);
    $fine_amount = mysqli_real_escape_string($conn, $_POST['Fine_Amount']);
    $fine_points = mysqli_real_escape_string($conn, $_POST['Fine_Points']);
    $incident_id = mysqli_real_escape_string($conn, $_POST['Incident_ID']);
    $incident_date = mysqli_real_escape_string($conn, $_POST['Incident_Date']);
    $incident_time = mysqli_real_escape_string($conn, $_POST['Incident_Time']);
    $incident_report = mysqli_real_escape_string($conn, $_POST['Incident_Report']);
    $offence_id = mysqli_real_escape_string($conn, $_POST['Offence_ID']);
    $offence_description = mysqli_real_escape_string($conn, $_POST['Offence_description']);

    $update_sql = "UPDATE Fines F
                    INNER JOIN Incident I ON F.Incident_ID = I.Incident_ID
                    INNER JOIN Offence O ON I.Offence_ID = O.Offence_ID
                    SET F.Fine_Amount = '$fine_amount',
                        F.Fine_Points = '$fine_points',
                        F.Incident_ID = '$incident_id', 
                        I.Incident_Date = '$incident_date',
                        I.Incident_Time = '$incident_time',
                        I.Incident_Report = '$incident_report',
                        O.Offence_ID = '$offence_id',
                        O.Offence_description = '$offence_description'
                    WHERE F.Fine_ID = '$fine_id';
                    ";

    if(mysqli_query($conn, $update_sql)) {
        $message =  "<div class='alert-success'>Fine with ID: '$fine_id' was updated successfully</div>";

        $report = "Fine ID: $fine_id, was updated";

        // Check if user is logged in before logging audit
        if(isset($_SESSION['id'])) {
          // Log the audit trail
          logAuditTrail(
              $_SESSION['id'],        // Current user ID
              'Fines',                // Table name
              $fine_id,               // Record ID
              'UPDATE',              // Action type
              $report                // Additional details
          );
        }
    } else {
        $message = "<div class='alert-error'>Error updating fine: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Search Fines</title>
  <link rel="stylesheet" href="stylesheet.css">
  <script>
    function toggleEditMode(button) {
      const row = button.closest('tr');
      const cells = row.querySelectorAll('.view-cell');

      if (button.value === 'Edit') {
        row.classList.add('edit-mode');
        button.value = 'Cancel';
        button.textContent = 'Cancel';
        
        cells.forEach(cell => {
          let input = document.createElement('input');
          input.type = 'text';
          input.value = cell.textContent;
          input.classList.add('edit-input');
          cell.innerHTML = '';
          cell.appendChild(input);
        });

        const saveButton = row.querySelector('.save-button');
        saveButton.style.display = 'inline-block';
        button.style.display = 'none';
      } else {
        row.classList.remove('edit-mode');
        button.value = 'Edit';
        button.textContent = 'Edit';
        
        const saveButton = row.querySelector('.save-button');
        saveButton.style.display = 'none';
        button.style.display = 'inline-block';

        cells.forEach(cell => {
          let inputValue = cell.querySelector('input').value;
          cell.textContent = inputValue;
        });
      }
    }

    function confirmDelete(fineId) {
      if (confirm('Are you sure you want to delete this fine?')) {
        window.location.href = 'search_fines_page.php?delete=' + fineId;
      }
    }
  </script>
</head>
<body>
  <div class="main-content">
    <div class="page-header">
      <h1>Search Fines</h1>
      <p>Enter a fine ID number to view the details</p>
    </div>

    <div class="form-container">
      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="search_fine_id">Fine ID</label>
            <input type="text" id="search_fine_id" name="search_fine_id" class="form-input" required>
          </div>
        </div>
        <button type="submit" name="search_fine_submit" class="form-button">Search</button>
      </form>
    </div>

    <?php 
        if (isset($_POST['search_fine_id']) && !$id_exists) {
            echo "<div class='alert-error'>No fines found for the entered Fine ID</div>";
            echo '<div class="alert-error"><a href="add_fines_page.php">Click here to add a new fine</a></div>';
        }

        if ($fine_result && mysqli_num_rows($fine_result) > 0) {
            echo "<table>"; 
            echo "<tr>
                    <th>Fine ID</th> 
                    <th>Fine Amount</th> 
                    <th>Fine Points</th> 
                    <th>Incident ID</th> 
                    <th>Incident Date</th> 
                    <th>Incident Time</th> 
                    <th>Incident Report</th> 
                    <th>Offence ID</th> 
                    <th>Offence Description</th> 
                    <th>Actions</th>
                  </tr>"; 

            while($row = mysqli_fetch_assoc($fine_result)) { 
                echo "<tr class='view-mode'>"; 
                echo "<form method='POST'>";
                echo "<input type='hidden' name='Fine_ID' value='".$row["Fine_ID"]."'>";

                // View mode cells
                echo "<td class='view-cell'>".$row["Fine_ID"]."</td>"; 
                echo "<td class='view-cell'>".$row["Fine_Amount"]."</td>"; 
                echo "<td class='view-cell'>".$row["Fine_Points"]."</td>"; 
                echo "<td class='view-cell'>".$row["Incident_ID"]."</td>"; 
                echo "<td class='view-cell'>".$row["Incident_Date"]."</td>"; 
                echo "<td class='view-cell'>".$row["Incident_Time"]."</td>"; 
                echo "<td class='view-cell'>".$row["Incident_Report"]."</td>"; 
                echo "<td class='view-cell'>".$row["Offence_ID"]."</td>"; 
                echo "<td class='view-cell'>".$row["Offence_description"]."</td>"; 

                
                // Edit mode cells (hidden by default)
                echo "<td class='edit-cell'><input type='text' name='Fine_ID' value='".$row["Fine_ID"]."' readonly></td>"; 
                echo "<td class='edit-cell'><input type='text' name='Fine_Amount' value='".$row["Fine_Amount"]."'></td>"; 
                echo "<td class='edit-cell'><input type='text' name='Fine_Points' value='".$row["Fine_Points"]."'></td>"; 
                echo "<td class='edit-cell'><input type='text' name='Incident_ID' value='".$row["Incident_ID"]."'></td>"; 
                echo "<td class='edit-cell'><input type='date' name='Incident_Date' value='".$row["Incident_Date"]."'></td>"; 
                echo "<td class='edit-cell'><input type='time' name='Incident_Time' value='".$row["Incident_Time"]."'></td>"; 
                echo "<td class='edit-cell'><textarea name='Incident_Report'>".$row["Incident_Report"]."</textarea></td>"; 
                echo "<td class='edit-cell'><input type='text' name='Offence_ID' value='".$row["Offence_ID"]."'></td>"; 
                echo "<td class='edit-cell'><input type='text' name='Offence_description' value='".$row["Offence_description"]."'></td>";

                // Action buttons
                echo "<td>
                        <input type='button' value='Edit' class='form-button edit-button' onclick='toggleEditMode(this)'>
                        <input type='submit' name='edit_fine' value='Save' class='form-button save-button' style='display:none;'>
                        <button type='button' class='form-button' onclick='confirmDelete(".$row["Fine_ID"].")'>Delete</button>
                      </td>";

                echo "</form>";
                echo "</tr>"; 
            } 
            echo "</table>"; 
        }
    ?>
    <?php
      // Display the success/error message
        if ($message != "") {
            echo $message;
        }  
    ?>
  </div>
</body>
</html>
