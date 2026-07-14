<?php
// manager_dashboard.php
// Manager dashboard with session check and tokens design

session_start();
include "db_connect.php";

if (!isset($_SESSION['Manager_Id'])) {
    header('Location: index.php?error=' . urlencode('Please login as manager to access the manager dashboard.'));
    exit();
}

$managerName = isset($_SESSION['Name']) ? $_SESSION['Name'] : 'Manager';

$conn = db_connect();
// Get unread applications count
$stmt = $conn->prepare('SELECT COUNT(*) as unread_count FROM Application WHERE Status = "N"');
$stmt->execute();
$result = $stmt->get_result();
$unread_data = $result->fetch_assoc();
$unread_count = $unread_data['unread_count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <!-- Sync with Global Design System Elements -->
    <link rel="stylesheet" href="style.css">
    <style>
        /* Layout structural adjustments following theme guidelines */
        .wrapper { max-width: auto;
                    margin: 40px auto; 
                    padding: 0 20px; 
                }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 20px;
            margin-top: 40px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--green-500);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .card h3 {
            color: var(--green-900);
            margin-bottom: 12px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card p {
            color: var(--text-body);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .card .btn {
            align-self: flex-start;
            width: 100%;
            text-align: center;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .action-item {
            background: var(--white);
            border-radius: var(--radius-sm);
            padding: 20px;
            text-align: center;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .action-item p {
            font-size: 14px;
            color: var(--text-body);
            margin-bottom: 12px;
        }

        .action-item .btn-link {
            display: inline-block;
            color: var(--green-700);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .action-item .btn-link:hover {
            color: var(--green-900);
            text-decoration: underline;
        }

        .alert.success {
            padding: 15px;
            border-radius: var(--radius-sm);
            margin-bottom: 25px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            font-size: 14px;
        }

        .counter-badge {
            background: #ff4757;
            color: var(--white);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 6px;
        }
    </style>
</head>
<body class="app-body">

    <!-- Dynamically pull separated manager navigation layout component -->
    <?php include "manager_nav.php"; ?>

    <!-- Standard system green banner header block -->
    <div class="page-banner">
        <div class="banner-title">
            <div class="banner-icon">👋</div>
            <div>
                <h1>Welcome Back, <?php echo htmlspecialchars($managerName); ?></h1>
                <p>Manage services, monitor registrations, and supervise incoming platform operations.</p>
            </div>
        </div>
    </div>

    <div class="wrapper">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">✅ <?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <div class="section-title">📊 Management Dashboard Overview</div>
        <div class="dashboard-grid">
            <div class="card">
                <div>
                    <h3>📢 Create New Service</h3>
                    <p>Post a new service, event, or announcement for volunteers to discover and join in the community.</p>
                </div>
                <a href="create_service.php" class="btn">Create Service &rarr;</a>
            </div>

            <div class="card">
                <div>
                    <h3>📋 View Applications
                        <?php if ($unread_count > 0): ?>
                            <span class="counter-badge"><?php echo $unread_count; ?> new</span>
                        <?php endif; ?>
                    </h3>
                    <p>Review volunteer credentials, check required information, and issue acceptances or rejections.</p>
                </div>
                <a href="view_applications.php" class="btn secondary">Review Submissions &rarr;</a>
            </div>

            <div class="card">
                <div>
                    <h3>📺 Manage Services</h3>
                    <p>View active listings, update content information, adjust time frames, or close full programs.</p>
                </div>
                <a href="view_events.php" class="btn secondary">View My Services &rarr;</a>
            </div>
        </div>

        <div class="section-title">🔧 Quick Access Links</div>
        <div class="action-grid">
            <div class="action-item">
                <p><strong>Add Opportunity</strong></p>
                <a href="create_service.php" class="btn-link">Create New Listing</a>
            </div>
            <div class="action-item">
                <p><strong>Modify Schedules</strong></p>
                <a href="view_events.php" class="btn-link">Edit Active Postings</a>
            </div>
            <div class="action-item">
                <p><strong>Pending Screenings</strong></p>
                <a href="view_applications.php" class="btn-link">Check Applications (<?php echo $unread_count; ?>)</a>
            </div>
        </div>

        <footer class="site-footer" style="margin-top: 60px; border-top: 1px solid var(--border); padding-top: 20px;">
            &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Connecting Volunteers with Community Service Opportunities
        </footer>
    </div>

</body>
</html>