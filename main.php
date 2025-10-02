<?php
// main.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice Application - Main Page</title>
<style>
 /* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    display: flex;
    height: 100vh;
    overflow: hidden;
    background: #ecf0f3;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background: linear-gradient(135deg, #4a90e2 0%, #0053ba 100%);
    color: #f0f4f8;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: width 0.3s ease-in-out;
    overflow: hidden;
    position: relative;
    box-shadow: 4px 0 12px rgb(0 0 0 / 0.15);
    border-radius: 0 15px 15px 0;
}

.sidebar.collapsed {
    width: 65px;
}

/* Sidebar toggle button */
.toggle-btn {
    background-color: transparent;
    border: none;
    color: #ffe066;
    font-size: 24px;
    cursor: pointer;
    margin: 16px auto 24px auto;
    display: flex;
    justify-content: center;
    width: 90%;
    transition: color 0.3s ease;
}

.toggle-btn:hover {
    color: #fff1a8;
}

/* Sidebar header */
.sidebar h2 {
    text-align: center;
    font-weight: 700;
    font-size: 24px;
    color: #fff9e0;
    margin-bottom: 40px;
    letter-spacing: 2px;
    user-select: none;
    transition: opacity 0.3s ease;
}

.sidebar.collapsed h2 {
    opacity: 0;
    pointer-events: none;
}

/* Navigation buttons */
.nav-buttons {
    display: flex;
    flex-direction: column;
    gap: 18px;
    padding: 0 20px 24px 20px;
    transition: padding 0.3s ease;
}

.sidebar.collapsed .nav-buttons {
    padding: 0 8px 24px 8px;
}

.nav-buttons button {
    padding: 14px 22px;
    border: none;
    background-color: #3b5998;
    color: #e2e8f0;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    box-shadow: inset 0 0 0 0 transparent;
    transition:
        background-color 0.3s ease,
        box-shadow 0.25s ease,
        color 0.3s ease;
    user-select: none;
}

.nav-buttons button:hover {
    background-color: #ffe066;
    color: #0053ba;
    box-shadow:
        inset 0 -4px 10px rgb(255 224 102 / 0.35);
}

.sidebar.collapsed .nav-buttons button {
    padding: 14px 8px;
    justify-content: center;
}

.nav-buttons button span.icon {
    margin-right: 14px;
    font-size: 22px;
    user-select: none;
}
.sidebar.collapsed .nav-buttons button span.text {
    display: none;
}

/* User Info section */
.sidebar .user-info {
    padding: 22px 20px;
    text-align: center;
    font-size: 15px;
    background-color: #134e8d;
    border-radius: 0 0 15px 15px;
    color: #fff9e0;
    letter-spacing: 0.4px;
    user-select: none;
    transition: opacity 0.3s ease;
    box-shadow: inset 0 1px 5px rgb(255 255 255 / 0.12);
}

.sidebar.collapsed .user-info {
    opacity: 0;
    pointer-events: none;
}

.user-info button {
    padding: 14px 22px;
    border: none;
    background-color: #3b5998;
    color: #e2e8f0;
    font-size: 17px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 0 0 0 transparent;
    transition:
        background-color 0.3s ease,
        box-shadow 0.25s ease,
        color 0.3s ease;
    user-select: none;
    width: 100%;
    margin-bottom: 10px;
}

.user-info button:hover {
    background-color: #ffe066;
    color: #0053ba;
    box-shadow:
        inset 0 -4px 10px rgb(255 224 102 / 0.35);
}

.user-info button span.icon {
    margin-right: 14px;
    font-size: 22px;
    user-select: none;
}

/* Main content area */
.main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background-color: #f7f9fc;
    overflow: hidden;
    border-radius: 0 10px 10px 0;
    box-shadow: inset 0 0 20px rgb(0 0 0 / 0.03);
}

/* Top bar */
.topbar {
    height: 60px;
    background: linear-gradient(90deg, #0053ba 0%, #4a90e2 100%);
    color: #fff;
    display: flex;
    align-items: center;
    padding: 0 26px;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 1.2px;
    user-select: none;
    box-shadow: 0 3px 12px rgb(0 0 0 / 0.1);
    border-radius: 0 15px 0 0;
}

/* Iframe and content */
.content {
    flex: 1;
    padding: 0px 0px;
    overflow-y: auto;
    background-color: #ffffff;
    border-radius: 0 0 10px 10px;
    box-shadow:
        0 4px 15px rgb(74 144 226 / 0.15),
        inset 0 0 15px #c6dbfc;
}

iframe {
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 10px;
    box-shadow: 0 3px 12px rgb(0 0 0 / 0.08);
    background-color: #fefefe;
}

/* Responsive tweaks */
@media (max-width: 520px) {
    .sidebar {
        position: absolute;
        z-index: 1000;
        height: 100vh;
        left: 0;
        top: 0;
        border-radius: 0 10px 10px 0;
        box-shadow: 4px 0 22px rgb(0 0 0 / 0.24);
    }
    .main {
        margin-left: 0;
        border-radius: 0;
    }
}


</style>
<script>
function loadPage(pageUrl) {
    document.getElementById('content-frame').src = pageUrl;
}

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('collapsed');
}
</script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>
            <h2>Invoice App</h2>
            <div class="nav-buttons">
                <button onclick="loadPage('dashboard.php')"><span class="icon">🏠</span><span class="text">Dashboard</span></button>
                <button onclick="loadPage('import.php')"><span class="icon">⬆️</span><span class="text">Import</span></button>
                <button onclick="loadPage('analysis.php')"><span class="icon">🔍</span><span class="text">Dup Check</span></button>
                <button onclick="loadPage('invoice.php')"><span class="icon">📄</span><span class="text">Invoice</span></button>
                
            </div>
        </div>
        <div class="user-info">
            <button onclick="window.location.href='login.php'"><span class="icon">🚪</span><span class="text">Logout</span></button>
            Logged in as: Kumar R<br>
            Login: 2025-08-30 08:00 AM
        </div>
    </div>

    <!-- Main content -->
    <div class="main">
        <div class="topbar">Invoice Application</div>
        <div class="content">
            <iframe id="content-frame" src="dashboard.php"></iframe>
        </div>
    </div>
</body>
</html>
