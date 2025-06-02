<?php
# Start the session to access session variables
session_start();
function getCurrentPage() {
    $currentFile = basename($_SERVER['PHP_SELF']);
    return $currentFile;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Police Department Website</title>
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            Police Department
        </div>
        <div class="navbar-links">
            <?php 
            // Navigation pages (excluding Logout)
            $pages = [
                'welcome_page.php' => 'Home',
                'incident.php' => 'Incident',
                'vehicle.php' => 'Vehicle',
                'people.php' => 'People'
            ];

            // Admin-specific pages (these will only be shown if the user is an admin)
            $adminPages = [
                'officers.php' => 'Officers',
                'fines.php' => 'Fines',
                'audit_log.php' => 'Audit'
            ];

            // Logout page 
            $logoutPage = 'index.php';
            $logoutName = 'Logout';

            $currentPage = getCurrentPage();
            $userRole = $_SESSION['credentials'] ?? 'guest'; // Default to 'guest' if not set

            // Render standard pages
            foreach ($pages as $file => $name) {
                $activeClass = ($currentPage == $file) ? 'active-page' : '';

                // Check if this page requires a dropdown
                if ($name == 'Incident' || $name == 'Vehicle' || $name == 'People') {
                    // Dropdown for sub-pages
                    echo "<div class=\"dropdown\">
                            <button class=\"dropdown-btn $activeClass\">$name</button>
                            <div class=\"dropdown-content\">";
                    
                    // Sub-links for each dropdown
                    if ($name == 'Incident') {
                        echo "<a href=\"search_incident.php\">Search Incident</a>";
                        echo "<a href=\"add_incident.php\">Add Incident</a>";
                    } elseif ($name == 'Vehicle') {
                        echo "<a href=\"search_vehicle_page.php\">Search Vehicle</a>";
                        echo "<a href=\"add_vehicle.php\">Add Vehicle</a>";
                        echo "<a href=\"link_vehicle.php\">Link Vehicle</a>";
                    } elseif ($name == 'People') {
                        echo "<a href=\"search_person_page.php\">Search Person</a>";
                        echo "<a href=\"add_person.php\">Add Person</a>";
                    }
                    echo "</div>
                        </div>";
                } else {
                    echo "<a href=\"$file\" class=\"navbar-link $activeClass\">$name</a>";
                }
            }

            // Render admin-only pages
            if ($userRole === 'Administrator') {
                foreach ($adminPages as $file => $name) {
                    $activeClass = ($currentPage == $file) ? 'active-page' : '';
                    if ($name == 'Officers' || $name == 'Fines') {
                        // Dropdown for admin sub-pages
                        echo "<div class=\"dropdown\">
                                <button class=\"dropdown-btn $activeClass\">$name</button>
                                <div class=\"dropdown-content\">";
                        
                        if ($name == 'Officers') {
                            echo "<a href=\"search_officer.php\">Search Officers</a>";
                            echo "<a href=\"new_police_officer_page.php\">Add Officer</a>";
                        } elseif ($name == 'Fines') {
                            echo "<a href=\"search_fines_page.php\">Search Fines</a>";
                            echo "<a href=\"add_fines_page.php\">Add Fines</a>";
                        }
                        echo "</div>
                            </div>";
                    } else {
                        echo "<a href=\"$file\" class=\"navbar-link $activeClass\">$name</a>";
                    }
                }
            }

            // Rendering the logout tab last
            $activeClass = ($currentPage == $logoutPage) ? 'active-page' : '';
            echo "<a href=\"$logoutPage\" class=\"navbar-link $activeClass\">$logoutName</a>";
            ?>
        </div>
    </nav>
</body>
</html>
