<?php
// admin_auth.php
// Handles admin login, registration, and service creation

session_start();
include "db_connect.php";

$FIXED_PASSKEY = 'Dit@2153';

function redirect_with_error($url, $msg) {
    $loc = $url . (strpos($url,'?') === false ? '?' : '&') . 'error=' . urlencode($msg);
    header("Location: $loc");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_error('index.php', 'Invalid request method.');
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// ============================================
// LOGIN ACTION
// ============================================
if ($action === 'login') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($name) || empty($password)) {
        redirect_with_error('index.php', 'Please fill in all fields.');
    }

    if (substr($name, -2) !== 'aM') {
        redirect_with_error('index.php', 'Either username or password is key-in wrongly, try again.');
    }

    // Lookup manager by exact name
    $conn = db_connect();
    $stmt = $conn->prepare("SELECT Manager_Id, Password FROM Manager WHERE Name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        // Not found — suggest registration
        $loc = 'index.php?register=1&name=' . urlencode($name);
        header("Location: $loc");
        exit();
    }

    $row = $result->fetch_assoc();
    $hash = $row['Password'];

    if (password_verify($password, $hash)) {
        $_SESSION['Manager_Id'] = $row['Manager_Id'];
        $_SESSION['Name'] = $name;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        redirect_with_error('index.php', 'Either username or password is key-in wrongly, try again.');
    }

    mysqli_close($conn);

// ============================================
// REGISTRATION ACTION
// ============================================
} elseif ($action === 'register') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($name) || empty($password) || empty($confirm) || empty($email)) {
        redirect_with_error('index.php?register=1&name=' . urlencode($name), 'Please fill in all fields.');
    }

    if (substr($name, -2) !== 'aM') {
        redirect_with_error('index.php?register=1&name=' . urlencode($name), 'Failed Passing Manager Criteria.');
    }

    if ($password !== $confirm) {
        redirect_with_error('index.php?register=1&name=' . urlencode($name), 'Passwords do not match.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_error('index.php?register=1&name=' . urlencode($name), 'Please enter a valid email address.');
    }

    // Check if name already exists
    $conn = db_connect();
    $stmt = $conn->prepare('SELECT Manager_Id FROM Manager WHERE Name = ?');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        redirect_with_error('index.php', 'An account with this name already exists. Please login instead.');
    }

    // Check if email already used
    $stmt = $conn->prepare('SELECT Manager_Id FROM Manager WHERE Email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        redirect_with_error('index.php?register=1&name=' . urlencode($name), 'This email is already registered.');
    }

    // Generate Manager_Id in format MNG001, MNG002, etc.
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM Manager');
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $manager_number = $row['count'] + 1;
    $manager_id = 'MNG' . str_pad($manager_number, 3, '0', STR_PAD_LEFT);
    
    // Hash password and insert manager
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO Manager (Manager_Id, Password, Name, Email, CreationDate) VALUES (?, ?, ?, ?, CURDATE())');
    $stmt->bind_param('ssss', $manager_id, $hash, $name, $email);

    if ($stmt->execute()) {
        $_SESSION['Manager_Id'] = $manager_id;
        $_SESSION['Name'] = $name;
        header('Location: admin_dashboard.php');
        exit();
    } else {
        redirect_with_error('index.php?register=1&name=' . urlencode($name), 'Unable to create account: ' . $stmt->error);
    }

    mysqli_close($conn);

// ============================================
// CREATE SERVICE ACTION
// ============================================
} elseif ($action === 'create_service') {
    // Check if manager is logged in
    if (!isset($_SESSION['Manager_Id'])) {
        redirect_with_error('index.php', 'Please login to create a service.');
    }
    
    // Inside admin_auth.php (Example mapping)
$type = $_POST['service_type'];
$title = $_POST['service_name'];
$description = $_POST['service_description'];
$venue = $_POST['service_vanue']; // Check spelling here!
$start_time = $_POST['service_Sdate'];
$end_time = $_POST['service_Edate'];
$num_of_vol = isset($_POST['num_of_vol']) ? intval($_POST['num_of_vol']) : 0;
$manager_id = $_SESSION['Manager_Id'];

// Ensure your SQL INSERT statement matches your exact database columns:
// INSERT INTO communityevent (Type, Title, Description, Venue, StartTime, EndTime, Num_of_Vol, Manager_Id) ...
    
    if (empty($type) || empty($title) || empty($description) || empty($venue) || empty($start_time) || empty($end_time)) {
        redirect_with_error('create_service.php', 'Please fill in all fields.');
    }
    
    if (strtotime($start_time) > strtotime($end_time)) {
        redirect_with_error('create_service.php', 'End date must be after start date.');
    }
    
    // Handle image upload (optional)
    $image_path = NULL;
    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/services/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_ext = pathinfo($_FILES['service_image']['name'], PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($file_ext), $allowed_ext)) {
            $filename = 'service_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['service_image']['tmp_name'], $file_path)) {
                $image_path = $file_path;
            }
        }
    }
    
    
    // Insert service into database
    $start_datetime = $start_time . ' 09:00:00';
    $end_datetime = $end_time . ' 17:00:00';
    
    $conn = db_connect();
    
    // 1. GENERATE EVENT ID FIRST
    $id_stmt = $conn->prepare('SELECT COUNT(*) as count FROM CommunityEvent');
    $id_stmt->execute();
    $res = $id_stmt->get_result();
    $row = $res->fetch_assoc();
    $event_number = $row['count'] + 1;
    $event_id = 'EVT' . str_pad($event_number, 3, '0', STR_PAD_LEFT);
    $id_stmt->close();

    // 2. PREPARE INSERT STATEMENT INCLUDING THE GENERATED EVENT_ID (10 fields total)
    $stmt = $conn->prepare('INSERT INTO CommunityEvent (Event_Id, Manager_Id, Type, Title, Description, Venue, StartTime, EndTime, Num_of_Vol, Image_Path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    
    // 3. BIND 10 STRINGS ('ssssssssss')
    $stmt->bind_param('ssssssssss', $event_id, $manager_id, $type, $title, $description, $venue, $start_datetime, $end_datetime, $num_of_vol, $image_path);
    
    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($conn);
        header('Location: admin_dashboard.php?success=Service created successfully');
        exit();
    } else {
        $error_msg = $stmt->error;
        $stmt->close();
        mysqli_close($conn);
        redirect_with_error('create_service.php', 'Error creating service: ' . $error_msg);
    }

    mysqli_close($conn);
} else {
    redirect_with_error('index.php', 'Unknown action.');
}
?>