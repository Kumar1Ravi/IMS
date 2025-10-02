<?php
include 'db_connection.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';

if ($type == "gis_list") {
    $sap_id = $_GET['sap_id'];
    $sql = "SELECT DISTINCT GIS_ID FROM Invoice_Consolidated WHERE SAP_ID = ?";
    $stmt = sqlsrv_query($conn, $sql, [$sap_id]);
    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode($rows);
}
elseif ($type == "invoice") {
    $sap_id = $_GET['sap_id'];
    $gis_id = $_GET['gis_id'];

    $sql = "SELECT 
                SAP_ID,
                GIS_ID,
                Employee_Name,
                Case_Manager_Name,
                Type_of_Request,
                Visa_Type,
                Visa_Sub_Type,
                Source_Country,
                Destination_Country,
                Region,
                GEO_Leads_Approver_Name,
                CONVERT(VARCHAR(10), Date_Sent_to_GEO_Lead, 23) AS Date_Sent_to_GEO_Lead,
                CONVERT(VARCHAR(10), Date_Approved_by_GEO_Lead, 23) AS Date_Approved_by_GEO_Lead,
                CONVERT(VARCHAR(10), Date_Sent_to_Mail_Room, 23) AS Date_Sent_to_Mail_Room,
                CONVERT(VARCHAR(10), Neon_Introduction_Date, 23) AS Neon_Introduction_Date,
                CONVERT(VARCHAR(10), Date_Sent_to_Approver_in_Neon, 23) AS Date_Sent_to_Approver_in_Neon,
                CONVERT(VARCHAR(10), Date_Approved_by_L1_L4_in_Neon, 23) AS Date_Approved_by_L1_L4_in_Neon,
                CONVERT(VARCHAR(10), Finance_Approved_Date, 23) AS Finance_Approved_Date,
                CONVERT(VARCHAR(10), Payment_Processed_Date, 23) AS Payment_Processed_Date,
                Toscana_ID_Ariba,
                Invoice_Number,
                Individual_Invoice_Number,
                CONVERT(VARCHAR(10), Invoice_Date, 23) AS Invoice_Date,
                CONVERT(VARCHAR(10), Invoice_Received_Date_at_HCL, 23) AS Invoice_Received_Date_at_HCL,
                CONVERT(VARCHAR(10), Invoice_Due_Date, 23) AS Invoice_Due_Date,
                Service_Description,
                Category,
                Service_Rendered_Month,
                Vendor_Code,
                Vendor_Name,
                Company_Code,
                Service_Provider_Fee,
                Service_Provider_Fee_Tax,
                Service_Tax_Total,
                Govt_Fee,
                VFS_Fee,
                Misc_Other_Exp,
                Tax,
                Total_Invoice_Amount,
                Currency,
                Cost_Center,
                Mirror_Cost_Center,
                Project_Code,
                GSTN,
                Currency_Exchange_Rate,
                Document_Currency_in_USD,
                LOB,
                Invoice_Current_Status,
                Remarks
            FROM Invoice_Consolidated
            WHERE SAP_ID = ? AND GIS_ID = ?";

    $stmt = sqlsrv_query($conn, $sql, [$sap_id, $gis_id]);
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    echo json_encode($row);
}
?>
