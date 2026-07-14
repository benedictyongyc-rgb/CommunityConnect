<?php
session_start();
include "db_connect.php";

// Check if manager is logged in
if (!isset($_SESSION['Manager_Id'])) {
    header("Location: index.php");
    exit();
}

$manager_id = $_SESSION['Manager_Id'];
$event_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (empty($event_id)) {
    header("Location: admin_dashboard.php?error=Invalid service ID");
    exit();
}
$conn = db_connect();

// Fetch the event details
$stmt = $conn->prepare('SELECT * FROM CommunityEvent WHERE Event_Id = ? AND Manager_Id = ?');
$stmt->bind_param('ss', $event_id, $manager_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin_dashboard.php?error=Service not found or you don't have permission to edit it");
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Handle delete action
if ($action === 'delete') {
    $stmt = $conn->prepare('DELETE FROM CommunityEvent WHERE Event_Id = ? AND Manager_Id = ?');
    $stmt->bind_param('ss', $event_id, $manager_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=Service deleted successfully");
        exit();
    } else {
        $error = "Error deleting service: " . $stmt->error;
    }
    $stmt->close();
}

// Handle update action
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : '';
    $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
    $service_description = isset($_POST['service_description']) ? trim($_POST['service_description']) : '';
    $service_venue = isset($_POST['service_vanue']) ? trim($_POST['service_vanue']) : '';
    $service_Sdate = isset($_POST['service_Sdate']) ? trim($_POST['service_Sdate']) : '';
    $service_Edate = isset($_POST['service_Edate']) ? trim($_POST['service_Edate']) : '';
    
    if (empty($service_type) || empty($service_name) || empty($service_description) || empty($service_venue) || empty($service_Sdate) || empty($service_Edate)) {
        $error = "Please fill in all required fields.";
    } elseif (strtotime($service_Sdate) > strtotime($service_Edate)) {
        $error = "End date must be after start date.";
    } else {
        $image_path = $event['Image_Path'];
        
        if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/services/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = pathinfo($_FILES['service_image']['name'], PATHINFO_EXTENSION);
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array(strtolower($file_ext), $allowed_ext)) {
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                
                $filename = 'service_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['service_image']['tmp_name'], $file_path)) {
                    $image_path = $file_path;
                }
            }
        }
        
        $start_datetime = $service_Sdate . ' 09:00:00';
        $end_datetime = $service_Edate . ' 17:00:00';
        
        $stmt = $conn->prepare('UPDATE CommunityEvent SET Type = ?, Title = ?, Description = ?, Venue = ?, StartTime = ?, EndTime = ?, Image_Path = ? WHERE Event_Id = ? AND Manager_Id = ?');
        $stmt->bind_param('sssssssss', $service_type, $service_name, $service_description, $service_venue, $start_datetime, $end_datetime, $image_path, $event_id, $manager_id);
        
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php?success=Service updated successfully");
            exit();
        } else {
            $error = "Error updating service: " . $stmt->error;
        }
        $stmt->close();
    }
}

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($error) && !empty($error) ? $error : (isset($_GET['error']) ? $_GET['error'] : '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Small unique page adjustments without overriding global styles */
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--green-900); }
        select, input[type="text"], input[type="date"], input[type="file"], textarea {
            width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit;
        }
        select:focus, input[type="text"]:focus, input[type="date"]:focus, textarea:focus { outline: none; border-color: var(--green-500); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .image-preview { margin-top: 10px; max-width: 100%; max-height: 200px; border-radius: var(--radius-sm); }
        .button-group { display: flex; gap: 12px; margin-top: 30px; }
        .flex-center { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px; }
        .alert { padding: 12px; border-left: 4px solid; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
        .info-text { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
        .btn.danger { background: #b02a37; color: white; }
        .btn.danger:hover { background: #8a2029; color: white; }
    </style>
</head>
<body class="app-body">

    <nav class="app-navbar">
        <div class="brand">
            <img src="images/cc_logo.png" alt="Logo">
            <div class="brand-text">
                <div class="brand-name">COMMUNITY CONNECT</div>
                <div class="role-pill">MANAGER PANEL</div>
            </div>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="view_events.php">View Feed</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="flex-center">
        <div class="container">
            <div class="header">
                <h1>Edit Service</h1>
                <p>Modify structural details for this post</p>
            </div>

            <?php if ($success): ?>
                <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="service_type">Service Type *</label>
                    <select id="service_type" name="service_type" required>
                        <option value="">Select a type</option>
                        <option value="Announcement" <?php echo ($event['Type'] === 'Announcement') ? 'selected' : ''; ?>>📢 Announcement</option>
                        <option value="Event" <?php echo ($event['Type'] === 'Event') ? 'selected' : ''; ?>>🎉 Event</option>
                        <option value="Service" <?php echo ($event['Type'] === 'Service') ? 'selected' : ''; ?>>🤝 Service</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service_name">Title *</label>
                    <input type="text" id="service_name" name="service_name" value="<?php echo htmlspecialchars($event['Title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="service_description">Description *</label>
                    <textarea id="service_description" name="service_description" rows="4" required><?php echo htmlspecialchars($event['Description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="service_vanue">Location *</label>
                    <input type="text" id="service_vanue" name="service_vanue" value="<?php echo htmlspecialchars($event['Venue']); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="service_Sdate">Start Date *</label>
                        <input type="date" id="service_Sdate" name="service_Sdate" value="<?php echo substr($event['StartTime'], 0, 10); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="service_Edate">End Date *</label>
                        <input type="date" id="service_Edate" name="service_Edate" value="<?php echo substr($event['EndTime'], 0, 10); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="service_image">Update Image (Optional)</label>
                    <input type="file" id="service_image" name="service_image" accept="image/*">
                    <p class="info-text">Supported formats: JPG, PNG, GIF</p>
                    
                    <?php if ($event['Image_Path'] && file_exists($event['Image_Path'])): ?>
                        <p style="margin-top: 10px; font-size: 12px; color: var(--text-muted);">Current image:</p>
                        <img src="<?php echo htmlspecialchars($event['Image_Path']); ?>" alt="Service image" class="image-preview">
                    <?php endif; ?>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">Save</button>
                    <button type="button" class="btn danger" onclick="if(confirm('Are you sure?')) window.location.href='update_service.php?id=<?php echo $event_id; ?>&action=delete'">Delete</button>
                    <button type="button" class="btn secondary" onclick="window.location.href='admin_dashboard.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>