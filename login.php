<?php
session_start();
include 'db_connection.php'; // ✅ DB connection file

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empCode = trim($_POST['empcode']);
    $password = $_POST['password'];

    // 1️⃣ Fetch user by Emp_Code
    $sql = "SELECT Emp_Code, Emp_Name, Email_ID, Password, Profile 
            FROM Login_User 
            WHERE Emp_Code = ?";
    $stmt = sqlsrv_query($conn, $sql, array($empCode));

    if ($stmt === false) {
        $message = "❌ Database error: " . print_r(sqlsrv_errors(), true);
    } else {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($user) {
            // 2️⃣ Verify password
            if (password_verify($password, $user['Password'])) {
                // ✅ Success → Store session
                $_SESSION['empcode'] = $user['Emp_Code'];
                $_SESSION['empname'] = $user['Emp_Name'];
                $_SESSION['profile'] = $user['Profile'];

                $message = "✅ Login successful! Welcome, " . htmlspecialchars($user['Emp_Name']);

                // Redirect to dashboard/home page
                header("Location: main.php");
                exit();
            } else {
                $message = "❌ Invalid password.";
            }
        } else {
            $message = "❌ Employee Code not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>User Login</title>
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
  width:360px;text-align:center;
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
.signup-btn { 
  display:block;margin-top:15px;padding:12px;background:#00c9a7;
  color:#fff;text-decoration:none;border-radius:8px;font-weight:600;
}
.signup-btn:hover { background:#00a78e; }
</style>
</head>
<body>
<div class="container">
  <h2>User Login</h2>
  <form method="POST" action="">
    <div class="form-group">
      <label>Employee Code</label>
      <input type="text" name="empcode" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit">Login</button>
  </form>

  <!-- ✅ Signup button -->
  <a href="signup.php" class="signup-btn">New user? Sign up here</a>

  <p class="message"><?php echo htmlspecialchars($message); ?></p>
</div>
</body>
</html>
