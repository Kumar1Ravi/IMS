<?php
// Include DB connection
include 'db_connection.php';

// Verify connection
if (!$conn) {
    die("❌ DB connection is not valid. Check db_connection.php!");
}

// Fetch all Vendor_Invoice rows with only selected columns
$sql = "SELECT 
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
        ORDER BY SNo DESC";

$result = sqlsrv_query($conn, $sql);

if ($result === false) {
    die("❌ Query failed: " . print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Invoice Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>📊 Vendor Invoice Dashboard</h2>

    <!-- File upload form -->
    <form action="upload_vendor.php" method="post" enctype="multipart/form-data">
        Select Vendor CSV File:
        <input type="file" name="vendor_file" required>
        <input type="submit" value="Upload">
    </form>

    <br>

    <table>
        <thead>
            <tr>
                <th>SNo</th>
                <th>SAP_ID</th>
                <th>GIS_ID</th>
                <th>Employee_Name</th>
                <th>Source_Country</th>
                <th>Destination_Country</th>
                <th>Service_Provider_Fee</th>
                <th>Service_Provider_Fee_Tax</th>
                <th>Govt_Fee</th>
                <th>VFS_Fee</th>
                <th>Misc_Other_Exp</th>
                <th>Tax</th>
                <th>Total_Invoice_Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                echo "<tr>";
                foreach ($row as $key => $value) {
                    if ($value instanceof DateTime) {
                        $value = $value->format('Y-m-d'); // Convert DateTime to string
                    }
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
