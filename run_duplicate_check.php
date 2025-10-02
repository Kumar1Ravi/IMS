<?php
include 'db_connection.php'; // $conn from SQL Server

if ($conn === false) {
    // Redirect with an error status if connection fails
    header("Location: analysis.php?status=error");
    exit();
}

// List of stored procedures to execute
$procedures = [
    "EXEC dbo.sp_UpdateVendorInvoiceValidation",
    "EXEC dbo.sp_FindDuplicateVendorInvoice",
    "EXEC dbo.sp_UpdateDuplicateStatus"
];

$success = true;
foreach ($procedures as $proc) {
    $stmt = sqlsrv_query($conn, $proc);
    if ($stmt === false) {
        $success = false;
        // Redirect with an error status if a procedure fails
        header("Location: analysis.php?status=error&proc=" . urlencode($proc));
        exit();
    }
}

// Close connection
sqlsrv_close($conn);

// Redirect with a success parameter if all procedures executed
header("Location: analysis.php?status=success&tab=duplicateFind");
exit();
?>

