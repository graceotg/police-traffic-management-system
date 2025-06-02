<?php
  ob_start(); // Start output buffering
  //session_start(); // Start the session
  include('nav_bar.php');
?>
<?php

    // Session variables
    $username = $_SESSION['username'];
    $cred = $_SESSION['credentials'];

    require("db.info.php");

    $message = "";
   
    // Open the database connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if(mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: ".mysqli_connect_error();
        die();
    }

    // Find the officer's name based on the username
    $sql = "SELECT * FROM Officers WHERE Username = '".$_SESSION['username']."';";
    $result = mysqli_query($conn, $sql);

    $row = mysqli_fetch_assoc($result);

    // Extract officer details
    $officer_name = $row["Officer_name"];
    $officer_id = $row["Officer_ID"];
    $officer_credentials = $row["Officer_credentials"];
    $username = $row["Username"];
    $password = $row["Password"];

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        if (isset($_POST['change_password'])){
            $old_password = $_POST['old_password']; // Get the old password
            $new_password = $_POST['new_password']; // Get the new password
            $retype_password = $_POST['retype_password']; // Get retyped password

            if ($old_password == $password){
                if ($new_password == $retype_password){
                    $updated_sql = "UPDATE Officers SET Password = '$new_password' WHERE Username = '$username';";
                    if (mysqli_query($conn, $updated_sql)) {
                        $_SESSION['success'] = "Password changed successfully!";
                        ob_end_clean();
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $_SESSION['error'] = "Error updating password";
                        ob_end_clean();
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "Passwords do not match!";
                    ob_end_clean();
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } else {
                $_SESSION['error'] = "Old password is incorrect";
                ob_end_clean();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }

    ob_end_flush();
    mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Department</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<style>
    /* Hide the form initially */
    #passwordForm {
          display: none;
          margin-top: 20px;
    }
</style>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1>Welcome, Officer <?php echo htmlspecialchars($officer_name); ?>!</h1>
        </div>
        <div class="form-container">
            <table>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Credentials</th>
                    <th>Username</th>
                    <th>Password</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($officer_id); ?></td>
                    <td><?php echo htmlspecialchars($officer_name); ?></td>
                    <td><?php echo htmlspecialchars($officer_credentials); ?></td>
                    <td><?php echo htmlspecialchars($username); ?></td>
                    <td><?php echo htmlspecialchars($password); ?></td>
                </tr>
            </table>

            <button onclick="changePasswordFunc()">Change Password</button></br>

            <form id="passwordForm" method="POST">
                Old Password: <input type="text" name="old_password"><br/>
                New Password: <input type="text" name="new_password"><br/>
                Re-type new password: <input type="text" name="retype_password"><br/>
                <input type="submit" name="change_password" value="Submit">
            </form>

            <?php
                if (!empty($_SESSION['error'])) {
                    echo "<div class='alert-error'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                if (!empty($_SESSION['success'])) {
                    echo "<div class='alert-success'>" . $_SESSION['success'] . "</div>";
                    unset($_SESSION['success']);
                }
            ?>

        </div>
    </div>

    <script>
        function changePasswordFunc() {
            document.getElementById('passwordForm').style.display = 'block';
            document.querySelector('button').style.display = 'none';
        }

    </script>
</body>
</html>
