<?php
session_start();
include 'db_connection.php'; // ✅ DB connection file

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empCode = trim($_POST['empcode']);
    $password = $_POST['password'];

    // Fetch user by Emp_Code
    $sql = "SELECT Emp_Code, Emp_Name, Email_ID, Password, Profile
            FROM Login_User
            WHERE Emp_Code = ?";
    $stmt = sqlsrv_query($conn, $sql, array($empCode));

    if ($stmt === false) {
        $message = "❌ Database error: " . print_r(sqlsrv_errors(), true);
    } else {
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($user) {
            // Verify password
            if (password_verify($password, $user['Password'])) {
                $_SESSION['empcode'] = $user['Emp_Code'];
                $_SESSION['empname'] = $user['Emp_Name'];
                $_SESSION['profile'] = $user['Profile'];

                header("Location: main.php"); // ✅ Redirect to dashboard
                exit();
            } else {
                $message = "❌ Invalid password.";
            }
        } else {
            $message = "❌ Employee Code not found.";
        }
    }
}

// Pass message back to login form (if any)
if (!empty($message)) {
    header("Location: login_form.php?msg=" . urlencode($message));
    exit();
}
?>
