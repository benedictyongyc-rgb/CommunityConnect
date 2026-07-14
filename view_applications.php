<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['Manager_Id'])) {
    header("Location: index.php?error=" . urlencode("Please login as manager to view applications."));
    exit();
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$conn = db_connect();

$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM Application");
$count_data = mysqli_fetch_assoc($count_result);
$total = $count_data['total'];
$total_pages = ceil($total / $per_page);

$sql = "SELECT a.*, v.Name, v.Email, v.ContactNo, v.Occupation, e.Title as EventTitle
        FROM Application a
        JOIN Volunteer v ON a.Volunteer_Id = v.Volunteer_Id
        JOIN CommunityEvent e ON a.Event_Id = e.Event_Id
        ORDER BY a.Apply_Date DESC, a.Status ASC
        LIMIT $offset, $per_page";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Data layout and operational processing states using UI theme variables */
        .wrapper { max-width: auto; margin: 40px auto; padding: 0 20px; }
        .table-responsive { background: var(--white); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); overflow-x: auto; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        thead { background: var(--green-900); color: var(--white); }
        thead th { padding: 16px; font-weight: 600; font-size: 14px; }
        tbody td { padding: 16px; border-bottom: 1px solid var(--border); font-size: 14px; color: var(--text-body); }
        tbody tr:hover { background: var(--green-50); }
        
        /* Synced Badges using design system colors */
        .status-pill { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-N { background: #fff3cd; color: #856404; }
        .status-A { background: var(--green-100); color: var(--green-900); }
        .status-R { background: #f8d7da; color: #721c24; }
        
        .action-flex { display: flex; gap: 8px; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: var(--radius-sm); font-weight: 600; text-decoration: none; }
        .btn-sm.approve { background: var(--green-200); color: var(--green-900); }
        .btn-sm.approve:hover { background: var(--green-300); }
        .btn-sm.reject { background: #f8d7da; color: #721c24; }
        .btn-sm.reject:hover { background: #f5c6cb; }

        .pagination-flex { display: flex; justify-content: center; gap: 8px; margin-top: 30px; }
        .pagination-flex a, .pagination-flex span { padding: 8px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); text-decoration: none; color: var(--green-700); font-weight: 600; font-size: 14px; }
        .pagination-flex a:hover, .pagination-flex .current { background: var(--green-900); color: var(--white); border-color: var(--green-900); }
        
        @media (max-width: 992px) {
            .action-flex { flex-direction: column; }
            .btn-sm { text-align: center; width: 100%; }
        }
    </style>
</head>
<body class="app-body">

    <?php include "manager_nav.php"; ?>

    <div class="page-banner">
        <div class="banner-title">
            <div class="banner-icon">📋</div>
            <div>
                <h1>Volunteer Applications</h1>
                <p>Review, screen, and process registrations for community services</p>
            </div>
        </div>
    </div>

    <div class="wrapper">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Volunteer Name</th>
                            <th>Service Opportunity</th>
                            <th>Contact Information</th>
                            <th>Statement of Reason</th>
                            <th>Current Status</th>
                            <th>Applied Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--green-900);"><?php echo htmlspecialchars($row['Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['EventTitle']); ?></td>
                                <td>
                                    <div style="font-size: 13px;"><?php echo htmlspecialchars($row['Email']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($row['ContactNo']); ?></div>
                                </td>
                                <td style="max-width: 250px; font-size: 13px;"><?php echo htmlspecialchars($row['Reason']); ?></td>
                                <td>
                                    <?php 
                                    $labels = ['N' => 'Pending', 'A' => 'Approved', 'R' => 'Rejected'];
                                    $current_status = $row['Status'];
                                    $status_text = $labels[$current_status] ?? 'Unknown';
                                    ?>
                                    <span class="status-pill status-<?php echo $current_status; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['Apply_Date'])); ?></td>
                                <td>
                                    <div class="action-flex">
                                        <?php if ($row['Status'] === 'N'): ?>
                                            <a href="approve_application.php?id=<?php echo $row['Application_Id']; ?>" class="btn-sm approve">Approve</a>
                                            <a href="reject_application.php?id=<?php echo $row['Application_Id']; ?>" class="btn-sm reject">Reject</a>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Processed</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination-flex">
                    <?php if ($page > 1): ?>
                        <a href="?page=1">«</a>
                        <a href="?page=<?php echo $page - 1; ?>">‹</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">›</a>
                        <a href="?page=<?php echo $total_pages; ?>">»</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="feature-card" style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                <h3>No Applications Found</h3>
                <p style="color: var(--text-muted);">Incoming volunteer submissions will appear in this control sheet pane.</p>
            </div>
        <?php endif; ?>

        <footer class="site-footer" style="margin-top: 60px; border-top: 1px solid var(--border); padding-top: 20px;">
            &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Connecting Volunteers with Community Service Opportunities
        </footer>
    </div>
</body>
</html>