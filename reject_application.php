<?php
// reject_application.php
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

// FIXED: Removed intval() because Application_Id is an alphanumeric string (e.g., AP001)
$application_id = trim($_GET['id']);
$manager_id = $_SESSION['Manager_Id'];

$conn = db_connect();

// Get application and volunteer info
$sql = "SELECT a.*, v.Name, v.Email, e.Title as EventTitle
        FROM Application a
        JOIN Volunteer v ON a.Volunteer_Id = v.Volunteer_Id
        JOIN CommunityEvent e ON a.Event_Id = e.Event_Id
        WHERE a.Application_Id = ?";

// FIXED: Switched to Object-Oriented syntax to match your system standard
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $application_id); // FIXED: Bound as string 's'
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$stmt->close();

if (!$application) {
    header("Location: view_applications.php?error=" . urlencode("Application not found."));
    exit();
}

$error = '';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_reason'])) {
    $reject_reason = trim($_POST['reject_reason']);
    
    if (empty($reject_reason)) {
        $error = "Please provide a reason for rejection.";
    } else {
        $today = date('Y-m-d');
        
        // Update application status strictly for this single Application_Id string
        $update_sql = "UPDATE Application 
                       SET Status = 'R', 
                           Reject_Reason = ?, 
                           Status_Change_By = ?, 
                           Status_Change_Date = ?
                       WHERE Application_Id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        // FIXED: Changed parameter bindings to 'ssss' to match alphanumeric text string types
        $update_stmt->bind_param("ssss", $reject_reason, $manager_id, $today, $application_id);
        
        if ($update_stmt->execute()) {
            $update_stmt->close();
            mysqli_close($conn);
            header("Location: view_applications.php?success=" . urlencode("Application rejected successfully!"));
            exit();
        } else {
            $error = "Failed to reject application. Please try again.";
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Application - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Sticky Footer Layout Architecture rules */
        body.app-body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .wrapper {
            flex: 1;
            max-width: 760px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
        }

        .action-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            padding: 40px;
            margin-bottom: 24px;
        }

        .action-card h2 {
            font-size: 18px;
            color: var(--text-dark);
            margin-bottom: 20px;
            font-weight: 700;
            border-bottom: 2px solid var(--green-100);
            padding-bottom: 8px;
        }

        .meta-summary {
            background: var(--green-50);
            padding: 20px;
            border-radius: var(--radius-md);
            border-left: 4px solid var(--green-500);
            margin-bottom: 10px;
        }

        .meta-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            font-size: 14px;
        }

        .meta-row:last-child {
            border-bottom: none;
        }

        .meta-label {
            font-weight: 600;
            color: var(--text-muted);
            min-width: 140px;
        }

        .meta-value {
            color: var(--text-dark);
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-group textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 14px;
            color: var(--text-dark);
            resize: vertical;
            min-height: 130px;
            transition: all 0.3s ease;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #dc2626; /* Warning red color border focus highlight */
            box-shadow: 0 0 0 4px #fee2e2;
        }

        .button-cluster {
            display: flex;
            gap: 14px;
            justify-content: flex-end;
            margin-top: 28px;
        }

        /* Semantic Overrides */
        .btn-danger-action {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-danger-action:hover {
            background-color: #fca5a5;
            color: #7f1d1d;
            transform: translateY(-1px);
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 14px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 600px) {
            .action-card { padding: 24px; }
            .button-cluster { flex-direction: column-reverse; }
            .button-cluster a, .button-cluster button { width: 100%; text-align: center; }
        }
    </style>
</head>
<body class="app-body">

    <div class="page-banner" style="background: linear-gradient(135deg, #7f1d1d 0%, var(--green-900) 100%);">
        <div class="banner-title">
            <div class="banner-icon">❌</div>
            <div>
                <h1>Reject Deployment Application</h1>
                <p>Provide administrative cancellation logic reasons to deny volunteer active assignment.</p>
            </div>
        </div>
        <div class="banner-actions">
            <a href="view_applications.php" class="btn secondary">&larr; Back to List</a>
        </div>
    </div>

    <div class="wrapper">
        
        <?php if ($error): ?>
            <div class="alert-error">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="action-card">
            <h2>Target Evaluation Summary</h2>
            <div class="meta-summary">
                <div class="meta-row">
                    <div class="meta-label">Application ID</div>
                    <div class="meta-value"><strong><?php echo htmlspecialchars($application['Application_Id']); ?></strong></div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Candidate Name</div>
                    <div class="meta-value"><?php echo htmlspecialchars($application['Name']); ?></div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Email Handle</div>
                    <div class="meta-value"><?php echo htmlspecialchars($application['Email']); ?></div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Requested Project</div>
                    <div class="meta-value"><?php echo htmlspecialchars($application['EventTitle']); ?></div>
                </div>
                <div class="meta-row">
                    <div class="meta-label">Filing Date</div>
                    <div class="meta-value"><?php echo date('F d, Y', strtotime($application['Apply_Date'])); ?></div>
                </div>
            </div>
        </div>

        <div class="action-card">
            <h2>Process Administrative Exclusion</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="reject_reason">Exclusion Reasoning Statement *</label>
                    <textarea id="reject_reason" name="reject_reason" required placeholder="Outline why this candidate is unsuited for this specific operation deployment opening..."></textarea>
                </div>

                <div class="button-cluster">
                    <a href="view_applications.php" class="btn secondary">Abort Process</a>
                    <button type="submit" class="btn-danger-action">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="site-footer" style="border-top: 1px solid var(--border); padding: 24px 40px;">
        &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Management Console Audit Tracking
    </footer>

</body>
</html>