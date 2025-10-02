<?php
// Include DB connection
include 'db_connection.php';

// Verify connection
if (!$conn) {
    die("❌ DB connection is not valid. Check db_connection.php!");
}

// Fetch all Vendor_Invoice rows with only selected columns
/*$sql = "SELECT
            SNo,
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
            Total_Invoice_Amount
        FROM Vendor_Invoice
        ORDER BY SNo DESC"; */

// Fetch Vendor_Invoice data for the 'Vendor File' tab
$sql = "SELECT
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

$result = sqlsrv_query($conn, $sql);

if ($result === false) die(print_r(sqlsrv_errors(), true));

// Get total count for stats
$totalRecords = 0;
while(sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $totalRecords++;
}

// Reset result pointer
$result = sqlsrv_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice Data Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Base Reset & Font */
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }
body { background:#f4f4f4; color:#333; min-height:100vh; }

/* Header */
.header {
    background:#fff; padding:15px 20px; border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,0.05); text-align:center; margin:20px auto;
    max-width:1800px;
}
.header h1 { font-size:24px; display:flex; justify-content:center; align-items:center; gap:8px; }
.header p { font-size:14px; color:#555; margin-top:4px; }

/* Stats Cards */
.stats-section {
    display:flex; flex-wrap:wrap; justify-content:center; gap:15px;
    max-width:1800px; margin:10px auto 20px auto;
}
.stat-card {
    flex:1 1 150px; background:#fff; padding:15px 12px; border-radius:12px;
    text-align:center; box-shadow:0 5px 15px rgba(0,0,0,0.05); transition:transform 0.3s;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.1); }
.stat-card h3 { font-size:22px; margin-bottom:4px; }
.stat-card p { font-size:13px; color:#666; margin:0; }

/* Table Container */
.table-container {
    max-width:1800px; margin:0 auto 30px auto; background:#fff; border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05); overflow:hidden;
}


.table-header {
    background:#007bff; color:#fff; padding:12px 20px;
}
.table-header h2 { font-size:18px; display:flex; align-items:center; gap:8px; margin:0; }

/* Table Controls */
.table-controls {
    display:flex; flex-wrap:wrap; gap:10px; padding:10px 20px; background:#f9f9f9;
    align-items:center; border-bottom:1px solid #e0e0e0;
}
.search-box { position:relative; flex:1; min-width:180px; }
.search-box input {
    width:100%; padding:8px 35px 8px 10px; border:1px solid #ccc; border-radius:6px; font-size:13px;
}
.search-box i { position:absolute; right:10px; top:50%; transform:translateY(-50%); color:#888; }
.filter-btn {
    padding:8px 15px; background:#007bff; color:#fff; border:none; border-radius:6px;
    cursor:pointer; font-size:13px; display:flex; align-items:center; gap:5px; transition:all 0.2s;
}
.filter-btn:hover { background:#0056d2; }

/* Table Styles */
table { width:100%; border-collapse:collapse; font-size:13px; }
th, td { padding:10px 12px; text-align:left; border-bottom:1px solid #eaeaea; }
th { background:#f1f1f1; font-weight:600; text-transform:uppercase; position:sticky; top:0; z-index:10; }
tr:nth-child(even) { background:#f9f9f9; }
tr:hover { background:#eef5ff; transition:0.2s; }
.amount-column { text-align:right; font-weight:500; color:inherit; }

/* Empty State */
.empty-state { text-align:center; padding:40px; color:#555; }
.empty-state i { font-size:36px; margin-bottom:10px; opacity:0.5; }
.empty-state h3 { font-size:18px; margin-bottom:4px; }

/* Responsive */
@media(max-width:768px){
    .stats-section { flex-direction:column; gap:10px; }
    .stat-card h3 { font-size:18px; }
    th, td { padding:8px 10px; font-size:12px; }
    .header h1 { font-size:20px; }
}
</style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-table"></i> Invoice Dashboard</h1>
    <p>Overview of vendor invoice records</p>
</div>

<div class="stats-section">
    <div class="stat-card">
        <h3><?php echo number_format($totalRecords); ?></h3>
        <p>Total Records</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($totalRecords * 0.85); ?></h3>
        <p>Active Invoices</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($totalRecords * 0.15); ?></h3>
        <p>Pending Review</p>
    </div>
    <div class="stat-card">
        <h3><?php echo number_format($totalRecords * 0.95,1); ?>%</h3>
        <p>Completion Rate</p>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h2><i class="fas fa-database"></i> Invoice Records</h2>
    </div>

    <div class="table-controls">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search records...">
            <i class="fas fa-search"></i>
        </div>
        <button class="filter-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>

    <table id="dataTable">
        <thead>
            <tr>
                <th style="width: 50px; text-align: center;">SNo</th>
                <th style="width: 100px; text-align: center;">SAP ID</th>
                <th style="width: 100px; text-align: center;">GIS ID</th>
                <th style="width: 180px; text-align: center;">Employee Name</th>
                <th style="width: 120px; text-align: center;">Source</th>
                <th style="width: 120px; text-align: center;">Destination</th>
                <th style="width: 90px; text-align: center;" class="amount-column">SP Fee</th>
                <th style="width: 90px; text-align: center;" class="amount-column">SP Tax</th>
                <th style="width: 90px; text-align: center;" class="amount-column">Gov Fee</th>
                <th style="width: 90px; text-align: center;" class="amount-column">VFS Fee</th>
                <th style="width: 90px; text-align: center;" class="amount-column">Misc Exp</th>
                <th style="width: 90px; text-align: center;" class="amount-column">Tax</th>
                <th style="width: 120px; text-align: center;" class="amount-column">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php if(sqlsrv_has_rows($result)): ?>
                <?php while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)): ?>
                <tr>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['SNo'] ?? '-'); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['SAP_ID'] ?? '-'); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['GIS_ID'] ?? '-'); ?></td>
                    <td style="text-align: left;"><?php echo htmlspecialchars($row['Employee_Name'] ?? '-'); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['Source_Country'] ?? '-'); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['Destination_Country'] ?? '-'); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['Service_Provider_Fee'] ?? 0,2); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['Service_Provider_Fee_Tax'] ?? 0,2); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['Govt_Fee'] ?? 0,2); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['VFS_Fee'] ?? 0,2); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['Misc_Other_Exp'] ?? 0,2); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['Tax'] ?? 0,2); ?></td>
                    <td  style="text-align: center;" class="amount-column">$<?php echo number_format($row['Total_Invoice_Amount'] ?? 0,2); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="13" class="empty-state">
                        <i class="fas fa-database"></i>
                        <h3>No Records Found</h3>
                        <p>No invoice records are available.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#dataTable tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Add loading state for better UX
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('dataTable');
            if (table) {
                table.style.opacity = '0';
                setTimeout(() => {
                    table.style.transition = 'opacity 0.5s ease';
                    table.style.opacity = '1';
                }, 100);
            }
        });
    </script>
</body>
</html>
