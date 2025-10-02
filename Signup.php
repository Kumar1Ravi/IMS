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
<title>User Signup</title>
<style>
body { 
  font-family: Arial, sans-serif; 
  background: linear-gradient(135deg,#4facfe,#00f2fe); 
  display:flex;justify-content:center;align-items:center;
  height:100vh;margin:0; 
}
.container { 
  background:#fff;padding:30px 40px;border-radius:15px;
  box-shadow:0 8px 20px rgba(0,0,0,.2);
  width:380px;text-align:center;
}
.form-group { margin-bottom:15px;text-align:left; }
label { display:block;margin-bottom:6px;color:#555;font-weight:600; }
input { 
  width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;
}
button { 
  width:100%;padding:12px;background:#4facfe;
  color:#fff;border-radius:8px;border:none;cursor:pointer;
}
button:hover { background:#3a8de0; }
.message { margin-top:12px;color:#b00020;font-weight:700; }
.login-btn { 
  display:block;margin-top:15px;padding:12px;background:#00c9a7;
  color:#fff;text-decoration:none;border-radius:8px;font-weight:600;
}
.login-btn:hover { background:#00a78e; }
</style>
</head>
<body>
<div class="container">
  <h2>User Signup</h2>
  <form method="POST" action="">
    <div class="form-group">
      <label>Employee Code</label>
      <input type="number" name="empcode" required>
    </div>
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="empname" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>

    <!-- 🔹 Hidden Defaults -->
    <input type="hidden" name="profile" value="User">
    <input type="hidden" name="active_status" value="1">

    <div class="form-group">
      <label>Security Question</label>
      <input type="text" name="security_question" placeholder="e.g., What is your favorite color?" required>
    </div>
    <button type="submit">Sign Up</button>
  </form>

  <!-- ✅ Login button -->
  <a href="login.php" class="login-btn">Already have an account? Login</a>

  <p class="message"><?php echo htmlspecialchars($message); ?></p>
</div>
</body>
</html>
