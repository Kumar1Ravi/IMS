<?php
// index.php

include 'db_connection.php'; // $conn from SQL Server connection

// Handle POST request for status update via AJAX
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
        echo json_encode(["success" => false, "message" => "Error: Could not update the status.", "details" => sqlsrv_errors()]);
    } else {
        echo json_encode(["success" => true, "message" => "Status updated successfully."]);
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    exit;
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

$result_vendor = sqlsrv_query($conn, $sql_vendor);
if ($result_vendor === false) die(print_r(sqlsrv_errors(), true));

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

$result_duplicate = sqlsrv_query($conn, $sql_duplicate);
if ($result_duplicate === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vendor Analysis</title>
<style>
body {
    font-family: 'Segoe UI', 'Arial', sans-serif;
    background: linear-gradient(120deg, #d9afd9 0%, #97d9e1 100%);
    margin: 0;
    min-height: 100vh;
    padding: 1px 0;
}

.tab-container {
    display: flex;
    border-bottom: none;
    margin-bottom: 0;
    justify-content: left;
    padding: 16px 32px;
    margin: 0 5px -24px 0;
}

.tab {
    padding: 16px 32px;
    margin: 0 5px -22px 0;
    cursor: pointer;
    font-size: 17px;
    font-weight: 500;
    border: none;
    border-radius: 20px 20px 0 0;
    background: #fff;
    transition: background 0.15s, color 0.15s;
    box-shadow: 0 2px 10px -3px rgba(70,70,70,0.12);
    color: #2b2d42;
}

.tab.active {
    background: linear-gradient(120deg, #ffecd2 0%, #fcb69f 100%);
    color: #503c2b;
    font-weight: bold;
    box-shadow: 0 8px 32px -16px #b48d67;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    margin-top: -8px;
    animation: fadeInTab 0.4s;
}

@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(25px);}
    to { opacity: 1; transform: translateY(0);}
}

.card-panel {
    max-width: 95vw;
    margin: 30px auto;
    border-radius: 21px;
    box-shadow: 0 4px 32px 0 rgba(70,70,70,0.09);
    background: #fff;
    padding: 35px 30px;
}

h2 {
    margin: 14px 0 24px 0;
    color: #4b3c2d;
    letter-spacing: 2px;
    text-shadow: 0 2px 8px #ebd6bf5e;
    font-weight: 700;
}

button {
    padding: 10px 22px;
    background: linear-gradient(90deg, #ffecd2, #fcb69f);
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    color: #3b2d1e;
    box-shadow: 0 2px 8px 0 #e4c7aa55;
    cursor: pointer;
    transition: background 0.18s, transform 0.12s;
    outline: none;
    margin-left: 8px;
}

button:hover {
    background: linear-gradient(90deg, #fcb69f, #ffecd2);
    transform: translateY(-2px) scale(1.03);
}

.table-wrapper {
    overflow-x: auto;
    padding: 0 2vw 24px 2vw;
    margin-bottom: 24px;
}

table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: 1100px;
    background: #ffffffed;
    border-radius: 16px;
    box-shadow: 0 2px 28px -2px #7aa2b790;
    overflow: hidden;
}

th, td {
    font-size: 14px;
    padding: 13px 11px;
    text-align: center;
}

th {
    background: linear-gradient(95deg, #ffecd2 55%, #ffecd2 55%);
    font-weight: 600;
    color: #3b2d1e;
    border-bottom: 3px solid #f8bfa0; 
    letter-spacing: 1px;
}

td {
    border-bottom: 1px solid #ede2dd;
    color: #2a3843;
    background: #fffaf8e9;
    transition: background 0.18s;
}

tr:hover td {
    background: #4a8596b2 !important;
}

select {
    padding: 6px 12px;
    border-radius: 8px;
    border: 1px solid #dbc5b7;
    background: #f7eee6;
    color: #27313c;
    font-size: 13px;
    transition: border 0.15s;
    outline: none;
}

select:focus {
    border: 1.5px solid #f5a35f;
    background: #fff;
}

em {
    color: #c2b399;
}

.highlight-dup {
    background: #ff8fa3;
    color: #080202ff;
    font-weight: bold;
    border-radius: 1px;
    box-shadow: none;
}


@media (max-width: 1200px) {
    .card-panel { padding: 2vw; }
    h2 { font-size: 1.5rem; }
    th, td { font-size: 12px; }
    .tab { font-size: 1rem; padding: 11px 18px; }
}

@media (max-width:700px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }
    .table-wrapper { padding: 0; }
    th { text-align: left; }
    tr { margin-bottom: 15px; background: #fff; border-radius: 16px; box-shadow: 0 3px 20px #d9d3cc;}
    td { border: none; position: relative; padding-left: 42%; text-align: left;}
    td:before {
        position: absolute;
        top: 12px;
        left: 12px;
        width: 38vw;
        white-space: pre-wrap;
        font-weight: bold;
        content: attr(data-label);
        color: #7d7470;
        font-size: 13px;
    }
}
</style>
</head>
<body>
<div class="tab-container">
    <div class="tab active" onclick="showTab('vendorFile')">Vendor File</div>
    <div class="tab" onclick="showTab('duplicateFind')">Duplicate Find</div>
</div>

<div id="vendorFile" class="tab-content active">
    <div class="card-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h2>Vendor File</h2>
            <button onclick="window.location.href='run_duplicate_check.php'" style="padding: 8px 16px; font-size: 14px; cursor: pointer;">
                Execute Duplicate Check
            </button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>SNo</th>
                        <th>SAP ID</th>
                        <th>GIS ID</th>
                        <th>Employee Name</th>
                        <th>S-Country</th>
                        <th>D-Country</th>
                        <th>SPF</th>
                        <th>SPFT</th>
                        <th>GOV</th>
                        <th>VFS</th>
                        <th>Misc</th>
                        <th>Tax</th>
                        <th>TIA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = sqlsrv_fetch_array($result_vendor, SQLSRV_FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['SNo']) ?></td>
                        <td><?= htmlspecialchars($row['SAP_ID']) ?></td>
                        <td><?= htmlspecialchars($row['GIS_ID']) ?></td>
                        <td><?= htmlspecialchars($row['Employee_Name']) ?></td>
                        <td><?= htmlspecialchars($row['Source_Country']) ?></td>
                        <td><?= htmlspecialchars($row['Destination_Country']) ?></td>
                        <td><?= htmlspecialchars($row['Service_Provider_Fee']) ?></td>
                        <td><?= htmlspecialchars($row['Service_Provider_Fee_Tax']) ?></td>
                        <td><?= htmlspecialchars($row['Govt_Fee']) ?></td>
                        <td><?= htmlspecialchars($row['VFS_Fee']) ?></td>
                        <td><?= htmlspecialchars($row['Misc_Other_Exp']) ?></td>
                        <td><?= htmlspecialchars($row['Tax']) ?></td>
                        <td><?= htmlspecialchars($row['Total_Invoice_Amount']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="duplicateFind" class="tab-content">
    <div class="card-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h2>Duplicate Find</h2>
            <button onclick="window.location.href='run_duplicate_check.php'" style="padding: 8px 16px; font-size: 14px; cursor: pointer;">
                Execute Duplicate Check
            </button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>SNo</th>
                        <th>SAP ID</th>
                        <th>GIS ID</th>
                        <th>Employee Name</th>
                        <th>S-Country</th>
                        <th>D-Country</th>
                        <th>SPF</th>
                        <th>SPFT</th>
                        <th>GOV</th>
                        <th>VFS</th>
                        <th>Misc</th>
                        <th>Tax</th>
                        <th>TIA</th>
                        <th>VF-DUP</th>
                        <th>Duplicate_Status</th>
                        <th>Source</th>
                        <th>User Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = sqlsrv_fetch_array($result_duplicate, SQLSRV_FETCH_ASSOC)): ?>
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
                    
                    <td><?= htmlspecialchars($row['SNo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['SAP_ID'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['GIS_ID'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Employee_Name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Source_Country'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Destination_Country'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Service_Provider_Fee'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Service_Provider_Fee_Tax'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Govt_Fee'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['VFS_Fee'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Misc_Other_Exp'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Tax'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Total_Invoice_Amount'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['DUP_True_Duplicate'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['Duplicate_Status'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['SourceTable'] ?? '-') ?></td>
                    <td>
                        <?php if ($row['SourceTable'] === 'Vend'): ?>
                            <select onchange="updateUserStatus(this.value, '<?= htmlspecialchars($row['SAP_ID']) ?>')">
                                <option value="Unique" <?= ($row['User_Status'] === 'Unique') ? 'selected' : '' ?>> </option>
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
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

// Update User Status via AJAX
function updateUserStatus(status, sapId){
    if(!status||!sapId) return;
    const formData = new FormData();
    formData.append('action','update_status');
    formData.append('status',status);
    formData.append('sapId',sapId);

    fetch('',{method:'POST',body:formData})
    .then(resp=>resp.text())
    .then(data=>console.log(data))
    .catch(err=>console.error(err));
}

// Highlight duplicate cells on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll("#duplicateFind tbody tr").forEach(row => {
        // The mapping of data attributes to table column indices
        const mapping = [
            { attr: 'dupSg', index: 6 },
            { attr: 'dupSglt', index: 7 },
            { attr: 'dupSgg', index: 8 },
            { attr: 'dupSgv', index: 9 },
            { attr: 'dupSgm', index: 10 },
            { attr: 'dupSgt', index: 11 },
            { attr: 'dupSgta', index: 12 },
            { attr: 'dupTrueDuplicate', index: 13 }
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
    });
});
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>
