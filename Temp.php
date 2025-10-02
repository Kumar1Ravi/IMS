<?php
// Temp.php

include 'db_connection.php'; // $conn from SQL Server connection

// --- 1. Handle POST request for status update via AJAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
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
        $error_details = sqlsrv_errors();
        $error_message = "Error: Could not update the status.";
        if ($error_details) {
            $error_message .= " Details: " . implode(" ", array_map(function($e) { return $e['message']; }, $error_details));
        }
        echo json_encode(["success" => false, "message" => $error_message]);
    } else {
        echo json_encode(["success" => true, "message" => "Status updated successfully."]);
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    exit;
}

// --- 2. Data Fetching Logic (REVISED to use arrays) ---

// a. Fetch Vendor_Invoice data for the 'Vendor File' tab
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

$result_vendor = sqlsrv_query($conn, $sql_vendor);
if ($result_vendor === false) die(print_r(sqlsrv_errors(), true));

// Read ALL Vendor data into an array
$vendor_data = [];
while ($row = sqlsrv_fetch_array($result_vendor, SQLSRV_FETCH_ASSOC)) {
    $vendor_data[] = $row;
}
if ($result_vendor) sqlsrv_free_stmt($result_vendor);


// b. Fetch data for the 'Duplicate Find' tab
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

$result_duplicate = sqlsrv_query($conn, $sql_duplicate);
if ($result_duplicate === false) die(print_r(sqlsrv_errors(), true));

// Read ALL Duplicate data into an array
$duplicate_data = [];
while ($row = sqlsrv_fetch_array($result_duplicate, SQLSRV_FETCH_ASSOC)) {
    $duplicate_data[] = $row;
}
if ($result_duplicate) sqlsrv_free_stmt($result_duplicate);

// Close the connection now that all data is fetched
sqlsrv_close($conn); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vendor Analysis Dashboard</title>
<style>
/* Body & Font */
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f7f7f7;
    margin: 0; padding: 0;
    color: #333;
}

/* Tabs */
.tab-container {
    display: flex; gap: 10px; padding: 10px 15px;
}
.tab {
    padding: 8px 18px; font-size: 14px; font-weight: 500;
    cursor: pointer; border-radius: 15px 15px 0 0;
    background: #e8e8e8; color: #4b4b4b;
    transition: 0.2s;
}
.tab.active {
    background: #fff3e6; color: #5a3e2b;
    font-weight: bold; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Card Panel */
.card-panel {
    max-width: 95vw;
    margin: 20px auto; border-radius: 15px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
    background: #fff; padding: 20px 15px;
}

/* Headings */
h2 { margin: 10px 0 18px; font-size: 18px; color: #4b3c2d; font-weight: 600; }

/* Buttons */
button {
    padding: 6px 14px; font-size: 13px; font-weight: 500;
    border-radius: 10px; border: none;
    background: #ffe6cc; color: #4b3c2b;
    cursor: pointer; transition: 0.15s;
}
button:hover { background: #ffd4a6; }

/* Table Wrapper */
.table-wrapper { width: 100%; overflow-x: auto; margin-bottom: 15px; }

/* Tables */
table {
    border-collapse: separate; border-spacing: 0;
    width: 100%; background: #ffffffcc;
    border-radius: 12px; box-shadow: 0 1px 12px rgba(0,0,0,0.05);
}
th, td { font-size: 12px; padding: 8px 10px; text-align: center; }
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

/* Select */
select {
    padding: 4px 10px; font-size: 12px;
    border-radius: 6px; border: 1px solid #d1c1b3;
    background: #f9f3ee; color: #333;
}
select:focus { border-color: #f5a35f; background: #fff; }

/* Highlight duplicate */
.highlight-dup { background: #ffb3b3 !important; color: #000; font-weight: bold; }

/* Tab Content */
.tab-content { display: none; }
.tab-content.active { display: block; margin-top: -8px; animation: fadeInTab 0.3s; }
@keyframes fadeInTab { from {opacity:0; transform:translateY(15px);} to {opacity:1; transform:translateY(0);} }

/* Responsive Tables for Mobile */
@media (max-width: 900px) {
    table, thead, tbody, th, td, tr { display: block; }
    td { text-align: left; padding-left: 40%; position: relative; }
    td:before {
        content: attr(data-label); position: absolute;
        top: 8px; left: 10px; width: 35%;
        font-weight: bold; color: #7d7470; font-size: 12px;
    }
    tr { margin-bottom: 12px; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #dcd3cc; }
}

/* --- Notification Styles (Dynamic Notification) --- */
#notification {
    position: fixed;
    top: 20px;
    right: 20px;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    min-width: 300px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.4s ease;
}

#notification.hidden {
    display: none;
    opacity: 0;
    transform: translateX(100%);
}

#notification.success {
    background: linear-gradient(135deg, #28a745, #20c997); /* Green/Success */
}

#notification.error {
    background: linear-gradient(135deg, #dc3545, #fd7e14); /* Red/Error */
}

#notification .close-btn {
    position: absolute;
    top: 5px;
    right: 10px;
    cursor: pointer;
    font-weight: bold;
    font-size: 18px;
    color: white;
    opacity: 0.8;
}

.notification-content {
    flex-grow: 1;
}

.notification-content #notification-title {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 2px;
}

.notification-content #notification-message {
    font-size: 12px;
    opacity: 0.9;
}
/* --- End Notification Styles --- */
</style>
</head>
<body>

<div class="tab-container">
    <div class="tab active" onclick="showTab('vendorFile')">Vendor File</div>
    <div class="tab" onclick="showTab('duplicateFind')">Duplicate Find</div>
</div>

<div id="vendorFile" class="tab-content active">
<div class="card-panel">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>SNo</th><th>SAP ID</th><th>GIS ID</th><th>Employee Name</th>
                    <th>S-Country</th><th>D-Country</th><th>SPF</th><th>SPFT</th>
                    <th>GOV</th><th>VFS</th><th>Misc</th><th>Tax</th><th>TIA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($vendor_data as $row): ?>
                <tr>
                    <td data-label="SNo"><?= htmlspecialchars($row['SNo']) ?></td>
                    <td data-label="SAP ID"><?= htmlspecialchars($row['SAP_ID']) ?></td>
                    <td data-label="GIS ID"><?= htmlspecialchars($row['GIS_ID']) ?></td>
                    <td data-label="Employee Name"><?= htmlspecialchars($row['Employee_Name']) ?></td>
                    <td data-label="S-Country"><?= htmlspecialchars($row['Source_Country']) ?></td>
                    <td data-label="D-Country"><?= htmlspecialchars($row['Destination_Country']) ?></td>
                    <td data-label="SPF"><?= htmlspecialchars($row['Service_Provider_Fee']) ?></td>
                    <td data-label="SPFT"><?= htmlspecialchars($row['Service_Provider_Fee_Tax']) ?></td>
                    <td data-label="GOV"><?= htmlspecialchars($row['Govt_Fee']) ?></td>
                    <td data-label="VFS"><?= htmlspecialchars($row['VFS_Fee']) ?></td>
                    <td data-label="Misc"><?= htmlspecialchars($row['Misc_Other_Exp']) ?></td>
                    <td data-label="Tax"><?= htmlspecialchars($row['Tax']) ?></td>
                    <td data-label="TIA"><?= htmlspecialchars($row['Total_Invoice_Amount']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="duplicateFind" class="tab-content">
<div class="card-panel">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <h2>Duplicate Find</h2>
        <button onclick="window.location.href='run_duplicate_check.php'">Execute Duplicate Check</button>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>SNo</th><th>SAP ID</th><th>GIS ID</th><th>Employee Name</th>
                    <th>S-Country</th><th>D-Country</th><th>SPF</th><th>SPFT</th>
                    <th>GOV</th><th>VFS</th><th>Misc</th><th>Tax</th><th>TIA</th>
                    <th>VF-DUP</th><th>Duplicate_Status</th><th>Source</th><th>User Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($duplicate_data as $row): ?>
            <tr
                data-dup-sg="<?= htmlspecialchars($row['DUP_SG_Status'] ?? '') ?>"
                data-dup-sglt="<?= htmlspecialchars($row['DUP_SGLT_Status'] ?? '') ?>"
                data-dup-sgg="<?= htmlspecialchars($row['DUP_SGG_Status'] ?? '') ?>"
                data-dup-sgv="<?= htmlspecialchars($row['DUP_SGV_Status'] ?? '') ?>"
                data-dup-sgm="<?= htmlspecialchars($row['DUP_SGM_Status'] ?? '') ?>"
                data-dup-sgt="<?= htmlspecialchars($row['DUP_SGT_Status'] ?? '') ?>"
                data-dup-sgta="<?= htmlspecialchars($row['DUP_SGTA_Status'] ?? '') ?>"
                data-dup-true-duplicate="<?= htmlspecialchars($row['DUP_True_Duplicate'] ?? '') ?>"
            >
                <td data-label="SNo"><?= htmlspecialchars($row['SNo'] ?? '-') ?></td>
                <td data-label="SAP ID"><?= htmlspecialchars($row['SAP_ID'] ?? '-') ?></td>
                <td data-label="GIS ID"><?= htmlspecialchars($row['GIS_ID'] ?? '-') ?></td>
                <td data-label="Employee Name"><?= htmlspecialchars($row['Employee_Name'] ?? '-') ?></td>
                <td data-label="S-Country"><?= htmlspecialchars($row['Source_Country'] ?? '-') ?></td>
                <td data-label="D-Country"><?= htmlspecialchars($row['Destination_Country'] ?? '-') ?></td>
                <td data-label="SPF"><?= htmlspecialchars($row['Service_Provider_Fee'] ?? '-') ?></td>
                <td data-label="SPFT"><?= htmlspecialchars($row['Service_Provider_Fee_Tax'] ?? '-') ?></td>
                <td data-label="GOV"><?= htmlspecialchars($row['Govt_Fee'] ?? '-') ?></td>
                <td data-label="VFS"><?= htmlspecialchars($row['VFS_Fee'] ?? '-') ?></td>
                <td data-label="Misc"><?= htmlspecialchars($row['Misc_Other_Exp'] ?? '-') ?></td>
                <td data-label="Tax"><?= htmlspecialchars($row['Tax'] ?? '-') ?></td>
                <td data-label="TIA"><?= htmlspecialchars($row['Total_Invoice_Amount'] ?? '-') ?></td>
                <td data-label="VF-DUP"><?= htmlspecialchars($row['DUP_True_Duplicate'] ?? '-') ?></td>
                <td data-label="Duplicate_Status"><?= htmlspecialchars($row['Duplicate_Status'] ?? '-') ?></td>
                <td data-label="Source"><?= htmlspecialchars($row['SourceTable'] ?? '-') ?></td>
                <td data-label="User Status">
                    <?php if ($row['SourceTable'] === 'Vend'): ?>
                        <select onchange="updateUserStatus(this.value, '<?= htmlspecialchars($row['SAP_ID']) ?>')">
                            <option value="Unique" <?= ($row['User_Status'] === 'Unique') ? 'selected' : '' ?>>Unique</option>
                            <option value="Duplicate" <?= ($row['User_Status'] === 'Duplicate') ? 'selected' : '' ?>>Duplicate</option>
                            <option value="Refer Back" <?= ($row['User_Status'] === 'Refer Back') ? 'selected' : '' ?>>Refer Back</option>
                            <option value="Rejected" <?= ($row['User_Status'] === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    <?php else: ?>
                        <em>-</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<div id="notification" class="hidden">
    <span class="close-btn" onclick="hideNotification()">&times;</span>
    <div class="notification-content">
        <div id="notification-title"></div>
        <div id="notification-message"></div>
    </div>
</div>


<script>
// Update User Status via AJAX
function updateUserStatus(status, sapId){
    if(!status || !sapId) return;
    const formData = new FormData();
    formData.append('action','update_status');
    formData.append('status',status);
    formData.append('sapId',sapId);

    fetch('',{method:'POST',body:formData})
    .then(resp=>resp.json()) // Change to .json() since the server returns JSON
    .then(data=>{
        if(data.success) {
            console.log("Status updated successfully.");
            // Optional: show a notification for AJAX status update success too
        } else {
            console.error("Status update failed:", data.message);
        }
    })
    .catch(err=>console.error(err));
}

// Tab switching functionality
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    
    // Activate the content
    document.getElementById(tabId).classList.add('active');
    
    // Activate the corresponding button
    const tabButton = document.querySelector(`.tab[onclick="showTab('${tabId}')"]`);
    if(tabButton) {
        tabButton.classList.add('active');
    }
}

// Highlight duplicate cells on page load and handle URL redirects
document.addEventListener('DOMContentLoaded', () => {
    
    // --- Highlighting Logic ---
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
            // Check the 1-based index based on table structure
            const cell = row.cells[item.index + 0]; 
            if (dataAttribute === "1" && cell) {
                cell.classList.add("highlight-dup");
            }
        });

        // Highlight the Duplicate Status column (Index 13)
        const duplicateStatusCell = row.cells[13]; 
        if (duplicateStatusCell && row.dataset.dupTrueDuplicate === "1") {
            duplicateStatusCell.classList.add("highlight-dup");
        }
    });

    // --- Notification and Tab Redirect Logic (THE FIX) ---
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status === 'success') {
        // 1. Show the success notification
        showNotification();
        
        // 2. ✅ FIX: Automatically switch to the Duplicate Find Tab
        showTab('duplicate Find'); 
        
        // 3. Clean the URL to remove the parameter after execution
        history.replaceState(null, '', window.location.pathname);
    } else if (status === 'error') {
        const msg = urlParams.get('msg') ? decodeURIComponent(urlParams.get('msg')) : 'An unknown error occurred.';
        // You'd need an error notification function/style to handle this gracefully
        console.error("Duplicate Check Failed:", msg);
        // Clean the URL
        history.replaceState(null, '', window.location.pathname);
    }
});

function showNotification() {
    const notification = document.getElementById('notification');
    const timeSpan = document.getElementById('notification-time');
    const now = new Date();
    timeSpan.textContent = 'Time: ' + now.toLocaleTimeString();
    notification.style.display = 'block';
    // Ensure the notification is visible immediately
    notification.style.opacity = 1; 
    setTimeout(hideNotification, 3000);
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.style.display = 'none';
}
</script>
</body>
</html>