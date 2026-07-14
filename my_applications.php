<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['Volunteer_Id'])) {
    header("Location: volunteer_login.php?error=" . urlencode("Please login as volunteer."));
    exit();
}

$volunteer_id = $_SESSION['Volunteer_Id'];
$volunteer_name = isset($_SESSION['Name']) ? $_SESSION['Name'] : 'User';

// Get page number for pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count
$conn = db_connect();
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM Application WHERE Volunteer_Id = '$volunteer_id'");
$count_data = mysqli_fetch_assoc($count_result);
$total = $count_data['total'];
$total_pages = ceil($total / $per_page);

// Get volunteer's applications
$sql = "SELECT a.*, e.Title as EventTitle, m.Name as ManagerName
        FROM Application a
        JOIN CommunityEvent e ON a.Event_Id = e.Event_Id
        LEFT JOIN Manager m ON a.Status_Change_By = m.Manager_Id
        WHERE a.Volunteer_Id = '$volunteer_id'
        ORDER BY a.Apply_Date DESC
        LIMIT $offset, $per_page";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            font-size: 28px;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .header-actions a {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .header-actions a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .applications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .application-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-left: 5px solid #e0e0e0;
        }

        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .application-card.pending {
            border-left-color: #ffc107;
        }

        .application-card.approved {
            border-left-color: #28a745;
        }

        .application-card.rejected {
            border-left-color: #dc3545;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            max-width: 70%;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .card-info {
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
            min-width: 100px;
        }

        .info-value {
            color: #333;
            flex: 1;
        }

        .reject-reason {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            border-left: 3px solid #dc3545;
        }

        .reject-reason-title {
            font-weight: 600;
            color: #dc3545;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .reject-reason-text {
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }

        .empty-message {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            text-decoration: none;
            color: #667eea;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
        }

        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 14px;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .applications-grid {
                grid-template-columns: 1fr;
            }

            .card-title {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <h1>📋 My Applications</h1>
        <div class="header-actions">
            <a href="user_dashboard.php">← Back</a>
        </div>
    </div>
</header>

<div class="container">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="applications-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="application-card <?php echo strtolower($row['Status'] === 'N' ? 'pending' : ($row['Status'] === 'A' ? 'approved' : 'rejected')); ?>">
                    <div class="card-header">
                        <div class="card-title"><?php echo htmlspecialchars($row['EventTitle']); ?></div>
                        <span class="status-badge <?php 
                            if ($row['Status'] === 'A') echo 'status-approved';
                            elseif ($row['Status'] === 'R') echo 'status-rejected';
                            else echo 'status-pending';
                        ?>">
                            <?php 
                            if ($row['Status'] === 'A') echo '✅ Approved';
                            elseif ($row['Status'] === 'R') echo '❌ Rejected';
                            else echo '⏳ Pending';
                            ?>
                        </span>
                    </div>

                    <div class="card-info">
                        <div class="info-row">
                            <div class="info-label">Applied:</div>
                            <div class="info-value"><?php echo date('M d, Y', strtotime($row['Apply_Date'])); ?></div>
                        </div>

                        <?php if ($row['Status'] !== 'N'): ?>
                            <div class="info-row">
                                <div class="info-label">Response:</div>
                                <div class="info-value"><?php echo date('M d, Y', strtotime($row['Status_Change_Date'])); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($row['Status_Change_By'] && $row['ManagerName']): ?>
                            <div class="info-row">
                                <div class="info-label">Manager:</div>
                                <div class="info-value"><?php echo htmlspecialchars($row['ManagerName']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($row['Status'] === 'R' && !empty($row['Reject_Reason'])): ?>
                        <div class="reject-reason">
                            <div class="reject-reason-title">❌ Rejection Reason</div>
                            <div class="reject-reason-text"><?php echo htmlspecialchars($row['Reject_Reason']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1">« First</a>
                    <a href="?page=<?php echo $page - 1; ?>">‹ Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>">Next ›</a>
                    <a href="?page=<?php echo $total_pages; ?>">Last »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-message">
            <div class="empty-icon">📭</div>
            <p>You haven't applied for any services yet.</p>
            <p><a href="view_events.php" style="color: #667eea; text-decoration: underline;">Browse available services →</a></p>
        </div>
    <?php endif; ?>

    <footer>
        &copy; 2026 Community Services Platform. All rights reserved.
    </footer>
</div>

</body>
</html>
