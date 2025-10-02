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
<title>Import Data - Invoice Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f6fa;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.container {
    background: #fff;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    max-width: 700px;
    width: 100%;
}
.header {
    text-align: center;
    margin-bottom: 20px;
}
.header h1 {
    color: #007bff;
    font-size: 26px;
    margin-bottom: 5px;
}
.header p {
    color: #555;
    font-size: 14px;
}
.upload-section {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    justify-content: space-between;
}
.upload-card {
    flex: 1 1 48%;
    background: #f7f9ff;
    padding: 20px;
    border: 2px dashed #007bff;
    border-radius: 10px;
    text-align: center;
}
.upload-card h2 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #007bff;
}
.upload-card i {
    font-size: 28px;
    margin-bottom: 8px;
    color: #007bff;
}
.upload-card input[type="file"] {
    display: none;
}
.file-input-label, .upload-card button {
    display: block;
    width: 100%;
    margin: 5px 0;
    padding: 10px 0;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-weight: 500;
}
.file-input-label {
    background: #007bff;
    color: #fff;
}
.file-input-label:hover {
    background: #0056d2;
}
.upload-card button {
    background: #28a745;
    color: #fff;
}
.upload-card button:hover {
    background: #1e7e34;
}
.message {
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    margin-bottom: 10px;
    font-size: 14px;
}
.message.success {
    background: #d4edda;
    color: #155724;
}
.message.error {
    background: #f8d7da;
    color: #721c24;
}
.info-section {
    font-size: 13px;
    color: #555;
    background: #f1f3f6;
    padding: 15px;
    border-radius: 8px;
}
.info-section ul {
    padding-left: 18px;
    margin: 0;
}
.info-section li {
    margin-bottom: 5px;
}
@media (max-width: 600px) {
    .upload-section {
        flex-direction: column;
    }
    .upload-card {
        flex: 1 1 100%;
    }
}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-upload"></i> Import Data</h1>
        <p>Upload Vendor & GIS Reports securely</p>
    </div>

    <div class="upload-section">
        <!-- Vendor Upload -->
        <div class="upload-card">
            <h2><i class="fas fa-file-alt"></i> Vendor Report</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="vendor_file" id="vendor_file" required>
                <label for="vendor_file" class="file-input-label"><i class="fas fa-folder-open"></i> Choose File</label>
                <button type="submit" name="upload_vendor"><i class="fas fa-upload"></i> Upload</button>
            </form>
        </div>

        <!-- GIS Upload -->
        <div class="upload-card">
            <h2><i class="fas fa-map-marked-alt"></i> GIS Report</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="gis_file" id="gis_file" required>
                <label for="gis_file" class="file-input-label"><i class="fas fa-folder-open"></i> Choose File</label>
                <button type="submit" name="upload_gis"><i class="fas fa-upload"></i> Upload</button>
            </form>
        </div>
    </div>

    <!-- Messages -->
    <?php
    if($vendorMsg) echo "<div class='message ".(strpos($vendorMsg,'❌')!==false?'error':'success')."'>$vendorMsg</div>";
    if($gisMsg) echo "<div class='message ".(strpos($gisMsg,'❌')!==false?'error':'success')."'>$gisMsg</div>";
    ?>

    <div class="info-section">
        <strong>Upload Guidelines:</strong>
        <ul>
            <li>Formats: CSV, XLSX, XLS</li>
            <li>Max File Size: 10MB</li>
            <li>Ensure proper headers</li>
            <li>Vendor files: SAP ID, GIS ID, invoice details</li>
            <li>GIS files: Employee & travel info</li>
            <li>Files processed automatically</li>
        </ul>
    </div>
</div>
</body>
</html>

<script>
// File drag and drop functionality
function setupDragAndDrop(uploadAreaId, fileInputId, fileInfoId) {
    const uploadArea = document.getElementById(uploadAreaId);
    const fileInput = document.getElementById(fileInputId);
    const fileInfo = document.getElementById(fileInfoId);

    // Drag and drop events
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            displayFileInfo(files[0], fileInfo);
        }
    });

    // File selection change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            displayFileInfo(e.target.files[0], fileInfo);
        }
    });
}

function displayFileInfo(file, fileInfoElement) {
    const size = (file.size / 1024 / 1024).toFixed(2);
    fileInfoElement.innerHTML = `
        <p><strong>File Name:</strong> ${file.name}</p>
        <p><strong>File Size:</strong> ${size} MB</p>
        <p><strong>File Type:</strong> ${file.type || 'Unknown'}</p>
    `;
    fileInfoElement.style.display = 'block';
}

// Setup drag and drop for both upload areas
setupDragAndDrop('vendorUploadArea', 'vendor_file', 'vendorFileInfo');
setupDragAndDrop('gisUploadArea', 'gis_file', 'gisFileInfo');

// Form submission with progress indication
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const progressBar = this.closest('.upload-section').querySelector('.upload-progress');
        const progressFill = progressBar.querySelector('.progress-fill');
        const progressText = progressBar.querySelector('.progress-text');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';

        progressBar.style.display = 'block';

        // Simulate progress (in real app, this would be actual upload progress)
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                progressText.textContent = 'Processing...';
            }
            progressFill.style.width = progress + '%';
        }, 200);
    });
});

// Add loading animation to upload sections
document.addEventListener('DOMContentLoaded', function() {
    const uploadSections = document.querySelectorAll('.upload-section');
    uploadSections.forEach((section, index) => {
        setTimeout(() => {
            section.style.opacity = '0';
            section.style.animation = 'none';
            setTimeout(() => {
                section.style.transition = 'opacity 0.5s ease';
                section.style.opacity = '1';
            }, 100);
        }, index * 200);
    });
});
</script>
</body>
</html>