<?php
session_start();
include "db_connect.php";

$is_manager = isset($_SESSION['Manager_Id']);
$is_volunteer = isset($_SESSION['Volunteer_Id']);

if (!$is_manager && !$is_volunteer) {
    header("Location: index.php?error=" . urlencode("Please login to view services."));
    exit();
}

$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_volunteer) {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $event_id = isset($_POST['event_id']) ? trim($_POST['event_id']) : '';
    $volunteer_id = $_SESSION['Volunteer_Id'];
    
    if (!empty($event_id)) {
        if ($action === 'like') {
            $id_stmt = $conn->prepare('SELECT COUNT(*) as count FROM ServiceLikes');
            $id_stmt->execute();
            $res = $id_stmt->get_result();
            $row = $res->fetch_assoc();
            $like_number = $row['count'] + 1;
            $like_id = 'LK' . str_pad($like_number, 3, '0', STR_PAD_LEFT);
            $id_stmt->close();

            $stmt = $conn->prepare('INSERT IGNORE INTO ServiceLikes (Like_Id, Volunteer_Id, Event_Id) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $like_id, $volunteer_id, $event_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'unlike') {
            $stmt = $conn->prepare('DELETE FROM ServiceLikes WHERE Volunteer_Id = ? AND Event_Id = ?');
            $stmt->bind_param('ss', $volunteer_id, $event_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    exit();
}

$sql = "SELECT * FROM CommunityEvent ORDER BY Event_Id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Feed - CommunityConnect</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Unique Feed styles integrated smoothly with standard style.css tokens */
        .feed-container { max-width: 700px; margin: 0 auto; padding: 40px 20px; }
        .post-card { background: var(--white); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm); margin-bottom: 24px; position: relative; }
        .post-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .post-meta { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--green-200); color: var(--green-900); display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .post-title { font-size: 20px; font-weight: 700; color: var(--green-900); margin: 12px 0 6px; }
        .post-img { width: 100%; max-height: 300px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 14px; }
        .detail-line { font-size: 13px; color: var(--text-muted); margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
        .post-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border); }
        .opt-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted); }
        .dropdown-menu { display: none; position: absolute; right: 24px; top: 60px; background: white; border: 1px solid var(--border); border-radius: var(--radius-sm); box-shadow: var(--shadow-md); z-index: 10; }
        .dropdown-menu.show { display: block; }
        .dropdown-menu a { display: block; padding: 10px 16px; font-size: 13px; color: var(--text-dark); }
        .dropdown-menu a:hover { background: var(--green-50); color: var(--green-800); }
        .action-buttons { display: flex; gap: 8px; }
    </style>
</head>
<body class="app-body">

    <nav class="app-navbar">
        <div class="brand">
            <img src="images/cc_logo.png" alt="Logo">
            <div class="brand-text">
                <div class="brand-name">COMMUNITY CONNECT</div>
                <div class="role-pill"><?php echo $is_manager ? 'MANAGER' : 'VOLUNTEER'; ?> PORTAL</div>
            </div>
        </div>
        <div class="nav-links">
            <a href="<?php echo $is_manager ? 'admin_dashboard.php' : 'user_dashboard.php'; ?>">Dashboard</a>
            <a href="view_events.php" style="background: var(--green-200);">View Feed</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="page-banner">
        <div class="banner-title">
            <div class="banner-icon">🌿</div>
            <div>
                <h1>Community Opportunities</h1>
                <p>Discover projects, stay informed, and engage with local events</p>
            </div>
        </div>
    </div>

    <div class="feed-container">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $type_emoji = ['Announcement' => '📢', 'Event' => '🎉', 'Service' => '🤝'];
                $emoji = $type_emoji[$row['Type']] ?? '📌';
                $manager_initial = strtoupper(substr($row['Manager_Id'], 0, 1));
                $is_owner = $is_manager && $_SESSION['Manager_Id'] === $row['Manager_Id'];
                
                $like_count = 0;
                $is_liked = false;
                
                if ($is_volunteer) {
                    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM ServiceLikes WHERE Event_Id = ?');
                    $stmt->bind_param('s', $row['Event_Id']);
                    $stmt->execute();
                    $like_count = $stmt->get_result()->fetch_assoc()['count'];
                    $stmt->close();
                    
                    $stmt = $conn->prepare('SELECT Like_Id FROM ServiceLikes WHERE Volunteer_Id = ? AND Event_Id = ?');
                    $stmt->bind_param('ss', $_SESSION['Volunteer_Id'], $row['Event_Id']);
                    $stmt->execute();
                    $is_liked = $stmt->get_result()->num_rows > 0;
                    $stmt->close();
                }
                ?>
                <div class="post-card">
                    <div class="post-header">
                        <div class="post-meta">
                            <div class="avatar"><?php echo $manager_initial; ?></div>
                            <div>
                                <div style="font-weight:700; font-size:14px; color:var(--green-900);">Project Manager</div>
                                <div style="font-size:11px; color:var(--text-muted); font-weight:600;"><?php echo strtoupper($row['Type']); ?></div>
                            </div>
                        </div>
                        <?php if ($is_owner): ?>
                            <div>
                                <button class="opt-btn" onclick="toggleMenu(this)">⋮</button>
                                <div class="dropdown-menu">
                                    <a href="update_service.php?id=<?php echo $row['Event_Id']; ?>">✏️ Edit Post</a>
                                    <a href="update_service.php?id=<?php echo $row['Event_Id']; ?>&action=delete" style="color:#b02a37;" onclick="return confirm('Delete this post permanently?')">🗑️ Delete</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($row['Image_Path'] && file_exists($row['Image_Path'])): ?>
                        <img src="<?php echo htmlspecialchars($row['Image_Path']); ?>" class="post-img" alt="Post graphic">
                    <?php endif; ?>

                    <div class="post-title"><?php echo $emoji . ' ' . htmlspecialchars($row['Title']); ?></div>
                    <p style="font-size:14px; line-height:1.5; margin-bottom:14px; color:var(--text-body);"><?php echo htmlspecialchars($row['Description']); ?></p>

                    <div class="detail-line">📍 <span><?php echo htmlspecialchars($row['Venue']); ?></span></div>
                    <div class="detail-line">📅 <span><?php echo date('M d, Y', strtotime($row['StartTime'])) . ' to ' . date('M d, Y', strtotime($row['EndTime'])); ?></span></div>
                    <div class="detail-line">👥 <span><?php echo htmlspecialchars($row['Num_of_Vol']); ?> spaces requested</span></div>

                    <div class="post-footer">
                        <span style="font-size:13px; color:var(--text-muted); font-weight:600;">❤️ <?php echo $like_count; ?> interested</span>
                        <?php if ($is_volunteer): ?>
                            <div class="action-buttons">
                                <button class="btn secondary" style="padding: 6px 14px; font-size:13px;" onclick="toggleLike('<?php echo $row['Event_Id']; ?>', this)">
                                    <?php echo $is_liked ? '💔 Unlike' : '❤️ Like'; ?>
                                </button>
                                <a href="apply_service.php?id=<?php echo $row['Event_Id']; ?>" class="btn" style="padding: 6px 14px; font-size:13px;">Apply</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php }
        } else { ?>
            <div class="feature-card" style="text-align:center; padding:40px;">
                <div style="font-size:40px; margin-bottom:10px;">📭</div>
                <h3>No Opportunities Found</h3>
                <p>Check back later for new announcements or updates.</p>
            </div>
        <?php } ?>
    </div>

    <script>
    function toggleMenu(button) {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
        button.nextElementSibling.classList.toggle('show');
        event.stopPropagation();
    }
    document.addEventListener('click', () => document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show')));

    function toggleLike(eventId, button) {
        const isLiked = button.innerText.includes('Unlike');
        const action = isLiked ? 'unlike' : 'like';
        const formData = new FormData();
        formData.append('action', action);
        formData.append('event_id', eventId);
        
        fetch(window.location.href, { method: 'POST', body: formData })
            .then(() => location.reload());
    }
    </script>
</body>
</html>