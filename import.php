<?php
// import.php

// Handle file uploads
$vendorMsg = $gisMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the 'uploads' directory exists, if not, create it.
    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Vendor Report upload logic
    if (isset($_POST['upload_vendor'])) {
        if (isset($_FILES['vendor_file']) && $_FILES['vendor_file']['error'] === 0) {
            $vendorTmp = $_FILES['vendor_file']['tmp_name'];
            $vendorName = $_FILES['vendor_file']['name'];
            // In a real application, you'd want to sanitize $vendorName
            // and possibly rename the file to prevent conflicts/security issues.
            move_uploaded_file($vendorTmp, "uploads/$vendorName");
            $vendorMsg = "✅ Vendor Report uploaded successfully: <strong>$vendorName</strong>";
        } else {
            $vendorMsg = "❌ Failed to upload Vendor Report. Please try again.";
            if (isset($_FILES['vendor_file'])) {
                 switch ($_FILES['vendor_file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $vendorMsg .= " File too large.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $vendorMsg = "❌ No Vendor Report file was selected.";
                        break;
                    default:
                        $vendorMsg .= " Error code: " . $_FILES['vendor_file']['error'];
                        break;
                }
            }
        }
    }

    // New GIS Report upload logic
    if (isset($_POST['upload_gis'])) {
        if (isset($_FILES['gis_file']) && $_FILES['gis_file']['error'] === 0) {
            $gisTmp = $_FILES['gis_file']['tmp_name'];
            $gisName = $_FILES['gis_file']['name'];
            // In a real application, you'd want to sanitize $gisName
            // and possibly rename the file to prevent conflicts/security issues.
            move_uploaded_file($gisTmp, "uploads/$gisName");
            $gisMsg = "✅ New GIS Report uploaded successfully: <strong>$gisName</strong>";
        } else {
            $gisMsg = "❌ Failed to upload New GIS Report. Please try again.";
            if (isset($_FILES['gis_file'])) {
                switch ($_FILES['gis_file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $gisMsg .= " File too large.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $gisMsg = "❌ No New GIS Report file was selected.";
                        break;
                    default:
                        $gisMsg .= " Error code: " . $_FILES['gis_file']['error'];
                        break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IMS - Import Reports</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-blue: #007bff;
        --primary-blue-dark: #0056b3;
        --secondary-gray: #6c757d;
        --light-bg: #e9ecef;
        --white: #ffffff;
        --success-green: #28a745;
        --error-red: #dc3545;
        --border-color: #ced4da;
        --shadow-light: rgba(0, 0, 0, 0.08);
        --shadow-medium: rgba(0, 0, 0, 0.15);
    }

    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(135deg, var(--light-bg) 0%, #dcdcdc 100%);
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #333;
        box-sizing: border-box;
    }

    .container {
        /* Reduced max-width for a more compact design */
        width: 100%;
        max-width: 650px; 
        background: var(--white);
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 25px var(--shadow-medium);
        text-align: center;
        transition: transform 0.3s ease-in-out;
    }

    .container:hover {
        transform: translateY(-5px);
    }

    h2 {
        color: var(--primary-blue);
        /* Reduced margin-bottom and font-size */
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 1.8rem;
        position: relative;
        padding-bottom: 8px;
    }

    h2::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 0;
        transform: translateX(-50%);
        width: 50px;
        height: 2px;
        background-color: var(--primary-blue);
        border-radius: 2px;
    }

    .form-group {
        /* Reduced margin and padding */
        margin-bottom: 20px;
        text-align: left;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        background-color: #f9f9f9;
        box-shadow: 0 1px 6px var(--shadow-light);
    }

    label {
        display: block;
        /* Reduced font-size */
        margin-bottom: 8px;
        font-weight: 500;
        color: #444;
        font-size: 1rem;
    }

    input[type="file"] {
        display: block;
        width: calc(100% - 22px);
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        margin-bottom: 12px;
        font-size: 0.95rem;
        background-color: var(--white);
        transition: border-color 0.3s, box-shadow 0.3s;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
    }

    input[type="file"]:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }
    
    .upload-button {
        width: 100%;
        /* Reduced padding and font-size */
        padding: 10px 15px;
        background-color: var(--primary-blue);
        color: var(--white);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        box-shadow: 0 3px 8px rgba(0, 123, 255, 0.2);
    }

    .upload-button:hover {
        background-color: var(--primary-blue-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(0, 123, 255, 0.3);
    }

    .upload-button:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px rgba(0, 123, 255, 0.2);
    }

    .msg {
        margin-top: 25px;
        padding: 12px 15px;
        border-radius: 8px;
        font-weight: 500;
        text-align: center;
        line-height: 1.4;
        font-size: 0.9rem;
        box-shadow: 0 1px 6px rgba(0,0,0,0.08);
    }

    .msg.success {
        background-color: #d4edda;
        color: var(--success-green);
        border: 1px solid #c3e6cb;
    }

    .msg.error {
        background-color: #f8d7da;
        color: var(--error-red);
        border: 1px solid #f5c6cb;
    }
</style>
</head>
<body>

<div class="container">
    <h2><span style="color: #4CAF50;">IMS</span> - Import Reports 🚀</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="vendor_file">Vendor Invoice Report (.csv)</label>
            <input type="file" name="vendor_file" id="vendor_file" accept=".csv">
            <button type="submit" name="upload_vendor" class="upload-button">Upload Vendor File</button>
        </div>

        <div class="form-group">
            <label for="gis_file">New GIS Report (.csv)</label>
            <input type="file" name="gis_file" id="gis_file" accept=".csv">
            <button type="submit" name="upload_gis" class="upload-button">Upload GIS File</button>
        </div>
    </form>
    
    <?php 
    if($vendorMsg) { 
        $msgClass = strpos($vendorMsg, '❌') !== false ? 'error' : 'success';
        echo "<p class='msg $msgClass'>$vendorMsg</p>"; 
    } 
    if($gisMsg) {
        $msgClass = strpos($gisMsg, '❌') !== false ? 'error' : 'success';
        echo "<p class='msg $msgClass'>$gisMsg</p>";
    }
    ?>
</div>

</body>
</html>