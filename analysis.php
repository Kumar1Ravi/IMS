<?php
// index.php

include 'db_connection.php'; // $conn from SQL Server connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $newStatus = $_POST['status'];
        $sapId = $_POST['sapId'];

        if (empty($newStatus) || empty($sapId)) {
            echo json_encode(["success" => false, "message" => "Error: Invalid status or SAP ID."]);
            exit;
        }

        $sql = "UPDATE Vendor_Invoice SET User_Status = ? WHERE SAP_ID = ?";
        $params = array($newStatus, $sapId);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Error: Could not update the status.", "details" => sqlsrv_errors()]);
        } else {
            echo json_encode(["success" => true, "message" => "Status updated successfully."]);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        exit;

    } elseif ($_POST['action'] === 'clear_all_status') {
        $sql = "UPDATE Vendor_Invoice SET User_Status = ''";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Error: Could not clear User Status.", "details" => sqlsrv_errors()]);
        } else {
            echo json_encode(["success" => true, "message" => "All User Status cleared."]);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        exit;

    } elseif ($_POST['action'] === 'clear_duplicate_validation') {
        $sql = "UPDATE Vendor_Invoice SET
            DUP_True_Duplicate = 0,
            Duplicate_Status = 'Not Checked',
            DUP_SG_Status = 0,
            DUP_SGL_Status = 0,
            DUP_SGLT_Status = 0,
            DUP_SGG_Status = 0,
            DUP_SGV_Status = 0,
            DUP_SGM_Status = 0,
            DUP_SGT_Status = 0,
            DUP_SGTA_Status = 0";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Error: Could not clear duplicate validation.", "details" => sqlsrv_errors()]);
        } else {
            echo json_encode(["success" => true, "message" => "Duplicate validation cleared."]);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        exit;

    } elseif ($_POST['action'] === 'run_duplicate_check') {
        // Execute stored procedures for duplicate check
        $procedures = [
            "EXEC dbo.sp_UpdateVendorInvoiceValidation",
            "EXEC dbo.sp_FindDuplicateVendorInvoice",
            "EXEC dbo.sp_UpdateDuplicateStatus"
        ];

        foreach ($procedures as $proc) {
            $stmt = sqlsrv_query($conn, $proc);
            if ($stmt === false) {
                echo json_encode(["success" => false, "message" => "Error executing procedure: " . $proc, "details" => sqlsrv_errors()]);
                sqlsrv_close($conn);
                exit();
            }
        }

        sqlsrv_close($conn);
        echo json_encode(["success" => true, "message" => "Duplicate check completed successfully."]);
        exit();
    }
}

// Fetch Vendor_Invoice data for the 'Vendor File' tab
$sql_vendor = "SELECT
    ROW_NUMBER() OVER (ORDER BY SAP_ID) AS SNo,
    SAP_ID,
    GIS_ID,
    Employee_Name,
    Source_Country,
    Destination_Country,
    Service_Provider_Fee,
    Service_Provider_Fee_Tax,
    Govt_Fee,
    VFS_Fee,
    Misc_Other_Exp,
    Tax,
    Total_Invoice_Amount,
    User_Status
FROM Vendor_Invoice";

// Fetch data for the 'Duplicate Find' tab, including duplicate status columns
$sql_duplicate = "
SELECT
    ROW_NUMBER() OVER (ORDER BY V.SAP_ID, V.GIS_ID) AS SNo,
    V.SAP_ID, V.GIS_ID, V.Employee_Name, V.Source_Country, V.Destination_Country,
    V.Service_Provider_Fee, V.Service_Provider_Fee_Tax, V.Govt_Fee, V.VFS_Fee,
    V.Misc_Other_Exp, V.Tax, V.Total_Invoice_Amount,
    V.DUP_True_Duplicate, V.Duplicate_Status, 'Vend' AS SourceTable,
    V.User_Status, 1 AS SortOrder,
    V.DUP_SG_Status, V.DUP_SGLT_Status, V.DUP_SGG_Status, V.DUP_SGV_Status,
    V.DUP_SGM_Status, V.DUP_SGT_Status, V.DUP_SGTA_Status
FROM Vendor_Invoice V
WHERE EXISTS (
    SELECT 1 FROM Invoice_Consolidated C
    WHERE V.SAP_ID = C.SAP_ID AND V.GIS_ID = C.GIS_ID
)
UNION
SELECT
    NULL AS SNo,
    C.SAP_ID, C.GIS_ID, C.Employee_Name, C.Source_Country, C.Destination_Country,
    C.Service_Provider_Fee, C.Service_Provider_Fee_Tax, C.Govt_Fee, C.VFS_Fee,
    C.Misc_Other_Exp, C.Tax, C.Total_Invoice_Amount,
    NULL AS DUP_True_Duplicate, NULL AS Duplicate_Status, 'Cons' AS SourceTable,
    NULL AS User_Status, 2 AS SortOrder,
    NULL AS DUP_SG_Status, NULL AS DUP_SGLT_Status, NULL AS DUP_SGG_Status, NULL AS DUP_SGV_Status,
    NULL AS DUP_SGM_Status, NULL AS DUP_SGT_Status, NULL AS DUP_SGTA_Status
FROM Invoice_Consolidated C
WHERE EXISTS (
    SELECT 1 FROM Vendor_Invoice V
    WHERE V.SAP_ID = C.SAP_ID AND V.GIS_ID = C.GIS_ID
)
ORDER BY SAP_ID, GIS_ID, SortOrder;
";

$result_vendor = sqlsrv_query($conn, $sql_vendor);
if ($result_vendor === false) die(print_r(sqlsrv_errors(), true));

$result_duplicate = sqlsrv_query($conn, $sql_duplicate);
if ($result_duplicate === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        // ...existing code for update_status...
        $newStatus = $_POST['status'];
        $sapId = $_POST['sapId'];

        if (empty($newStatus) || empty($sapId)) {
            echo json_encode(["success" => false, "message" => "Error: Invalid status or SAP ID."]);
            exit;
        }

        $sql = "UPDATE Vendor_Invoice SET User_Status = ? WHERE SAP_ID = ?";
        $params = array($newStatus, $sapId);

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Error: Could not update the status.", "details" => sqlsrv_errors()]);
        } else {
            echo json_encode(["success" => true, "message" => "Status updated successfully."]);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        exit;

    } elseif ($_POST['action'] === 'clear_all_status') {
        $sql = "UPDATE Vendor_Invoice SET User_Status = ''";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Error: Could not clear User Status.", "details" => sqlsrv_errors()]);
        } else {
            echo json_encode(["success" => true, "message" => "All User Status cleared."]);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        exit;

    } elseif ($_POST['action'] === 'clear_duplicate_validation') {
        $sql = "UPDATE Vendor_Invoice SET
            DUP_True_Duplicate = 0,
            Duplicate_Status = 'Not Checked',
            DUP_SG_Status = 0,
            DUP_SGL_Status = 0,
            DUP_SGLT_Status = 0,
            DUP_SGG_Status = 0,
            DUP_SGV_Status = 0,
            DUP_SGM_Status = 0,
            DUP_SGT_Status = 0,
            DUP_SGTA_Status = 0";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Error: Could not clear duplicate validation.", "details" => sqlsrv_errors()]);
        } else {
            echo json_encode(["success" => true, "message" => "Duplicate validation cleared."]);
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        exit;
    }elseif ($_POST['action'] === 'run_duplicate_check') {
    $procedures = [
        "EXEC dbo.sp_UpdateVendorInvoiceValidation",
        "EXEC dbo.sp_FindDuplicateVendorInvoice",
        "EXEC dbo.sp_UpdateDuplicateStatus"
    ];

    foreach ($procedures as $proc) {
        $stmt = sqlsrv_query($conn, $proc);
        if ($stmt === false) {
            echo json_encode([
                "success" => false, 
                "message" => "Error executing procedure: " . $proc,
                "details" => sqlsrv_errors()
            ]);
            sqlsrv_close($conn);
            exit();
        }
    }

    sqlsrv_close($conn);
    echo json_encode([
        "success" => true, 
        "message" => "Duplicate check completed successfully."
    ]);
    exit();
}
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Body & Font */
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #fffaf7;
    margin: 0; padding: 0;
    color: #333;
}

/* Header */
.header {
    background: #fff3e6;
    padding: 20px 30px;
    border-bottom: 2px solid #ffd2a6;
    text-align: center;
}

.header h1 {
    color: #5a3e2b;
    font-size: 28px;
    font-weight: 600;
    margin: 0;
}

.header p {
    color: #7a6a5a;
    font-size: 14px;
    margin: 5px 0 0;
}

/* Navigation */
.nav-bar {
    background: #ffe6cc;
    padding: 10px 30px;
    border-bottom: 1px solid #ffd2a6;
}

.nav-links {
    display: flex;
    justify-content: center;
    gap: 20px;
}

.nav-links a {
    color: #4b3c2b;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 8px;
    background: #fff3e6;
    transition: 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-links a:hover {
    background: #ffd4a6;
}

.nav-links a i {
    font-size: 14px;
}

/* Main Container */
.main-container {
    max-width: 1800px;
    margin: 20px auto;
    padding: 0 20px;
}

/* Enhanced Tab Container */
.tab-container {
    display: flex;
    background: #fff3e6;
    border-radius: 15px 15px 0 0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 0;
    border: 1px solid #ffd2a6;
}

.tab {
    flex: 1;
    padding: 20px 25px;
    cursor: pointer;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    color: #495057;
    border: none;
    font-size: 16px;
    font-weight: 100;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.tab::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
    transition: left 0.5s;
}

.tab:hover::before {
    left: 100%;
}

.tab.active {
    background: linear-gradient(135deg, #007bff, #0056d2);
    color: white;
    box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1);
}

.tab:hover:not(.active) {
    background: linear-gradient(135deg, #e9ecef, #dee2e6);
    transform: translateY(-2px);
}

.tab i {
    margin-right: 8px;
    font-size: 18px;
}

/* Enhanced Tab Content */
.tab-content {
    display: none;
    background: #fffaf7;
    padding: 30px;
    border-radius: 0 0 15px 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border: 1px solid #ffd2a6;
    border-top: none;
}

.tab-content.active {
    display: block;
}

/* Tab Header */
.tab-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.tab-header h2 {
    color: #5a3e2b;
    font-size: 28px;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tab-header h2 i {
    text-shadow: 0 2px 10px rgba(255, 210, 166, 0.3);
}

.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056d2);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056d2, #004085);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    background: linear-gradient(135deg, #20c997, #17a2b8);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

/* Table Wrapper */
.table-wrapper { width: 100%; overflow-x: auto; margin-bottom: 15px; }

/* Tables */
table {
    border-collapse: separate; border-spacing: 0;
    width: 100%; background: #ffffffcc;
    border-radius: 12px; box-shadow: 0 1px 12px rgba(0,0,0,0.05);
}
th, td { font-size: 12px; padding: 5px 5px; text-align: center; }
th {
    background: #fff1e0; font-weight: 600; color: #5a3e2b;
    border-bottom: 2px solid #ffd2a6; letter-spacing: 1px;
}
td {
    background: #fffaf7; color: #3b3b3b;
    border-bottom: 1px solid #eee5e0;
    transition: background 0.15s;
}
tr:hover td { background: #ffe1c2cc !important; }

/* Highlight Duplicate */
.highlight-dup {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
    font-weight: bold;
    
}

/* Status Select */
select {
    padding: 6px 12px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    background-color: white;
    color: #495057;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 100px;
}

select:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

select:hover {
    border-color: #0056d2;
}

/* Notification */
#notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(40, 167, 69, 0.3);
    font-weight: 600;
    display: none;
    z-index: 1000;
    min-width: 320px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

#notification .close-btn {
    position: absolute;
    top: 8px;
    right: 12px;
    cursor: pointer;
    font-weight: bold;
    font-size: 20px;
    color: white;
    user-select: none;
    transition: opacity 0.3s ease;
}

#notification .close-btn:hover {
    opacity: 0.7;
}

/* Statistics Cards */
.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff3e6;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    border: 1px solid #ffd2a6;
}

.stat-card h3 {
    color: #5a3e2b;
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 5px;
}

.stat-card p {
    color: #7a6a5a;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
    th, td {
        padding: 12px 15px;
        font-size: 13px;
    }

    .tab {
        padding: 16px 20px;
        font-size: 15px;
    }

    /* Adjust column widths for tablets */
    th:nth-child(4), td:nth-child(4) { width: 120px; } /* Employee Name */
    th:nth-child(5), td:nth-child(5) { width: 100px; } /* Source Country */
    th:nth-child(6), td:nth-child(6) { width: 110px; } /* Destination Country */
}

@media (max-width: 768px) {
    .header, .nav-bar {
        padding: 20px;
    }

    .main-container {
        margin: 20px auto;
        padding: 0 15px;
    }

    .tab-container {
        flex-direction: column;
    }

    .tab {
        padding: 15px 18px;
        font-size: 14px;
    }

    .tab-content {
        padding: 20px;
    }

    .tab-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .action-buttons {
        width: 100%;
        justify-content: center;
    }

    .btn {
        flex: 1;
        justify-content: center;
    }

    .stats-section {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .stat-card {
        padding: 20px;
    }

    .stat-card h3 {
        font-size: 28px;
    }

    /* Table becomes scrollable on mobile */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    table {
        min-width: 1000px; /* Reduced for mobile */
    }

    th, td {
        white-space: nowrap;
        min-width: 80px;
    }
}

@media (max-width: 480px) {
    .header h1 {
        font-size: 24px;
    }

    .tab-content h2 {
        font-size: 20px;
    }

    .stats-section {
        grid-template-columns: 1fr;
    }

    th, td {
        padding: 8px 10px;
        font-size: 12px;
        min-width: 70px;
    }

    .tab-container {
        border-radius: 8px 8px 0 0;
    }

    .tab-content {
        border-radius: 0 0 8px 8px;
    }

    /* Further reduce table width for very small screens */
    table {
        min-width: 900px;
    }

    /* Hide less important columns on very small screens */
    th:nth-child(7), td:nth-child(7),
    th:nth-child(8), td:nth-child(8),
    th:nth-child(9), td:nth-child(9),
    th:nth-child(10), td:nth-child(10),
    th:nth-child(11), td:nth-child(11),
    th:nth-child(12), td:nth-child(12) {
        display: none;
    }
}

/* Custom Scrollbar */
.table-container::-webkit-scrollbar {
    width: 8px;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #007bff, #0056d2);
    border-radius: 10px;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #0056d2, #004085);
}

/* Loading Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.tab-content {
    animation: fadeIn 0.5s ease-out;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
</head>
<body>

<!-- Main Container -->
<div class="main-container">
   
    <!-- Duplicate Find Tab -->
    <div id="duplicateFind" class="tab-content">
        <div class="tab-header">
            <h2><i class="fas fa-search-plus"></i> Duplicate Analysis Results</h2>
            <div class="action-buttons">
                <button class="btn btn-primary"onclick="window.location.href='run_duplicate_check.php'">
                    Execute Duplicate Check
                </button>                                     
                <button class="btn btn-danger" onclick="clearDuplicateValidation()">
                    <i class="fas fa-eraser"></i> Clear Duplicate validation
                </button>
                <button class="btn btn-danger" onclick="clearAllUserStatus()">
                    <i class="fas fa-eraser"></i> Clear All User Status
                </button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>SNo</th>
                        <th>SAP ID</th>
                        <th>GIS ID</th>
                        <th>Employee Name</th>
                        <th>Source Country</th>
                        <th>Destination Country</th>
                        <th>SP Fee</th>
                        <th>SP Tax</th>
                        <th>Gov Fee</th>
                        <th>VFS Fee</th>
                        <th>Misc Exp</th>
                        <th>Tax</th>
                        <th>Total Amount</th>
                        <th>Duplicate Status</th>
                        <th>Source</th>
                        <th>User Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Reset result pointer
                $result_duplicate = sqlsrv_query($conn, $sql_duplicate);
                while($row = sqlsrv_fetch_array($result_duplicate, SQLSRV_FETCH_ASSOC)):
                ?>
                <tr
                    data-dup-sg="<?php echo htmlspecialchars($row['DUP_SG_Status'] ?? ''); ?>"
                    data-dup-sglt="<?php echo htmlspecialchars($row['DUP_SGLT_Status'] ?? ''); ?>"
                    data-dup-sgg="<?php echo htmlspecialchars($row['DUP_SGG_Status'] ?? ''); ?>"
                    data-dup-sgv="<?php echo htmlspecialchars($row['DUP_SGV_Status'] ?? ''); ?>"
                    data-dup-sgm="<?php echo htmlspecialchars($row['DUP_SGM_Status'] ?? ''); ?>"
                    data-dup-sgt="<?php echo htmlspecialchars($row['DUP_SGT_Status'] ?? ''); ?>"
                    data-dup-sgta="<?php echo htmlspecialchars($row['DUP_SGTA_Status'] ?? ''); ?>"
                    data-dup-true-duplicate="<?php echo htmlspecialchars($row['DUP_True_Duplicate'] ?? ''); ?>"
                >
                    <td><?php echo htmlspecialchars($row['SNo'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['SAP_ID'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['GIS_ID'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['Employee_Name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['Source_Country'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['Destination_Country'] ?? '-'); ?></td>
                    <td>$<?php echo number_format($row['Service_Provider_Fee'] ?? 0, 2); ?></td>
                    <td>$<?php echo number_format($row['Service_Provider_Fee_Tax'] ?? 0, 2); ?></td>
                    <td>$<?php echo number_format($row['Govt_Fee'] ?? 0, 2); ?></td>
                    <td>$<?php echo number_format($row['VFS_Fee'] ?? 0, 2); ?></td>
                    <td>$<?php echo number_format($row['Misc_Other_Exp'] ?? 0, 2); ?></td>
                    <td>$<?php echo number_format($row['Tax'] ?? 0, 2); ?></td>
                    <td>$<?php echo number_format($row['Total_Invoice_Amount'] ?? 0, 2); ?></td>
                    <td><?php echo htmlspecialchars($row['Duplicate_Status'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['SourceTable'] ?? '-'); ?></td>
                    <td>
                        <?php if ($row['SourceTable'] === 'Vend'): ?>
                            <select onchange="updateUserStatus(this.value, '<?php echo htmlspecialchars($row['SAP_ID']); ?>')">
                                <option value="" <?php echo (empty($row['User_Status'])) ? 'selected' : ''; ?>></option>
                                <option value="Unique" <?php echo ($row['User_Status'] === 'Unique') ? 'selected' : ''; ?>>Unique</option>
                                <option value="Duplicate" <?php echo ($row['User_Status'] === 'Duplicate') ? 'selected' : ''; ?>>Duplicate</option>
                                <option value="Refer Back" <?php echo ($row['User_Status'] === 'Refer Back') ? 'selected' : ''; ?>>Refer Back</option>
                                <option value="Rejected" <?php echo ($row['User_Status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Notification -->
<div id="notification">
    <span class="close-btn" onclick="hideNotification()">&times;</span>
    <div>Successfully Completed the Duplicate Check.</div>
    <div style="font-size: 12px; margin-top: 4px; opacity: 0.8;">
        <span id="notification-time"></span>
    </div>
</div>

<script>
// Force select the Duplicate Find tab and button
function selectDuplicateFindTab() {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
    // Remove active from all tab buttons
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    // Show Duplicate Find tab content
    document.getElementById('duplicateFind').classList.add('active');
    // Set Duplicate Find tab button as active
    document.querySelector('.tab[onclick*="duplicateFind"]').classList.add('active');
}
// Update User Status via AJAX
function updateUserStatus(status, sapId){
    if(!status || !sapId) return;
    const formData = new FormData();
    formData.append('action','update_status');
    formData.append('status',status);
    formData.append('sapId',sapId);

    fetch('',{method:'POST',body:formData})
    .then(resp=>resp.text())
    .then(data=>console.log(data))
    .catch(err=>console.error(err));
}

// Tab switching functionality
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    if(event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
}

// Highlight duplicate cells on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll("#duplicateFind tbody tr").forEach(row => {
        const mapping = [
            { attr: 'dupSg', index: 6 },
            { attr: 'dupSglt', index: 7 },
            { attr: 'dupSgg', index: 8 },
            { attr: 'dupSgv', index: 9 },
            { attr: 'dupSgm', index: 10 },
            { attr: 'dupSgt', index: 11 },
            { attr: 'dupSgta', index: 12 }
        ];
        mapping.forEach(item => {
            const dataAttribute = row.dataset[item.attr];
            if (dataAttribute === "1") {
                const cell = row.cells[item.index];
                if (cell) {
                    cell.classList.add("highlight-dup");
                }
            }
        });

        // Also highlight the Duplicate Status column if it's a duplicate
        const duplicateStatusCell = row.cells[13]; // Duplicate Status column
        if (duplicateStatusCell && row.dataset.dupTrueDuplicate === "1") {
            duplicateStatusCell.classList.add("highlight-dup");
        }
    });

    // Show notification if status=success in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'success') {
        showNotification();
    }

    // If hash is #duplicateFind, activate that tab
    if (window.location.hash === '#duplicateFind') {
        showTab('duplicateFind');
    }

});

function showNotification() {
    const notification = document.getElementById('notification');
    const timeSpan = document.getElementById('notification-time');
    const now = new Date();
    timeSpan.textContent = now.toLocaleTimeString();
    notification.style.display = 'block';
    setTimeout(hideNotification, 3000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.style.display = 'none';
}

function selectDuplicateFindTab() {
    showTab('duplicateFind');
    // Remember tab in localStorage so it stays selected after reload
    localStorage.setItem('activeTab', 'duplicateFind');
}

// Restore Duplicate Find tab after reload if needed
window.addEventListener('load', function() {
    const activeTab = localStorage.getItem('activeTab');
    if (activeTab === 'duplicateFind') {
        showTab('duplicateFind');
        // Clear stored tab so future reloads default to Vendor File
        localStorage.removeItem('activeTab');
    }
});

function clearAllUserStatus() {
    if (!confirm('Are you sure you want to clear all User Status values?')) return;
    const formData = new FormData();
    formData.append('action', 'clear_all_status');
    fetch('', {method: 'POST', body: formData})
        .then(resp => resp.text())
        .then(data => {
            document.querySelectorAll('#duplicateFind select').forEach(sel => {
                sel.selectedIndex = -1;
            });
            alert('All User Status values cleared.');
        })
        .catch(err => alert('Error clearing User Status.'));
}

function clearDuplicateValidation() {
    if (!confirm('Are you sure you want to clear all duplicate validation values?')) return;
    const formData = new FormData();
    formData.append('action', 'clear_duplicate_validation');
    fetch('', {method: 'POST', body: formData})
        .then(resp => resp.text())
        .then(data => {
            document.querySelectorAll('#duplicateFind select').forEach(sel => {
                sel.selectedIndex = -1;
            });
            alert('Duplicate validation cleared.');
            // Reload and navigate back to analysis.php
            window.location.href = 'analysis.php';

        })
        .catch(err => alert('Error clearing duplicate validation.'));
}


</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>
