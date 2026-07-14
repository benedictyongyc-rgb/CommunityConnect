<?php
// admin_dashboard.php - Redirect to manager_dashboard.php
session_start();

if (!isset($_SESSION['Manager_Id'])) {
    header('Location: admin_login.php?error=' . urlencode('Please login as manager to access the dashboard.'));
    exit();
}

// Redirect to the new manager dashboard
header('Location: manager_dashboard.php');
exit();
?>

