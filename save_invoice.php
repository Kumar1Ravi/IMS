<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = array_keys($_POST);
    $values = array_values($_POST);

    // Check if record exists
    $checkSql = "SELECT COUNT(*) AS cnt FROM Invoice_Consolidated WHERE SAP_ID = ? AND GIS_ID = ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, [$_POST['SAP_ID'], $_POST['GIS_ID']]);
    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($row['cnt'] > 0) {
        // Update existing record
        $setClause = implode(", ", array_map(function($f){ return "$f = ?"; }, $fields));
        $sql = "UPDATE Invoice_Consolidated SET $setClause WHERE SAP_ID = ? AND GIS_ID = ?";
        $params = array_merge($values, [$_POST['SAP_ID'], $_POST['GIS_ID']]);
    } else {
        // Insert new record
        $colNames = implode(",", $fields);
        $placeholders = implode(",", array_fill(0, count($fields), "?"));
        $sql = "INSERT INTO Invoice_Consolidated ($colNames) VALUES ($placeholders)";
        $params = $values;
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo "<script>alert('Invoice saved successfully'); window.location.href='Invoice.php';</script>";
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}
?>
