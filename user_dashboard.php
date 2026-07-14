<?php
    // user_dashboard.php
    // Simple user dashboard with session check / logout guard

    session_start();

    if (!isset($_SESSION['Volunteer_Id'])) {
        // Not logged in as volunteer
        header('Location: index.php?error=' . urlencode('Please login as volunteer to access the user dashboard.'));
        exit();
    }

    $volunteerName = isset($_SESSION['Name']) ? $_SESSION['Name'] : 'User';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Dashboard - CommunityConnect</title>
        <link rel="icon" type="image/png" href="images/cc_logo.png">
    	<link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
        <link rel="stylesheet" href="style.css">
        <style>
            /* Layout structural adjustments following theme guidelines */
            .wrapper { max-width: auto; 
                        margin: 40px auto; padding: 0 20px; 
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

            .action-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
            }

            .action-item {
                background: var(--white);
                border-radius: var(--radius-md);
                padding: 25px;
                text-align: center;
                transition: all 0.3s;
                border: 1px solid var(--border);
                box-shadow: var(--shadow-sm);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                gap: 16px;
            }

            .action-item:hover {
                transform: translateY(-4px);
                box-shadow: var(--shadow-md);
            }

            .action-item p {
                color: var(--text-body);
                font-size: 15px;
                margin: 0;
            }

            .action-item .btn {
                display: inline-block;
                width: 100%;
                text-align: center;
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
        </style>
    </head>
    <body class="app-body">

    <?php include "volunteer_nav.php"; ?>

    <div class="page-banner">
        <div class="banner-title">
            <div class="banner-icon">👋</div>
            <div>
                <h1>Welcome Back, <?php echo htmlspecialchars($volunteerName); ?>!</h1>
                <p>Track your applications, look through upcoming projects, and make a difference.</p>
            </div>
        </div>
    </div>

    <div class="wrapper">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">
                ✅ <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <div class="section-title">🎉 Service Feed & Discovery</div>
        <div class="action-grid">
            <div class="action-item">
                <p>📺 Browse the live system feed for newly announced schedules and special programs.</p>
                <a href="view_events.php" class="btn">View Upcoming Events</a>
            </div>

            <div class="action-item">
                <p>📋 Found an opportunity? Send your credentials and statement over to the management panel.</p>
                <a href="apply_service.php" class="btn secondary">Apply for Service</a>
            </div>
        </div>

        <div class="section-title">📂 Your Registrations</div>
        <div class="action-grid">
            <div class="action-item">
                <p>❗ Check real-time acceptance data and approvals for your active submissions.</p>
                <a href="application_status.php" class="btn secondary">Check Application Status</a>
            </div>
        </div>

        <footer class="site-footer" style="margin-top: 60px; border-top: 1px solid var(--border); padding-top: 20px;">
            &copy; <?php echo date('Y'); ?> CommunityConnect &mdash; Connecting Volunteers with Community Service Opportunities
        </footer>
    </div>

    </body>
</html>