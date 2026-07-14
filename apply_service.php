<?php
// apply_service.php
session_start();
include "db_connect.php";

if (!isset($_SESSION['Volunteer_Id'])) {
    header("Location: index.php?error=" . urlencode("Please login to apply for a service."));
    exit();
}

$event_id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (empty($event_id)) {
    header("Location: view_events.php?error=" . urlencode("Invalid service ID."));
    exit();
}

$conn = db_connect();

// 1. Check if event exists
$stmt = $conn->prepare('SELECT * FROM CommunityEvent WHERE Event_Id = ?');
$stmt->bind_param('s', $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_events.php?error=" . urlencode("Service not found."));
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// 2. Get volunteer info
$volunteer_id = $_SESSION['Volunteer_Id'];
$stmt = $conn->prepare('SELECT * FROM Volunteer WHERE Volunteer_Id = ?');
$stmt->bind_param('s', $volunteer_id);
$stmt->execute();
$volunteer_result = $stmt->get_result();
$volunteer = $volunteer_result->fetch_assoc();
$stmt->close();

// 3. Check if already applied
$stmt = $conn->prepare('SELECT Application_Id FROM Application WHERE Volunteer_Id = ? AND Event_Id = ?');
$stmt->bind_param('ss', $volunteer_id, $event_id);
$stmt->execute();
$check_result = $stmt->get_result();
$has_applied = ($check_result->num_rows > 0);
$stmt->close();

if ($has_applied) {
    header("Location: view_events.php?error=" . urlencode("You have already applied for this service."));
    exit();
}

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = isset($_POST['Reason']) ? trim($_POST['Reason']) : '';
    
    if (empty($reason)) {
        $error = "Please provide a reason for applying.";
    } else {
        // 4. Generate next Application_Id
        $id_stmt = $conn->prepare('SELECT COUNT(*) as count FROM Application');
        $id_stmt->execute();
        $res = $id_stmt->get_result();
        $row = $res->fetch_assoc();
        $application_number = $row['count'] + 1;
        $application_id = 'AP' . str_pad($application_number, 3, '0', STR_PAD_LEFT);
        $id_stmt->close();

        // 5. Insert Application (4 strings 'ssss')
        $stmt = $conn->prepare('INSERT INTO Application (Application_Id, Volunteer_Id, Event_Id, Reason, Apply_Date, Status) VALUES (?, ?, ?, ?, CURDATE(), "N")');
        $stmt->bind_param('ssss', $application_id, $volunteer_id, $event_id, $reason);
        
        if ($stmt->execute()) {
            $stmt->close();
            mysqli_close($conn);
            header("Location: view_events.php?success=" . urlencode("Application submitted successfully! Your application is pending approval."));
            exit();
        } else {
            $error = "Error submitting application: " . $stmt->error;
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
    <title>Apply for Service - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Sticky Footer Layout Structure */
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

        .application-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            padding: 40px;
        }

        .service-summary {
            background: var(--green-50);
            padding: 24px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            border-left: 4px solid var(--green-600);
        }

        .service-summary h3 {
            color: var(--green-900);
            font-size: 18px;
            margin-bottom: 14px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(31, 58, 28, 0.08);
            font-size: 14px;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .detail-value {
            color: var(--text-dark);
            font-weight: 600;
        }

        .volunteer-meta {
            background: #fdfdfd;
            border: 1px dashed var(--border);
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 28px;
            font-size: 13px;
            line-height: 1.6;
            color: var(--text-body);
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 14px;
        }

        textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-family: inherit;
            color: var(--text-dark);
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 140px;
        }

        textarea:focus {
            outline: none;
            border-color: var(--green-500);
            box-shadow: 0 0 0 4px var(--green-100);
        }

        .button-cluster {
            display: flex;
            gap: 14px;
            margin-top: 32px;
        }

        .button-cluster button, 
        .button-cluster a {
            flex: 1;
            text-align: center;
        }

        .alert {
            padding: 14px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 600px) {
            .application-card { padding: 24px; }
            .button-cluster { flex-direction: column; }
        }
    </style>
</head>
<body class="app-body">

    <?php include "volunteer_nav.php"; ?>

    <div class="page-banner">
        <div class="banner-title">
            <div class="banner-icon">✍️</div>
            <div>
                <h1>Submit Service Request</h1>
                <p>Complete your deployment enrollment form to request standard placement approval.</p>
            </div>
        </div>
    </div>

    <div class="wrapper">
        <div class="application-card">
            
            <?php if ($error): ?>
                <div class="alert error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="service-summary">
                <h3><?php echo htmlspecialchars($event['Title']); ?></h3>
                
                <div class="detail-row">
                    <span class="detail-label">📌 Classification Type</span>
                    <span class="detail-value"><?php echo htmlspecialchars($event['Type']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📍 Venue Location</span>
                    <span class="detail-value"><?php echo htmlspecialchars($event['Venue']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">📅 Project Duration</span>
                    <span class="detail-value">
                        <?php echo date('M d, Y', strtotime($event['StartTime'])); ?> to <?php echo date('M d, Y', strtotime($event['EndTime'])); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">👥 Slots Allocated</span>
                    <span class="detail-value"><?php echo htmlspecialchars($event['Num_of_Vol']); ?> Open Openings</span>
                </div>
            </div>

            <div class="volunteer-meta">
                <strong style="color: var(--green-900);">Your Registration Metadata:</strong><br>
                <strong>Name:</strong> <?php echo htmlspecialchars($volunteer['Name']); ?> &bull; 
                <strong>Email:</strong> <?php echo htmlspecialchars($volunteer['Email']); ?> &bull; 
                <strong>Phone:</strong> <?php echo htmlspecialchars($volunteer['ContactNo']); ?>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="reason">Why do you want to volunteer for this service? *</label>
                    <textarea name="Reason" id="reason" required placeholder="Outline your availability, background motivation, and how you can contribute to the operations..."></textarea>
                </div>

                <div class="button-cluster">
                    <button type="submit" class="btn">Submit Placement Form</button>
                    <a href="view_events.php" class="btn secondary">Cancel Request</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="site-footer" style="border-top: 1px solid var(--border); padding: 24px 40px;">
        &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Connecting Volunteers with Community Service Opportunities
    </footer>

</body>
</html>