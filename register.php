<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
    $birthdate = isset($_POST["birthdate"]) ? trim($_POST["birthdate"]) : '';
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $occupation = isset($_POST["occupation"]) ? trim($_POST["occupation"]) : '';
    $contact = isset($_POST["contact"]) ? trim($_POST["contact"]) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
    $confirm_password = isset($_POST["confirm_password"]) ? trim($_POST["confirm_password"]) : '';
    
    $error = '';
    $success = '';
    $conn = db_connect();

    // Validation (Added empty($birthdate) to the check)
    if (empty($name) || empty($birthdate) || empty($email) || empty($occupation) || empty($contact) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // 1. Check if email already exists first
        $stmt = $conn->prepare("SELECT Volunteer_Id FROM Volunteer WHERE Email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "This email has already been registered.";
            $stmt->close();
        } else {
            $stmt->close(); // Close the email check statement safely

            // 2. Email is safe! Now generate the next Volunteer_Id (e.g., V001, V002)
            $id_stmt = $conn->prepare('SELECT COUNT(*) as count FROM Volunteer');
            $id_stmt->execute();
            $res = $id_stmt->get_result();
            $row = $res->fetch_assoc();
            $volunteer_number = $row['count'] + 1;
            $volunteer_id = 'V' . str_pad($volunteer_number, 3, '0', STR_PAD_LEFT);
            $id_stmt->close();
            
            // 3. Hash password and insert using the corrected 7 's' placeholders
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO Volunteer (Volunteer_Id, Name, Birthdate, Email, Occupation, ContactNo, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            // CHANGED TO 'sssssss' (7 s's) TO MATCH THE 7 VARIABLES BELOW
            $insert_stmt->bind_param('sssssss', $volunteer_id, $name, $birthdate, $email, $occupation, $contact, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = "Registration successful! Redirecting to login...";
                header("refresh:2;url=index.php"); 
            } else {
                $error = "Registration failed. Please try again.";
            }
            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
    <link rel="icon" type="image/png" href="images/cc_logo.png">
    <link rel="shortcut icon" type="image/png" href="images/cc_logo.png">
    <link rel="stylesheet" hrefr="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E6E6E6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #BAC8B1 0%, #404E3B 100%);
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: none;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: #721c24;
            display: block;
        }

        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: #155724;
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #404E3B;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="date"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #BAC8B1;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #404E3B;
            box-shadow: 0 0 0 3px rgba(64, 78, 59, 0.1);
            background-color: #f9f9f9;
        }

        .password-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .password-group .form-group {
            margin-bottom: 0;
        }

        .button-group {
            margin-top: 30px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #BAC8B1 0%, #404E3B 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(64, 78, 59, 0.2);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(64, 78, 59, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #E6E6E6;
        }

        .links a {
            color: #404E3B;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #BAC8B1;
            text-decoration: underline;
        }

        .info-box {
            background: #E6E6E6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #404E3B;
        }

        .info-box p {
            color: #404E3B;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
        }

        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }

            .header h1 {
                font-size: 24px;
            }

            .password-group {
                grid-template-columns: 1fr;
            }

            .links {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .links a {
                display: block;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📝 Volunteer Registration</h1>
        <p>Join our community service platform</p>
    </div>

    <?php if (!empty($error)): ?>
    <div class="alert error">
    ❌ <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
    <div class="alert success">
    ✅ <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="info-box">
        <p>💡 <strong>Registration Tips:</strong> Create an account to browse and apply for community service opportunities. Your information helps managers match you with the right opportunities.</p>
    </div>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
            <label for="birthdate">Birthdate</label>
            <input type="date" name="birthdate" id="birthdate" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="your.email@example.com" required>
        </div>

        <div class="form-group">
            <label for="occupation">Occupation</label>
            <input type="text" name="occupation" id="occupation" placeholder="e.g., Student, Engineer, etc." required>
        </div>

        <div class="form-group">
            <label for="contact">Contact Number</label>
            <input type="text" name="contact" id="contact" placeholder="Your phone number" required>
        </div>

        <div class="password-group">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Min. 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password" required>
            </div>
        </div>

        <div class="button-group">
            <button type="submit">📝 Register</button>
        </div>
    </form>

    <div class="links">
        <a href="login.php">🔑 Already have an account? Login</a>
    </div>
</div>

</body>
</html>
