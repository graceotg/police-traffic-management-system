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
  $person_result = null;

  if(isset($_POST['name']) && $_POST['name']!="" &&
      isset($_POST['licence_number']) && $_POST['licence_number']!=""){
    $person_sql = "SELECT * FROM People WHERE People_name = '".$_POST['name']."' and People_licence = '".$_POST['licence_number']."';";
    $person_result = mysqli_query($conn, $person_sql);
    
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Person</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>

    <div class="main-content">
        <div class="page-header">
            <h1>Search Person</h1>
            <p>Enter full name and licence</p>
        </div>

        <div class="form-container">
            <!-- Search for Person Form -->
            <form id="searchPersonForm" method="POST">
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="licence_number">Licence Number</label>
                    <input type="text" name="licence_number" id="licence_number" class="form-input" required>
                </div>

                <button type="submit" name="lookup_person_submit" class="form-button">Look Up Person</button>
            </form>
            <?php
              if ($person_result !== null) {
                  // check that something has been returned                                       
                  if (mysqli_num_rows($person_result) > 0) 
                  {
                     echo "<table>";  
                     // table header
                     echo "<tr><th>ID</th><th>Name</th><th>Address</th><th>Licence Number</th></tr>"; 
                     
                     // loop through each row of the result
                     while($row = mysqli_fetch_assoc($person_result)) 
                     {
                       // output table content
                       echo "<tr>";
                       echo "<td>".$row["People_ID"]."</td>"; 
                       echo "<td>".$row["People_name"]."</td>"; 
                       echo "<td>".$row["People_address"]."</td>"; 
                       echo "<td>".$row["People_licence"]."&nbsp;&nbsp;";
                       
                       // Delete button executes JavaScript confirmDelete          
                       // echo "<button onclick=confirmDelete(".$row["ID"].")>Delete</button></td>";
                      
                       echo "</tr>";
                     } 
                     echo "</table>"; 
                  }
                  else // if query result is empty 
                  {
                      echo "No one was found";
                  }
              } 
            ?>
        </div>
    </div>

    


</body>
</html>
