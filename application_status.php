<?php
// application_status.php
// Start session
session_start();

// Include database connection file
include "db_connect.php";

// Check if volunteer is logged in
if (!isset($_SESSION['Volunteer_Id'])) {
    header("Location: index.php?error=" . urlencode("Please login to view your applications."));
    exit();
}

$volunteer_id = $_SESSION['Volunteer_Id'];
$conn = db_connect();

// Fetch applications along with event details for the logged-in volunteer
// FIXED: Capitalized 'CommunityEvent' to exactly match the working syntax in apply_service.php
$sql = "SELECT a.Application_Id, a.Status, a.Status_Change_Date, 
               e.Title, e.StartTime, e.Venue, e.Type
        FROM Application a
        JOIN CommunityEvent e ON a.Event_Id = e.Event_Id
        WHERE a.Volunteer_Id = ?
        ORDER BY a.Application_Id DESC";

// FIXED: Switched to Object-Oriented syntax to match your working apply_service.php architecture
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // If the query fails here, it means a column name or table name doesn't match your DB schema
    die("Database Query Preparation Failed: " . $conn->error);
}

// Bind as string 's'
$stmt->bind_param("s", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Application Status - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Sticky Footer Layout Rules Architecture */
        body.app-body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .wrapper {
            flex: 1;
            max-width: 1100px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
        }

        .table-responsive {
            background: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
            margin-top: 20px;
        }

        .status-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
        }

        .status-table th, .status-table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
        }

        .status-table th {
            background-color: var(--green-50);
            color: var(--green-900);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .status-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-table tbody tr:hover td {
            background-color: #f9fbf8;
        }

        /* Status Badges Tokens */
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }
        .badge.approved {
            background-color: #d1fae5;
            color: #059669;
        }
        .badge.rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .badge.pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--white);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            color: var(--text-muted);
        }
        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body class="app-body">

    <?php include "volunteer_nav.php"; ?>

    <div class="page-banner">
        <div class="banner-title">
            <div class="banner-icon">📋</div>
            <div>
                <h1>My Applications</h1>
                <p>Track your evaluation progress, screening outcomes, and active service assignments.</p>
            </div>
        </div>
        <div class="banner-actions">
            <a href="user_dashboard.php" class="btn secondary">&larr; Back to Dashboard</a>
        </div>
    </div>

    <div class="wrapper">

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="status-table">
                    <thead>
                        <tr>
                            <th>Service / Event</th>
                            <th>Classification</th>
                            <th>Scheduled Date</th>
                            <th>Venue Location</th>
                            <th>Status Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): 
                            $status_char = $row['Status'] ? $row['Status'] : 'N'; 
                            
                            switch ($status_char) {
                                case 'A':
                                    $badge_class = 'approved';
                                    $status_text = 'Approved';
                                    break;
                                case 'R':
                                    $badge_class = 'rejected';
                                    $status_text = 'Rejected';
                                    break;
                                case 'N':
                                case 'P':
                                default:
                                    $badge_class = 'pending';
                                    $status_text = 'Pending';
                                    break;
                            }
                            
                            $event_date = date('M d, Y', strtotime($row['StartTime']));
                        ?>
                            <tr>
                                <td><strong style="color: var(--text-dark);"><?php echo htmlspecialchars($row['Title']); ?></strong></td>
                                <td><span style="color: var(--text-muted);"><?php echo htmlspecialchars($row['Type']); ?></span></td>
                                <td><?php echo $event_date; ?></td>
                                <td><?php echo htmlspecialchars($row['Venue']); ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <h3>No Applications Found</h3>
                <p style="margin-top: 8px;">You haven't requested placement or applied for any community service listings yet.</p>
            </div>
        <?php endif; ?>

    </div>

    <footer class="site-footer" style="border-top: 1px solid var(--border); padding: 24px 40px;">
        &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Connecting Volunteers with Community Service Opportunities
    </footer>

<?php
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
</body>
</html>