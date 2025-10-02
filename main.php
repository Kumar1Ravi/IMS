<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Reset & Base */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
body { background: #f4f6f8; color: #333; }

/* Top Navigation Bar */
.topnav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #007bff;
    padding: 12px 20px;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.topnav .logo {
    font-size: 18px;
    font-weight: bold;
}

.topnav .nav-links {
    display: flex;
    gap: 15px;
}

.topnav .nav-links button {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 6px;
    transition: background 0.2s;
}

.topnav .nav-links button:hover,
.topnav .nav-links button.active {
    background: rgba(255,255,255,0.2);
}

/* User Info Section */
.topnav .user-info {
    font-size: 13px;
    opacity: 0.9;
}

.topnav .user-info button {
    background: #dc3545;
    border: none;
    color: #fff;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
    font-size: 12px;
    transition: background 0.2s;
}

.topnav .user-info button:hover {
    background: #c82333;
}

/* Main Content Area */
.content {
    padding: 15px;
}


.content iframe {
    width: 100%;
    height: calc(100vh - 90px);
    border: none;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    background: #fff;
}

/* Responsive Design */
@media (max-width: 768px) {
    .topnav {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .topnav .nav-links {
        flex-wrap: wrap;
        gap: 8px;
    }
    .content iframe {
        height: calc(100vh - 110px);
    }
}

@media (max-width: 480px) {
    .topnav .logo { font-size: 16px; }
    .topnav .nav-links button { font-size: 12px; padding: 4px 8px; }
    .topnav .user-info { font-size: 11px; }
    .topnav .user-info button { font-size: 11px; padding: 4px 8px; }
}
</style>
<script>
function loadPage(pageUrl, btn) {
    document.getElementById('content-frame').src = pageUrl;
    // Remove active class from all buttons
    document.querySelectorAll('.nav-links button').forEach(b => b.classList.remove('active'));
    // Add active to clicked button
    if(btn) btn.classList.add('active');
}
</script>
</head>
<body>

<?php
session_start();
include 'db_connection.php';
$empName = '';
if (isset($_SESSION['empcode'])) {
    // Try to get name from session first
    if (!empty($_SESSION['empname'])) {
        $empName = $_SESSION['empname'];
    } else {
        // Fallback: fetch from DB
        $empCode = $_SESSION['empcode'];
        $sql = "SELECT Emp_Name FROM Login_User WHERE Emp_Code = ?";
        $stmt = sqlsrv_query($conn, $sql, array($empCode));
        if ($stmt !== false) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($row && !empty($row['Emp_Name'])) {
                $empName = $row['Emp_Name'];
            }
        }
    }
}
?>
<!-- Top Navigation -->
<div class="topnav">
    <div class="logo">Invoice Manager</div>
    <div class="nav-links">
        <button onclick="loadPage('dashboard.php', this)" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</button>
        <button onclick="loadPage('import.php', this)"><i class="fas fa-upload"></i> Import Data</button>        
        <button onclick="loadPage('index.php', this)"><i class="fas fa-table"></i> Data View</button>
        <button onclick="loadPage('analysis.php', this)"><i class="fas fa-chart-bar"></i> Analysis</button>

        <button onclick="loadPage('Invoice.php', this)"><i class="fas fa-file-invoice"></i> Create Invoice</button>
    </div>
    <div class="user-info">
        Welcome, <?php echo htmlspecialchars($empName ?: 'User'); ?>
        <button onclick="window.location.href='login.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- Main Content -->
<div class="content">
    <iframe id="content-frame" src="dashboard.php"></iframe>
</div>

</body>
</html>
