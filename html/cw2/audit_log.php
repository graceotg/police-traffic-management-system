<?php
    ob_start(); // Start output buffering
    include('nav_bar.php');
?>

<?php 
    require("db.info.php"); 
        
    // Open the database connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
                           
    // Check connection
    if(mysqli_connect_errno()) {
        $error = 'Failed to connect to MySQL: " . mysqli_connect_error() . "'; 
        die();
    } 
    
    // Base query
    $audit_sql = "SELECT AuditLogs.*, Officers.Username 
                  FROM AuditLogs 
                  LEFT JOIN Officers ON AuditLogs.user_id = Officers.Officer_ID 
                  WHERE 1=1";
    
    // Add filters if they are set
    if(isset($_POST['user_id']) && $_POST['user_id']!="") {
        $user_id = $_POST['user_id'];
        $audit_sql .= " AND AuditLogs.user_id = '$user_id'";
    }

    if(isset($_POST['action']) && $_POST['action']!="") {
        $action = $_POST['action'];
        $audit_sql .= " AND AuditLogs.action = '$action'";
    }

    if(isset($_POST['created_date']) && $_POST['created_date']!="") {
        $created_date = $_POST['created_date'];
        $audit_sql .= " AND DATE(AuditLogs.created_at) = '$created_date'";
    }

    // Add order by
    $audit_sql .= " ORDER BY created_at DESC";

    $audit_result = mysqli_query($conn, $audit_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Audit Trail</title>
    <link rel="stylesheet" href="stylesheet.css">
    <style>
        .container {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .form-grid {
            display: flex;
            gap: 10px;  /* Reduced gap */
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
            min-width: 0;  /* Added this to prevent overflow */
        }
        .form-input {
            width: 100%;  /* Make inputs take full width of their container */
            box-sizing: border-box;  /* Include padding in width calculation */
        }
        .form-button {
            width: 150px;
            height: 35px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <h1>Database Audit Trail</h1>
            
            <div class="filter-section">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="user_id">Filter by User:</label>
                            <select name="user_id" id="user_id" class="form-input">
                                <option value="">All Users</option>
                                <?php
                                $user_sql = "SELECT Officer_ID, Username FROM Officers ORDER BY Username";
                                $user_result = mysqli_query($conn, $user_sql);
                                if (mysqli_num_rows($user_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($user_result)) {
                                        $selected = (isset($_POST['user_id']) && $_POST['user_id'] == $row['Officer_ID']) ? 'selected' : '';
                                        echo "<option value='" . $row['Officer_ID'] . "' $selected>" . 
                                             $row['Username'] . " (ID: " . $row['Officer_ID'] . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="action">Action:</label>
                            <select name="action" id="action" class="form-input">
                                <option value="">All Actions</option>
                                <option value="INSERT" <?php echo (isset($_POST['action']) && $_POST['action'] == 'INSERT') ? 'selected' : ''; ?>>Insert</option>
                                <option value="UPDATE" <?php echo (isset($_POST['action']) && $_POST['action'] == 'UPDATE') ? 'selected' : ''; ?>>Update</option>
                                <option value="DELETE" <?php echo (isset($_POST['action']) && $_POST['action'] == 'DELETE') ? 'selected' : ''; ?>>Delete</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="created_date">Date:</label>
                            <input type="date" name="created_date" id="created_date" class="form-input" 
                                   value="<?php echo isset($_POST['created_date']) ? $_POST['created_date'] : ''; ?>">
                        </div>

                    </div>
                    <button type="submit" class="form-button">Apply Filters</button>
                </form>
            </div>

            <?php if (mysqli_num_rows($audit_result) > 0) { ?>
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Table</th>
                            <th>Record ID</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($audit_result)) { ?>
                            <tr>
                                <td><?php echo $row['created_at']; ?></td>
                                <td><?php echo $row['Username'] . " (ID: " . $row['user_id'] . ")"; ?></td>
                                <td><?php echo $row['table_name']; ?></td>
                                <td><?php echo $row['record_id']; ?></td>
                                <td><?php echo $row['action']; ?></td>
                                <td><?php echo $row['details']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No audit logs found.</p>
            <?php } ?>
        </div>
    </div>
</body>
</html>