<?php
require("db.info.php");

// Open the database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
                           
// Check if connection failed
if(mysqli_connect_errno()) {
    $error = 'Failed to connect to MySQL: " . mysqli_connect_error() . "'; 
    die();
} 

// Function to log database changes
function logAuditTrail($user_id, $table_name, $record_id, $action, $details) {
    global $conn;
    
    // Check if all required parameters are set and not empty
    if(isset($user_id) && $user_id != "" &&
       isset($table_name) && $table_name != "" &&
       isset($record_id) && $record_id != "" &&
       isset($action) && $action != "") {
        
        // Convert details array to JSON string if it exists
        $details_json = "";
        if(isset($details) && $details != "") {
            $details_json = json_encode($details);
        }
        
        // Create audit log entry
        $insert_sql = "INSERT INTO AuditLogs (user_id, table_name, record_id, action, details, created_at) 
                      VALUES ('$user_id', '$table_name', '$record_id', '$action', '$details_json', NOW())";
        
        // Try to insert the audit log
        if(mysqli_query($conn, $insert_sql)) {
            $audit_id = mysqli_insert_id($conn);
            return true;
        } else {
            $error = "Error creating audit log: " . mysqli_error($conn);
            return false;
        }
    } else {
        $error = "Missing required audit log parameters";
        return false;
    }
}
?>
