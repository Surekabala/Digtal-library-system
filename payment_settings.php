
<?php
require_once "config/config.php";
redirectIfNotLoggedIn();
redirectIfNotAdmin();

include "includes/header.php";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upi_id = clean_input($_POST['upi_id']);
    
    // Check if settings already exist
    $check_query = "SELECT * FROM system_settings WHERE setting_name = 'upi_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing setting
        $update_query = "UPDATE system_settings SET setting_value = ? WHERE setting_name = 'upi_id'";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "s", $upi_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['message'] = "UPI ID updated successfully.";
        } else {
            $_SESSION['error'] = "Error updating UPI ID.";
        }
    } else {
        // Insert new setting
        $insert_query = "INSERT INTO system_settings (setting_name, setting_value) VALUES ('upi_id', ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "s", $upi_id);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['message'] = "UPI ID saved successfully.";
        } else {
            $_SESSION['error'] = "Error saving UPI ID.";
        }
    }
    
    // Handle QR code upload
    if (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png');
        $filename = $_FILES['qr_image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $target_file = "images/qr_code." . $ext;
            
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $target_file)) {
                // Save the QR image path to database
                $qr_path = $target_file;
                
                // Check if setting exists
                $check_query = "SELECT * FROM system_settings WHERE setting_name = 'qr_image_path'";
                $check_result = mysqli_query($conn, $check_query);
                
                if (mysqli_num_rows($check_result) > 0) {
                    // Update existing setting
                    $update_query = "UPDATE system_settings SET setting_value = ? WHERE setting_name = 'qr_image_path'";
                    $update_stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($update_stmt, "s", $qr_path);
                    mysqli_stmt_execute($update_stmt);
                } else {
                    // Insert new setting
                    $insert_query = "INSERT INTO system_settings (setting_name, setting_value) VALUES ('qr_image_path', ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_query);
                    mysqli_stmt_bind_param($insert_stmt, "s", $qr_path);
                    mysqli_stmt_execute($insert_stmt);
                }
                
                $_SESSION['message'] = $_SESSION['message'] ? $_SESSION['message'] . " QR code uploaded successfully." : "QR code uploaded successfully.";
            } else {
                $_SESSION['error'] = "Error uploading QR code.";
            }
        } else {
            $_SESSION['error'] = "Only JPG, JPEG and PNG files are allowed for QR code.";
        }
    }
}

// Get current settings
$upi_id = "";
$qr_image_path = "";

$settings_query = "SELECT * FROM system_settings WHERE setting_name IN ('upi_id', 'qr_image_path')";
$settings_result = mysqli_query($conn, $settings_query);

while ($setting = mysqli_fetch_assoc($settings_result)) {
    if ($setting['setting_name'] == 'upi_id') {
        $upi_id = $setting['setting_value'];
    } elseif ($setting['setting_name'] == 'qr_image_path') {
        $qr_image_path = $setting['setting_value'];
    }
}
?>

<h1>Payment Settings</h1>

<div class="form-container" style="max-width: 600px;">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>UPI ID for Payments</label>
            <input type="text" name="upi_id" class="form-control" value="<?php echo htmlspecialchars($upi_id); ?>" required>
            <small>This UPI ID will be displayed to users for making payments.</small>
        </div>
        
        <div class="form-group">
            <label>Upload QR Code Image</label>
            <input type="file" name="qr_image" class="form-control">
            <small>Upload a QR code image that users can scan for UPI payments.</small>
        </div>
        
        <?php if (!empty($qr_image_path) && file_exists($qr_image_path)): ?>
        <div class="form-group">
            <label>Current QR Code</label>
            <div class="qr-image">
                <img src="<?php echo $qr_image_path; ?>" alt="UPI QR Code" style="max-width: 200px;">
            </div>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <input type="submit" class="btn" value="Save Settings">
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
