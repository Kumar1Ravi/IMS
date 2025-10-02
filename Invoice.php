<?php
// Invoice.php
include 'db_connection.php'; // <-- make sure you have $conn here
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Invoice - Invoice Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Modern Background */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333333;
            min-height: 100vh;
            position: relative;
        }

        /* Animated Background Elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(0, 123, 255, 0.2) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(10px) rotate(240deg); }
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 25px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(0, 123, 255, 0.1);
        }

        .header h1 {
            color: #007bff;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h1 i {
            color: #007bff;
            text-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        .header p {
            color: #666666;
            font-size: 16px;
            margin: 0;
        }

        /* Navigation */
        .nav-bar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .nav-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: 2px solid #007bff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover {
            background: linear-gradient(135deg, #007bff, #0056d2);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
        }

        .nav-links a i {
            font-size: 14px;
        }

        /* Main Container */
        .main-container {
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Enhanced Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .form-header {
            background: linear-gradient(135deg, #007bff, #0056d2);
            color: #ffffff;
            padding: 25px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .form-header h2 i {
            opacity: 0.9;
        }

        .form-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 16px;
        }

        /* Enhanced Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 30px;
        }

        .form-group {
            position: relative;
        }

        .form-group.full-width {
            grid-column: span 3;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333333;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 15px;
            background: #ffffff;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
            background: #f8f9ff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            transform: translateY(-2px);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Icon indicators for different input types */
        .form-group::after {
            content: '';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            opacity: 0.5;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .form-group:nth-child(1)::after { content: '👤'; } /* Employee fields */
        .form-group:nth-child(2)::after { content: '🆔'; } /* ID fields */
        .form-group:nth-child(3)::after { content: '📝'; } /* Name fields */
        .form-group:nth-child(4)::after { content: '📞'; } /* Contact fields */
        .form-group:nth-child(5)::after { content: '📋'; } /* Type fields */
        .form-group:nth-child(6)::after { content: '🛂'; } /* Visa fields */
        .form-group:nth-child(7)::after { content: '🌍'; } /* Country fields */
        .form-group:nth-child(8)::after { content: '🏢'; } /* Company fields */
        .form-group:nth-child(9)::after { content: '📊'; } /* Amount fields */
        .form-group:nth-child(10)::after { content: '💰'; } /* Finance fields */

        .form-group input:focus ~ ::after,
        .form-group select:focus ~ ::after,
        .form-group textarea:focus ~ ::after {
            opacity: 1;
        }

        /* Enhanced Search Section */
        .search-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 123, 255, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .search-section h3 {
            color: #007bff;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-section h3 i {
            text-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .search-form .form-group {
            margin-bottom: 0;
        }

        .search-buttons {
            display: flex;
            gap: 10px;
        }

        /* Enhanced Buttons */
        .btn {
            padding: 14px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056d2);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0056d2, #004085);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268, #495057);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }

        /* Form Actions */
        .form-actions {
            background: rgba(248, 249, 250, 0.8);
            padding: 25px 30px;
            border-top: 1px solid rgba(0, 123, 255, 0.1);
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        /* Enhanced Message */
        .message {
            margin: 20px 0;
            padding: 15px 20px;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
            font-size: 14px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .message.success {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border-color: rgba(40, 167, 69, 0.3);
        }

        .message.error {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border-color: rgba(220, 53, 69, 0.3);
        }

        /* Progress Indicator */
        .form-progress {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            padding: 0 30px;
        }

        .progress-step {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e1e5e9;
            margin: 0 4px;
            transition: background 0.3s ease;
        }

        .progress-step.active {
            background: #007bff;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .form-group.full-width {
                grid-column: span 2;
            }
        }

        @media (max-width: 768px) {
            .header, .nav-bar {
                padding: 20px;
            }

            .main-container {
                margin: 20px auto;
                padding: 0 15px;
            }

            .form-container {
                border-radius: 15px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .search-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .search-buttons {
                justify-content: center;
            }

            .form-actions {
                flex-direction: column;
                align-items: center;
            }

            .nav-links {
                flex-direction: column;
                gap: 10px;
            }

            .nav-links a {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 24px;
            }

            .form-header h2 {
                font-size: 20px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px 16px;
                font-size: 16px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #007bff, #0056d2);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #0056d2, #004085);
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            animation: fadeIn 0.5s ease-out;
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Focus states for accessibility */
        button:focus,
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
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
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-file-invoice"></i> Create Invoice</h1>
        <p>Fill out the form below to create a new invoice record</p>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Search Section -->
        <div class="search-section">
            <h3><i class="fas fa-search"></i> Search Existing Records</h3>
            <div class="search-form">
                <div class="form-group">
                    <label for="search_sap_id">SAP ID</label>
                    <input type="text" id="search_sap_id" placeholder="Enter SAP ID to search">
                </div>
                <div class="form-group">
                    <label for="search_gis_id">GIS ID</label>
                    <select id="search_gis_id" onchange="fetchInvoiceDetails()">
                        <option value="">-- Select GIS ID --</option>
                    </select>
                </div>
                <div class="search-buttons">
                    <button type="button" class="btn btn-primary" onclick="fetchGISList()">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSearch()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Invoice Form -->
        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Invoice Details</h2>
                <p>Enter all required information for the new invoice</p>
            </div>

            <form method="post" action="save_invoice.php">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="sap_id">SAP ID</label>
                        <input type="text" id="sap_id" name="SAP_ID" required>
                    </div>
                    <div class="form-group">
                        <label for="gis_id">GIS ID</label>
                        <input type="text" id="gis_id" name="GIS_ID" required>
                    </div>
                    <div class="form-group">
                        <label for="employee_name">Employee Name</label>
                        <input type="text" id="employee_name" name="Employee_Name" required>
                    </div>
                    <div class="form-group">
                        <label for="case_manager">Case Manager Name</label>
                        <input type="text" id="case_manager" name="Case_Manager_Name" required>
                    </div>
                    <div class="form-group">
                        <label for="request_type">Type of Request</label>
                        <input type="text" id="request_type" name="Type_of_Request" required>
                    </div>
                    <div class="form-group">
                        <label for="visa_type">Visa Type</label>
                        <input type="text" id="visa_type" name="Visa_Type" required>
                    </div>
                    <div class="form-group">
                        <label for="visa_sub_type">Visa Sub Type</label>
                        <input type="text" id="visa_sub_type" name="Visa_Sub_Type">
                    </div>
                    <div class="form-group">
                        <label for="source_country">Source Country</label>
                        <input type="text" id="source_country" name="Source_Country" required>
                    </div>
                    <div class="form-group">
                        <label for="destination_country">Destination Country</label>
                        <input type="text" id="destination_country" name="Destination_Country" required>
                    </div>
                    <div class="form-group">
                        <label for="region">Region</label>
                        <input type="text" id="region" name="Region">
                    </div>
                    <div class="form-group">
                        <label for="geo_approver">GEO Leads Approver</label>
                        <input type="text" id="geo_approver" name="GEO_Leads_Approver_Name">
                    </div>
                    <div class="form-group">
                        <label for="date_geo_sent">Date Sent to GEO Lead</label>
                        <input type="date" id="date_geo_sent" name="Date_Sent_to_GEO_Lead">
                    </div>
                    <div class="form-group">
                        <label for="date_geo_approved">Date Approved by GEO Lead</label>
                        <input type="date" id="date_geo_approved" name="Date_Approved_by_GEO_Lead">
                    </div>
                    <div class="form-group">
                        <label for="date_mail_room">Date Sent to Mail Room</label>
                        <input type="date" id="date_mail_room" name="Date_Sent_to_Mail_Room">
                    </div>
                    <div class="form-group">
                        <label for="neon_date">Neon Introduction Date</label>
                        <input type="date" id="neon_date" name="Neon_Introduction_Date">
                    </div>
                    <div class="form-group">
                        <label for="date_neon_sent">Date Sent to Approver in Neon</label>
                        <input type="date" id="date_neon_sent" name="Date_Sent_to_Approver_in_Neon">
                    </div>
                    <div class="form-group">
                        <label for="date_neon_approved">Date Approved by L1 L4 in Neon</label>
                        <input type="date" id="date_neon_approved" name="Date_Approved_by_L1_L4_in_Neon">
                    </div>
                    <div class="form-group">
                        <label for="finance_date">Finance Approved Date</label>
                        <input type="date" id="finance_date" name="Finance_Approved_Date">
                    </div>
                    <div class="form-group">
                        <label for="payment_date">Payment Processed Date</label>
                        <input type="date" id="payment_date" name="Payment_Processed_Date">
                    </div>
                    <div class="form-group">
                        <label for="toscana_id">Toscana ID Ariba</label>
                        <input type="text" id="toscana_id" name="Toscana_ID_Ariba">
                    </div>
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" id="invoice_number" name="Invoice_Number" required>
                    </div>
                    <div class="form-group">
                        <label for="individual_invoice">Individual Invoice Number</label>
                        <input type="text" id="individual_invoice" name="Individual_Invoice_Number">
                    </div>
                    <div class="form-group">
                        <label for="invoice_date">Invoice Date</label>
                        <input type="date" id="invoice_date" name="Invoice_Date" required>
                    </div>
                    <div class="form-group">
                        <label for="received_date">Invoice Received Date at HCL</label>
                        <input type="date" id="received_date" name="Invoice_Received_Date_at_HCL">
                    </div>
                    <div class="form-group">
                        <label for="due_date">Invoice Due Date</label>
                        <input type="date" id="due_date" name="Invoice_Due_Date">
                    </div>
                    <div class="form-group">
                        <label for="service_description">Service Description</label>
                        <input type="text" id="service_description" name="Service_Description">
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="Category">
                    </div>
                    <div class="form-group">
                        <label for="service_month">Service Rendered Month</label>
                        <input type="text" id="service_month" name="Service_Rendered_Month">
                    </div>
                    <div class="form-group">
                        <label for="vendor_code">Vendor Code</label>
                        <input type="text" id="vendor_code" name="Vendor_Code">
                    </div>
                    <div class="form-group">
                        <label for="vendor_name">Vendor Name</label>
                        <input type="text" id="vendor_name" name="Vendor_Name" required>
                    </div>
                    <div class="form-group">
                        <label for="company_code">Company Code</label>
                        <input type="text" id="company_code" name="Company_Code">
                    </div>
                    <div class="form-group">
                        <label for="service_provider_fee">Service Provider Fee</label>
                        <input type="number" id="service_provider_fee" name="Service_Provider_Fee" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="sp_fee_tax">Service Provider Fee Tax</label>
                        <input type="number" id="sp_fee_tax" name="Service_Provider_Fee_Tax" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="service_tax_total">Service Tax Total</label>
                        <input type="number" id="service_tax_total" name="Service_Tax_Total" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="govt_fee">Govt Fee</label>
                        <input type="number" id="govt_fee" name="Govt_Fee" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="vfs_fee">VFS Fee</label>
                        <input type="number" id="vfs_fee" name="VFS_Fee" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="misc_exp">Misc Other Exp</label>
                        <input type="number" id="misc_exp" name="Misc_Other_Exp" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="tax">Tax</label>
                        <input type="number" id="tax" name="Tax" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="total_amount">Total Invoice Amount</label>
                        <input type="number" id="total_amount" name="Total_Invoice_Amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <input type="text" id="currency" name="Currency" value="USD">
                    </div>
                    <div class="form-group">
                        <label for="cost_center">Cost Center</label>
                        <input type="text" id="cost_center" name="Cost_Center">
                    </div>
                    <div class="form-group">
                        <label for="mirror_cost_center">Mirror Cost Center</label>
                        <input type="text" id="mirror_cost_center" name="Mirror_Cost_Center">
                    </div>
                    <div class="form-group">
                        <label for="project_code">Project Code</label>
                        <input type="text" id="project_code" name="Project_Code">
                    </div>
                    <div class="form-group">
                        <label for="gstn">GSTN</label>
                        <input type="text" id="gstn" name="GSTN">
                    </div>
                    <div class="form-group">
                        <label for="exchange_rate">Currency Exchange Rate</label>
                        <input type="number" id="exchange_rate" name="Currency_Exchange_Rate" step="0.0001">
                    </div>
                    <div class="form-group">
                        <label for="usd_amount">Document Currency in USD</label>
                        <input type="number" id="usd_amount" name="Document_Currency_in_USD" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="lob">LOB</label>
                        <input type="text" id="lob" name="LOB">
                    </div>
                    <div class="form-group">
                        <label for="invoice_status">Invoice Current Status</label>
                        <input type="text" id="invoice_status" name="Invoice_Current_Status">
                    </div>
                    <div class="form-group full-width">
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks" name="Remarks" rows="4" placeholder="Enter any additional remarks or notes..."></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Invoice
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSearch()">
                        <i class="fas fa-times"></i> Clear Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate total when amounts change
            const amountFields = ['service_provider_fee', 'sp_fee_tax', 'govt_fee', 'vfs_fee', 'misc_exp', 'tax'];
            amountFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', calculateTotal);
                }
            });

            function calculateTotal() {
                const spFee = parseFloat(document.getElementById('service_provider_fee').value) || 0;
                const spTax = parseFloat(document.getElementById('sp_fee_tax').value) || 0;
                const govtFee = parseFloat(document.getElementById('govt_fee').value) || 0;
                const vfsFee = parseFloat(document.getElementById('vfs_fee').value) || 0;
                const miscExp = parseFloat(document.getElementById('misc_exp').value) || 0;
                const tax = parseFloat(document.getElementById('tax').value) || 0;

                const total = spFee + spTax + govtFee + vfsFee + miscExp + tax;
                document.getElementById('total_amount').value = total.toFixed(2);
            }

            // Form validation enhancement
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('input[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.style.borderColor = '#dc3545';
                        isValid = false;
                    } else {
                        field.style.borderColor = '#28a745';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });

            // Add loading state to submit button
            const submitBtn = document.querySelector('button[type="submit"]');
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>
