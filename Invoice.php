<?php
// Invoice.php
include 'db_connection.php'; // <-- make sure you have $conn here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Consolidated Entry</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #f5f7fa, #e4ebf5);
            padding: 10px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            color: #2c3e50;
        }
        form {
            background: #fff;
            padding: 25px 20px 20px;  
            border-radius: 12px;
            max-width: 1500px;
            margin: auto;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;  /* extra space between inputs */
        }

        form:hover {
            transform: translateY(-3px);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }
        label {
            font-weight: 600;
            font-size: 14px;
            color: #444;
            margin-bottom: 4px;
            display: block;
        }
        input, select, textarea {
            width: 95%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            background: #fafafa;
            transition: all 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #007bff;
            outline: none;
            background: #fff;
            box-shadow: 0 0 6px rgba(0,123,255,0.3);
        }
        .full-width {
            grid-column: span 3;
        }
        button {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-search {
            background: linear-gradient(135deg, #007bff, #0056d2);
            color: white;
        }
        .btn-search:hover {
            background: linear-gradient(135deg, #0056d2, #003d99);
            transform: translateY(-2px);
        }
        .btn-clear {
            background: linear-gradient(135deg, #dc3545, #a71d2a);
            color: white;
            margin-left: 10px;
        }
        .btn-clear:hover {
            background: linear-gradient(135deg, #a71d2a, #7a131f);
            transform: translateY(-2px);
        }
        /* Search Section */
        .search-box {
            max-width: 1500px;
            margin: auto;
            background: #fff;
            padding: 25px 20px 20px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            margin-bottom: 22px;
            position: sticky;
            top: 10px;
            z-index: 10;
        }
        .search-box label {
            font-size: 15px;
            font-weight: 600;
        }
        .search-box input, .search-box select {
            margin-bottom: 10px;
        }
    </style>
    <script>
        function fetchGISList() {
            let sapId = document.getElementById("search_sap_id").value;
            if (!sapId) {
                alert("Please enter SAP ID");
                return;
            }
            fetch("fetch_invoice.php?type=gis_list&sap_id=" + sapId)
                .then(res => res.json())
                .then(data => {
                    let gisSelect = document.getElementById("search_gis_id");
                    gisSelect.innerHTML = "<option value=''>-- Select GIS ID --</option>";
                    data.forEach(row => {
                        let opt = document.createElement("option");
                        opt.value = row.GIS_ID;
                        opt.textContent = row.GIS_ID;
                        gisSelect.appendChild(opt);
                    });
                });
        }

        function fetchInvoiceDetails() {
            let sapId = document.getElementById("search_sap_id").value;
            let gisId = document.getElementById("search_gis_id").value;
            if (!gisId) {
                alert("Please select GIS ID");
                return;
            }
            fetch("fetch_invoice.php?type=invoice&sap_id=" + sapId + "&gis_id=" + gisId)
                .then(res => res.json())
                .then(data => {
                    if (data) {
                        Object.keys(data).forEach(key => {
                            if (document.getElementsByName(key)[0]) {
                                document.getElementsByName(key)[0].value = data[key];
                            }
                        });
                    }
                });
        }

        function clearSearch() {
            document.getElementById("search_sap_id").value = "";
            document.getElementById("search_gis_id").innerHTML = "<option value=''>-- Select GIS ID --</option>";
            document.querySelectorAll("input, textarea").forEach(el => el.value = "");
        }
    </script>
</head>
<body>

<h2>Invoice Consolidated Entry</h2>

<!-- Search Section -->
<div class="search-box">
    <label for="search_sap_id">SAP ID:</label>
    <input type="text" id="search_sap_id" style="width:200px; margin-right:10px;">
    
    <button class="btn-search" type="button" onclick="fetchGISList()">Search</button>
    <button class="btn-clear" type="button" onclick="clearSearch()">Clear</button>
    <br><br>

    <label for="search_gis_id">GIS ID:</label>
    <select id="search_gis_id" style="width:200px;" onchange="fetchInvoiceDetails()">
        <option value="">-- Select GIS ID --</option>
    </select>
</div>

<!-- Main Invoice Form -->
<form method="post" action="save_invoice.php">
    <div class="form-grid">
        <div><label>SAP ID</label><input type="text" name="SAP_ID"></div>
        <div><label>GIS ID</label><input type="text" name="GIS_ID"></div>
        <div><label>Employee Name</label><input type="text" name="Employee_Name"></div>
        <div><label>Case Manager Name</label><input type="text" name="Case_Manager_Name"></div>
        <div><label>Type of Request</label><input type="text" name="Type_of_Request"></div>
        <div><label>Visa Type</label><input type="text" name="Visa_Type"></div>
        <div><label>Visa Sub Type</label><input type="text" name="Visa_Sub_Type"></div>
        <div><label>Source Country</label><input type="text" name="Source_Country"></div>
        <div><label>Destination Country</label><input type="text" name="Destination_Country"></div>
        <div><label>Region</label><input type="text" name="Region"></div>
        <div><label>GEO Leads Approver Name</label><input type="text" name="GEO_Leads_Approver_Name"></div>
        <div><label>Date Sent to GEO Lead</label><input type="date" name="Date_Sent_to_GEO_Lead"></div>
        <div><label>Date Approved by GEO Lead</label><input type="date" name="Date_Approved_by_GEO_Lead"></div>
        <div><label>Date Sent to Mail Room</label><input type="date" name="Date_Sent_to_Mail_Room"></div>
        <div><label>Neon Introduction Date</label><input type="date" name="Neon_Introduction_Date"></div>
        <div><label>Date Sent to Approver in Neon</label><input type="date" name="Date_Sent_to_Approver_in_Neon"></div>
        <div><label>Date Approved by L1 L4 in Neon</label><input type="date" name="Date_Approved_by_L1_L4_in_Neon"></div>
        <div><label>Finance Approved Date</label><input type="date" name="Finance_Approved_Date"></div>
        <div><label>Payment Processed Date</label><input type="date" name="Payment_Processed_Date"></div>
        <div><label>Toscana ID Ariba</label><input type="text" name="Toscana_ID_Ariba"></div>
        <div><label>Invoice Number</label><input type="text" name="Invoice_Number"></div>
        <div><label>Individual Invoice Number</label><input type="text" name="Individual_Invoice_Number"></div>
        <div><label>Invoice Date</label><input type="date" name="Invoice_Date"></div>
        <div><label>Invoice Received Date at HCL</label><input type="date" name="Invoice_Received_Date_at_HCL"></div>
        <div><label>Invoice Due Date</label><input type="date" name="Invoice_Due_Date"></div>
        <div><label>Service Description</label><input type="text" name="Service_Description"></div>
        <div><label>Category</label><input type="text" name="Category"></div>
        <div><label>Service Rendered Month</label><input type="text" name="Service_Rendered_Month"></div>
        <div><label>Vendor Code</label><input type="text" name="Vendor_Code"></div>
        <div><label>Vendor Name</label><input type="text" name="Vendor_Name"></div>
        <div><label>Company Code</label><input type="text" name="Company_Code"></div>
        <div><label>Service Provider Fee</label><input type="number" step="0.01" name="Service_Provider_Fee"></div>
        <div><label>Service Provider Fee Tax</label><input type="number" step="0.01" name="Service_Provider_Fee_Tax"></div>
        <div><label>Service Tax Total</label><input type="number" step="0.01" name="Service_Tax_Total"></div>
        <div><label>Govt Fee</label><input type="number" step="0.01" name="Govt_Fee"></div>
        <div><label>VFS Fee</label><input type="number" step="0.01" name="VFS_Fee"></div>
        <div><label>Misc Other Exp</label><input type="number" step="0.01" name="Misc_Other_Exp"></div>
        <div><label>Tax</label><input type="number" step="0.01" name="Tax"></div>
        <div><label>Total Invoice Amount</label><input type="number" step="0.01" name="Total_Invoice_Amount"></div>
        <div><label>Currency</label><input type="text" name="Currency"></div>
        <div><label>Cost Center</label><input type="text" name="Cost_Center"></div>
        <div><label>Mirror Cost Center</label><input type="text" name="Mirror_Cost_Center"></div>
        <div><label>Project Code</label><input type="text" name="Project_Code"></div>
        <div><label>GSTN</label><input type="text" name="GSTN"></div>
        <div><label>Currency Exchange Rate</label><input type="number" step="0.0001" name="Currency_Exchange_Rate"></div>
        <div><label>Document Currency in USD</label><input type="number" step="0.01" name="Document_Currency_in_USD"></div>
        <div><label>LOB</label><input type="text" name="LOB"></div>
        <div><label>Invoice Current Status</label><input type="text" name="Invoice_Current_Status"></div>
        <div class="full-width"><label>Remarks</label><textarea name="Remarks" rows="3"></textarea></div>
    </div>
    <div style="text-align:center; margin-top:20px;">
        <button type="submit" class="btn-search">💾 Save Invoice</button>
    </div>
</form>

</body>
</html>
