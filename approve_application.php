<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['Manager_Id'])) {
    header("Location: index.php?error=" . urlencode("Please login as manager."));
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_applications.php?error=" . urlencode("Application ID not provided."));
    exit();
}

$application_id = trim($_GET['id']); // Remove any intval()
$manager_id = $_SESSION['Manager_Id'];
$today = date('Y-m-d');

$update_sql = "UPDATE Application 
               SET Status = 'A', 
                   Status_Change_By = ?, 
                   Status_Change_Date = ?
               WHERE Application_Id = ?";

$conn = db_connect();
$update_stmt = $conn->prepare($update_sql);
// FIXED: Using "sss" (all strings) so it matches the specific alphanumeric ID
$update_stmt->bind_param("sss", $manager_id, $today, $application_id);
$update_stmt->execute();

if (mysqli_stmt_execute($update_stmt)) {
    header("Location: view_applications.php?success=" . urlencode("Application approved successfully!"));
} else {
    header("Location: view_applications.php?error=" . urlencode("Failed to approve application."));
}

mysqli_stmt_close($update_stmt);
mysqli_close($conn);
?>
