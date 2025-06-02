<?php include('nav_bar.php'); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Search Officer</title>
  <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
  <div class="main-content">
    <div class="page-header">
      <h1>Search Officer</h1>
      <p>Give the officer's full name and ID number</p>
    </div>
    <div class="form-container">
      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label for="full_name" class="form-label">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required class="form-input">
          </div>
          <div class="form-group">
            <label for="officer_id" class="form-label">Officer ID:</label>
            <input type="text" id="officer_id" name="officer_id" required class="form-input">
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

        if(isset($_POST['full_name']) && $_POST['full_name']!="" &&
            isset($_POST['officer_id']) && $_POST['officer_id']!=""){
          
          $full_name = $_POST['full_name'];
          $officer_id = $_POST['officer_id'];
          

          $sql = "SELECT * FROM Officers WHERE Officer_ID = $officer_id AND Officer_name = '$full_name'";
          $result = mysqli_query($conn, $sql);

          if (mysqli_num_rows($result) > 0){
            echo "<table>";

            // table header
            echo "<tr><th>Officer's ID</th><th>Officer's Name</th><th>Officer's Credentials</th><th>Username</th><th>Password</th></tr>";

            // loop through each row of the result
            while($row = mysqli_fetch_assoc($result)) {
              // output table content
              echo "<tr>";
              echo "<td>".$row["Officer_ID"]."</td>";
              // echo "<td>" . htmlspecialchars($row["Officer_name"], ENT_QUOTES, 'UTF-8') . "</td>";
              echo "<td>".$row["Officer_name"]."</td>";  
              echo "<td>".$row["Officer_credentials"]."</td>"; 
              echo "<td>".$row["Username"]."</td>";
              echo "<td>".$row["Password"]."&nbsp;&nbsp;";
              echo "</tr>";
            }
            echo "</table>";
          } else {
            echo "<div class='alert-error'>Officer does not exist</div>";
            echo '<div class="alert-error"><a href="new_police_officer_page.php">Click here to add a new officer</a></div>';
          }
        }
      ?>
  </div>
</body>
</html>