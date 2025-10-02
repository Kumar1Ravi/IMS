<?php
include 'db_connection.php'; // ✅ include your DB connection

$message = "";

// Check DB connection
if ($conn === false) {
    die("❌ Database connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empCode = trim($_POST['empcode']);
    $empName = trim($_POST['empname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $securityQuestion = trim($_POST['security_question']);

    // ✅ Defaults (hidden fields)
    $profile = "User";
    $active_status = 1;

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $checkUserSql = "SELECT Email_ID FROM Login_User WHERE Email_ID = ?";
    $checkUserStmt = sqlsrv_query($conn, $checkUserSql, array($email));

    if ($checkUserStmt === false) {
        $message = "❌ Database error while checking email.";
    } elseif (sqlsrv_fetch_array($checkUserStmt, SQLSRV_FETCH_ASSOC)) {
        $message = "❌ Email already registered. Please log in.";
    } else {
        // Insert new user
        $insertSql = "INSERT INTO Login_User
            (Emp_Code, Emp_Name, Email_ID, Password, Profile, Security_Question, Register_Login_DateTime)
            VALUES (?, ?, ?, ?, ?, ?, GETDATE())";

        $params = array($empCode, $empName, $email, $passwordHash, $profile, $securityQuestion);

        $insertStmt = sqlsrv_query($conn, $insertSql, $params);

        if ($insertStmt) {
            $message = "✅ Signup successful! You can now log in.";
        } else {
            $message = "❌ Error inserting user: " . print_r(sqlsrv_errors(), true);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>User Signup - Invoice Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Modern Background */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    overflow: hidden;
    position: relative;
}

/* Animated Background Elements */
body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(0, 123, 255, 0.2) 0%, transparent 50%);
    animation: float 20s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    33% { transform: translateY(-20px) rotate(120deg); }
    66% { transform: translateY(10px) rotate(240deg); }
}

/* Enhanced Signup Container */
.container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    padding: 35px 45px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 480px;
    text-align: center;
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.container:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
}

/* Logo/Icon Section */
.logo-section {
    margin-bottom: 25px;
}

.logo-section i {
    font-size: 48px;
    color: #007bff;
    margin-bottom: 15px;
    text-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.logo-section h2 {
    color: #333333;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.logo-section p {
    color: #666666;
    font-size: 16px;
    margin: 0;
}

/* Enhanced Form Elements */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
    position: relative;
}

.form-group.full-width {
    grid-column: span 2;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333333;
    font-weight: 600;
    font-size: 14px;
    transition: color 0.3s ease;
}

.form-group input {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    font-size: 15px;
    background: #ffffff;
    transition: all 0.3s ease;
    box-sizing: border-box;
    position: relative;
}

.form-group input:focus {
    border-color: #007bff;
    outline: none;
    background: #f8f9ff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #adb5bd;
    transition: color 0.3s ease;
}

.form-group input:focus::placeholder {
    color: #007bff;
    opacity: 0.7;
}

/* Icon indicators for different input types */
.form-group:nth-child(1)::after,
.form-group:nth-child(2)::after,
.form-group:nth-child(3)::after,
.form-group:nth-child(4)::after,
.form-group:nth-child(5)::after {
    content: '';
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.form-group:nth-child(1)::after { content: '👤'; } /* Employee Code */
.form-group:nth-child(2)::after { content: '📝'; } /* Full Name */
.form-group:nth-child(3)::after { content: '📧'; } /* Email */
.form-group:nth-child(4)::after { content: '🔒'; } /* Password */
.form-group:nth-child(5)::after { content: '❓'; } /* Security Question */

.form-group input:focus ~ ::after {
    opacity: 1;
}

/* Enhanced Button */
button[type="submit"] {
    width: 100%;
    padding: 16px 30px;
    background: linear-gradient(135deg, #007bff, #0056d2);
    color: #ffffff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
}

button[type="submit"]::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

button[type="submit"]:hover::before {
    left: 100%;
}

button[type="submit"]:hover {
    background: linear-gradient(135deg, #0056d2, #004085);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
}

button[type="submit"]:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
}

/* Enhanced Login Link */
.login-btn {
    display: inline-block;
    padding: 12px 25px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.login-btn:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    color: #ffffff;
}

.login-btn i {
    margin-right: 8px;
}

/* Enhanced Message */
.message {
    margin-top: 20px;
    padding: 15px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-align: center;
    font-size: 14px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.message.success {
    background: rgba(40, 167, 69, 0.1);
    color: #155724;
    border-color: rgba(40, 167, 69, 0.3);
}

.message.error {
    background: rgba(220, 53, 69, 0.1);
    color: #721c24;
    border-color: rgba(220, 53, 69, 0.3);
}

/* Progress Indicator */
.signup-progress {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.step {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e1e5e9;
    margin: 0 4px;
    transition: background 0.3s ease;
}

.step.active {
    background: #007bff;
}

/* Loading Animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.container {
    animation: pulse 2s ease-in-out infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 25px 30px;
        margin: 20px;
        max-width: none;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .form-group.full-width {
        grid-column: span 1;
    }

    .logo-section h2 {
        font-size: 24px;
    }

    .form-group input {
        padding: 12px 16px;
        font-size: 16px;
    }

    button[type="submit"] {
        padding: 14px 25px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 20px 20px;
    }

    .logo-section i {
        font-size: 36px;
    }

    .logo-section h2 {
        font-size: 22px;
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus states for accessibility */
button:focus,
.login-btn:focus,
input:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}
</style>
</head>
<body>
<div class="container">
    <div class="logo-section">
        <i class="fas fa-user-plus"></i>
        <h2>Create Account</h2>
        <p>Join Invoice Management System</p>
    </div>

    <form method="POST" action="">
        <div class="form-grid">
            <div class="form-group">
                <label for="empcode">Employee Code</label>
                <input type="number" id="empcode" name="empcode" required placeholder="Enter employee code">
            </div>

            <div class="form-group">
                <label for="empname">Full Name</label>
                <input type="text" id="empname" name="empname" required placeholder="Enter full name">
            </div>

            <div class="form-group full-width">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter email address">
            </div>

            <div class="form-group full-width">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a strong password">
            </div>

            <div class="form-group full-width">
                <label for="security_question">Security Question</label>
                <input type="text" id="security_question" name="security_question" placeholder="e.g., What is your favorite color?" required>
            </div>
        </div>

        <!-- Hidden Defaults -->
        <input type="hidden" name="profile" value="User">
        <input type="hidden" name="active_status" value="1">

        <button type="submit">
            <i class="fas fa-user-plus"></i> Create Account
        </button>
    </form>

    <a href="login.php" class="login-btn">
        <i class="fas fa-sign-in-alt"></i> Already have an account? Login
    </a>

    <?php if($message): ?>
    <div class="message <?php echo strpos($message, '❌') !== false ? 'error' : 'success'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
