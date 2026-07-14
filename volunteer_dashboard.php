<?php
// volunteer_dashboard.php - Redirect to user_dashboard.php
session_start();

if (!isset($_SESSION['Manager_Id'])) {
    header('Location: index.php?error=' . urlencode('Please login as volunteer to access the dashboard.'));
    exit();
}

// Redirect to the new manager dashboard
header('Location: user_dashboard.php');
exit();
?>

