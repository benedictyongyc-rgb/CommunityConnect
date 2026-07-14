<?php
session_start();
include "db_connect.php";

// Check if manager is logged in
if (!isset($_SESSION['Manager_Id'])) {
    header("Location: index.php");
    exit();
}

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Service - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Lightweight form structural helper elements built on tokens */
        .flex-center { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: var(--green-900); font-size: 14px; }
        select, input[type="text"], input[type="number"], input[type="date"], textarea {
            width: 100%; padding: 12px; border: 2px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit;
        }
        select:focus, input[type="text"]:focus, input[type="number"]:focus, input[type="date"]:focus, textarea:focus { outline: none; border-color: var(--green-500); }
        textarea { resize: vertical; min-height: 100px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .button-group { display: flex; gap: 12px; margin-top: 30px; }
        .alert { padding: 12px; border-left: 4px solid; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 14px; }
        .info-text { font-size: 12px; color: var(--text-muted); margin-top: 5px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="app-body">

    <?php include "manager_nav.php"; ?>

    <div class="flex-center">
        <div class="container">
            <div class="header" style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: var(--green-200); font-size: 28px; margin-bottom: 10px;">Create New Service</h1>
                <p style="color: var(--green-300); font-size: 14px;">Share a new opportunity with the community</p>
            </div>

            <?php if ($success): ?>
                <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="admin_auth.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_service">

                <div class="form-group">
                    <label for="service_type">Service Type *</label>
                    <select id="service_type" name="service_type" required>
                        <option value="">Select a type</option>
                        <option value="Announcement">📢 Announcement</option>
                        <option value="Event">🎉 Event</option>
                        <option value="Service">🤝 Service</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service_name">Title *</label>
                    <input type="text" id="service_name" name="service_name" placeholder="Enter service title" required>
                    <p class="info-text">Be descriptive and clear</p>
                </div>

                <div class="form-group">
                    <label for="service_description">Description *</label>
                    <textarea id="service_description" name="service_description" placeholder="Tell us more about this service..." required></textarea>
                    <p class="info-text">Include requirements, scheduling, and primary structural goals</p>
                </div>

                <div class="form-group">
                    <label for="num_of_vol">Volunteers Needed *</label>
                    <input type="number" id="num_of_vol" name="num_of_vol" min="1" placeholder="e.g., 5" required>
                </div>

                <div class="form-group">
                    <label for="service_vanue">Location *</label>
                    <input type="text" id="service_vanue" name="service_vanue" placeholder="e.g., Community Center, Main Street" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="service_Sdate">Start Date *</label>
                        <input type="date" id="service_Sdate" name="service_Sdate" required>
                    </div>
                    <div class="form-group">
                        <label for="service_Edate">End Date *</label>
                        <input type="date" id="service_Edate" name="service_Edate" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="service_image">Add Image (Optional)</label>
                    <input type="file" id="service_image" name="service_image" accept="image/*" style="border: none; padding: 6px 0;">
                    <p class="info-text">Supported formats: JPG, PNG, GIF</p>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn">Post Service</button>
                    <button type="button" class="btn secondary" onclick="window.location.href='manager_dashboard.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>