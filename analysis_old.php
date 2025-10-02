<?php
include 'db_connection.php'; // $conn from SQL Server connection

// Handle POST request for status update via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newStatus = $_POST['status'];
    $sapId = $_POST['sapId'];

    $sql = "UPDATE Vendor_Invoice SET User_Status = ? WHERE SAP_ID = ?";
    $params = array($newStatus, $sapId);

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo "Error: Could not update the status.";
    } else {
        echo "Status updated successfully.";
    }
    exit; // Stop further execution
}

// Fetch Vendor_Invoice data (selected columns)
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

// Fetch Vendor_Invoice data (for Duplicate Find)
$sql_duplicate = "SELECT 
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
    DUP_True_Duplicate,
    DUP_SG_Status,
    DUP_SGL_Status,
    DUP_SGLT_Status,
    DUP_SGG_Status,
    DUP_SGV_Status,
    DUP_SGM_Status,
    DUP_SGT_Status,
    DUP_SGTA_Status,
    Duplicate_Status,
    User_Status
FROM Vendor_Invoice
WHERE Duplicate_Status = 'DUP'";

$result_duplicate = sqlsrv_query($conn, $sql_duplicate);
if ($result_duplicate === false) die(print_r(sqlsrv_errors(), true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vendor Analysis</title>
<style>
body { font-family: Arial; margin: 20px; }
.tab-container { display: flex; border-bottom: 2px solid #ccc; margin-bottom: 15px; }
.tab { padding: 10px 20px; cursor: pointer; border: 1px solid #ccc; border-bottom: none; background-color: #f2f2f2; margin-right: 5px; }
.tab.active { background-color: #fff; font-weight: bold; }
.tab-content { display: none; }
.tab-content.active { display: block; }
table { border-collapse: collapse; width: 100%; table-layout: auto; font-size: 12px; }
th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; }
th { background-color: #f2f2f2; white-space: nowrap; }
.table-wrapper { overflow-x: auto; margin-bottom: 20px; }
button { padding: 6px 12px; font-size: 12px; cursor: pointer; }
.highlight-dup { background-color: #e06666; color: #fff; font-weight: bold; }
</style>
</head>
<body>

<div class="tab-container">
    <div class="tab active" onclick="showTab('vendorFile')">Vendor File</div>
    <div class="tab" onclick="showTab('duplicateFind')">Duplicate Find</div>
</div>

<!-- Vendor File Tab -->
<div id="vendorFile" class="tab-content active">
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
                    <td><?= $row['SNo'] ?></td>
                    <td><?= $row['SAP_ID'] ?></td>
                    <td><?= $row['GIS_ID'] ?></td>
                    <td><?= $row['Employee_Name'] ?></td>
                    <td><?= $row['Source_Country'] ?></td>
                    <td><?= $row['Destination_Country'] ?></td>
                    <td><?= $row['Service_Provider_Fee'] ?></td>
                    <td><?= $row['Service_Provider_Fee_Tax'] ?></td>
                    <td><?= $row['Govt_Fee'] ?></td>
                    <td><?= $row['VFS_Fee'] ?></td>
                    <td><?= $row['Misc_Other_Exp'] ?></td>
                    <td><?= $row['Tax'] ?></td>
                    <td><?= $row['Total_Invoice_Amount'] ?></td>

                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Duplicate Find Tab -->
<div id="duplicateFind" class="tab-content">
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
                    <th>User Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = sqlsrv_fetch_array($result_duplicate, SQLSRV_FETCH_ASSOC)): ?>
                <tr
                    data-dup-sg="<?= $row['DUP_SG_Status'] ?>"
                    data-dup-sglt="<?= $row['DUP_SGLT_Status'] ?>"
                    data-dup-sgg="<?= $row['DUP_SGG_Status'] ?>"
                    data-dup-sgv="<?= $row['DUP_SGV_Status'] ?>"
                    data-dup-sgm="<?= $row['DUP_SGM_Status'] ?>"
                    data-dup-sgt="<?= $row['DUP_SGT_Status'] ?>"
                    data-dup-sgta="<?= $row['DUP_SGTA_Status'] ?>"
                    data-dup-true="<?= $row['DUP_True_Duplicate'] ?>"
                    data-dup-status="<?= $row['Duplicate_Status'] ?>"
                >
                    <td><?= $row['SNo'] ?></td>
                    <td><?= $row['SAP_ID'] ?></td>
                    <td><?= $row['GIS_ID'] ?></td>
                    <td><?= $row['Employee_Name'] ?></td>
                    <td><?= $row['Source_Country'] ?></td>
                    <td><?= $row['Destination_Country'] ?></td>
                    <td><?= $row['Service_Provider_Fee'] ?></td>
                    <td><?= $row['Service_Provider_Fee_Tax'] ?></td>
                    <td><?= $row['Govt_Fee'] ?></td>
                    <td><?= $row['VFS_Fee'] ?></td>
                    <td><?= $row['Misc_Other_Exp'] ?></td>
                    <td><?= $row['Tax'] ?></td>
                    <td><?= $row['Total_Invoice_Amount'] ?></td>
                    <td><?= $row['DUP_True_Duplicate'] ?></td>
                    <td><?= $row['Duplicate_Status'] ?></td>
                    <td>
                        <select onchange="updateUserStatus(this.value, '<?= $row['SAP_ID'] ?>')">
                            <option value="Unique" <?= $row['User_Status']=='Unique'?'selected':'' ?>>Unique</option>
                            <option value="Duplicate" <?= $row['User_Status']=='Duplicate'?'selected':'' ?>>Duplicate</option>
                            <option value="Refer Back" <?= $row['User_Status']=='Refer Back'?'selected':'' ?>>Refer Back</option>
                            <option value="Rejected" <?= $row['User_Status']=='Rejected'?'selected':'' ?>>Rejected</option>
                        </select>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Highlight duplicates
        document.querySelectorAll("#duplicateFind tbody tr").forEach(row => {
            const cells = row.cells;
            if(row.dataset.dupSg==="1") cells[6].classList.add("highlight-dup");
            if(row.dataset.dupSglt==="1") cells[7].classList.add("highlight-dup");
            if(row.dataset.dupSgg==="1") cells[8].classList.add("highlight-dup");
            if(row.dataset.dupSgv==="1") cells[9].classList.add("highlight-dup");
            if(row.dataset.dupSgm==="1") cells[10].classList.add("highlight-dup");
            if(row.dataset.dupSgt==="1") cells[11].classList.add("highlight-dup");
            if(row.dataset.dupSgta==="1") cells[12].classList.add("highlight-dup");
            if(row.dataset.dupTrue==="1") cells[13].classList.add("highlight-dup");
            if(row.dataset.dupStatus==="DUP") cells[14].classList.add("highlight-dup");
        });
    </script>
</div>

<script>
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tc=>tc.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
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
</script>

</body>
</html>

<?php sqlsrv_close($conn); ?>
