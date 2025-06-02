<?php
session_start();  // Start the session to store data

require("db.info.php");

// Open the database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die();
}

// Declaring variables
$result = null;

// Check if form is submitted
if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Construct the SELECT query
    $sql = "SELECT * FROM Officers WHERE Username = '$username' AND Password = '$password';";
    
    // Send query to database
    $result = mysqli_query($conn, $sql);

    // if (mysqli_num_rows($result) > 0) {
    //     $row = mysqli_fetch_assoc($result);
    //     $_SESSION['username'] = $username;  // Save the username in the session
    //     $_SESSION['credentials'] = $row['Officer_credentials']; // Save the credentials in the session
        
    //     // Login successful, redirect to welcome page
    //     echo "<script type='text/javascript'>
    //             window.location.href = 'welcome_page.php';
    //           </script>";
    //     exit(); 
    // } else {
    //     echo "Invalid username or password!";
    // }
}

// mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Department - Log In</title>
    <!-- Importing the CSS file -->
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>
    <div class="main-content">
        <div class="page-header">
          <h1>Log In</h1>  
        </div>
        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" id="username" name="username" required class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" id="password" name="password" required class="form-input">
                    </div>

                    <div class="form-group">
                        <input type="submit" name="login" value="Log In" class="form-button">
                    </div>
                </div>
            </form>
            <?php
                if($result !== null){
                    if (mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $_SESSION['username'] = $username;  // Save the username in the session
                        $_SESSION['credentials'] = $row['Officer_credentials']; // Save the credentials in the session
                        $_SESSION['id'] = $row['Officer_ID']; // Save the ID in the session
                        
                        // Login successful, redirect to welcome page
                        echo "<script type='text/javascript'>
                                window.location.href = 'welcome_page.php';
                              </script>";
                        exit(); 
                    } else {
                        echo "<div class='alert-error'>Invalid username or password</div>";
                    }
                }  
            ?>
        </div>
    </div>
</body>
</html>

