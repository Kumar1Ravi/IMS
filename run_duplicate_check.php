<?php
include 'db_connection.php'; // $conn from SQL Server

if ($conn === false) {
    // Redirect on connection error
    header("Location: analysis.php?status=error"); 
    exit();
}

// List of stored procedures to execute
$procedures = [
    "EXEC dbo.sp_UpdateVendorInvoiceValidation",
    "EXEC dbo.sp_FindDuplicateVendorInvoice",
    "EXEC dbo.sp_UpdateDuplicateStatus"
];

foreach ($procedures as $proc) {
    $stmt = sqlsrv_query($conn, $proc);
    if ($stmt === false) {
        // Redirect on procedure error
        // Assuming your main page is 'analysis.php' or rename 'Temp.php'
        header("Location: analysis.php?status=error&proc=" . urlencode($proc));
        exit();
    }
}

sqlsrv_close($conn);

// ✅ SUCCESS REDIRECT: Redirect back to the main page with a 'status=success' parameter
header("Location: analysis.php?status=success"); 
exit();
?>