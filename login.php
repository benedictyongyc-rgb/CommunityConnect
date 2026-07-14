<?php
session_start();
include "db_connect.php";

$error = "";

// Handle Volunteer Logic directly if submitted here
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_type']) && $_POST['login_type'] === 'volunteer') {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT Volunteer_Id, Name, Password FROM Volunteer WHERE Email = ?");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["Password"])) {
                $_SESSION["Volunteer_Id"] = $row["Volunteer_Id"];
                $_SESSION["Name"] = $row["Name"];
                header("Location: user_dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Email does not exist.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CommunityConnect Volunteer System</title>
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
            background: #E6E6E6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .portal-header {
            text-align: center;
            margin-bottom: 25px;
            color: #404E3B;
        }

        .portal-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .container {
            width: 440px;
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,.15);
        }

        /* Direct Layout Selector Buttons */
        .layout-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }

        .selector-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #BAC8B1;
            background: #f8faf8;
            color: #404E3B;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .selector-btn.active {
            background: #404E3B;
            color: white;
            border-color: #404E3B;
        }

        /* Layout Panels Toggle */
        .login-panel {
            display: none;
        }

        .login-panel.active {
            display: block;
        }

        .form-header {
            text-align: center;
            background: linear-gradient(135deg, #BAC8B1, #404E3B);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #404E3B;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #BAC8B1;
            border-radius: 8px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #404E3B;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            color: white;
            font-size: 16px;
            font-weight: bold;
            background: linear-gradient(135deg, #BAC8B1, #404E3B);
            margin-top: 10px;
        }

        .btn-submit:hover {
            opacity: .95;
        }

        .btn-secondary {
            background: #E6E6E6;
            color: #404E3B;
            border: 1px solid #404E3B;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }

        .links {
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 14px;
        }

        .links a {
            text-decoration: none;
            color: #404E3B;
            margin: 0 10px;
            font-weight: 600;
        }

        .alert {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-left: 4px solid #721c24;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .note, .small {
            font-size: 13px;
            color: #555;
            margin-bottom: 12px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<div class="portal-header">
    <h1>🎯 CommunityConnect Portal</h1>
    <p>Connecting Volunteers with Community Service Opportunities</p>
</div>

<div class="container">

    <div class="layout-selector">
        <button type="button" class="selector-btn active" onclick="switchLayout('volunteer', this)">🙋‍♂️ Volunteer</button>
        <button type="button" class="selector-btn" onclick="switchLayout('manager', this)">💼 Manager</button>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>


    <div id="volunteer-panel" class="login-panel active">
        <div class="form-header">
            <h2>Volunteer Login</h2>
            <p>Join our community service</p>
        </div>
        <form method="POST" action="login.php">
            <input type="hidden" name="login_type" value="volunteer">
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="your.email@example.com" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-submit">🔑 Login</button>
        </form>
        
        <div class="links">
            <a href="register.php">📝 Register Account</a>
        </div>
    </div>


    <div id="manager-panel" class="login-panel">
        
        <?php if (isset($_GET['register']) && $_GET['register'] == 1): ?>
            <div class="form-header">
                <h2>Create Manager Account</h2>
            </div>
            <div class="note">Provide a valid email and choose a secure password to register your account.</div>
            
            <form method="POST" action="admin_auth.php">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button class="btn-submit" type="submit">Create Account</button>
                <div style="margin-top: 15px; text-align: center;">
                    <a class="btn-secondary" href="login.php?pane=manager">Cancel</a>
                </div>
            </form>

        <?php else: ?>
            <div class="form-header">
                <h2>Manager Login</h2>
                <p>Manage events and resources</p>
            </div>
            <div class="note">Enter your admin name and password to sign in.</div>
            
            <form method="POST" action="admin_auth.php">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="admin_name">Name</label>
                    <input type="text" id="admin_name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Password</label>
                    <input type="password" id="admin_password" name="password" required>
                </div>
                
                <button class="btn-submit" type="submit">Login</button>
            </form>
            
            <div class="links">
                <p class="small">If you were invited but don't have an account yet:</p>
                <a class="btn-secondary" href="login.php?register=1">📝 Join Manager</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function switchLayout(type, button) {
    // Hide all panels
    document.querySelectorAll('.login-panel').forEach(panel => panel.classList.remove('active'));
    // Remove active styles from selector buttons
    document.querySelectorAll('.selector-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show chosen panel and highlight its button
    document.getElementById(type + '-panel').classList.add('active');
    button.classList.add('active');
}

// Keep the manager pane open if navigating from registration states
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('register') || urlParams.get('pane') === 'manager') {
    const mgrBtn = document.querySelectorAll('.selector-btn')[1];
    switchLayout('manager', mgrBtn);
}
</script>
</body>
</html>