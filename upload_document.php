<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
requireLogin();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = sanitizeInput($_POST['document_type']);
    
    // Handle document upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $target_dir = "public/uploads/documents/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["document"]["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $error = "Only PDF, JPG, JPEG, and PNG files are allowed.";
        } else {
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
                // Insert document into database
                $sql = "INSERT INTO documents (user_id, document_type, document_path) VALUES (?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iss", $_SESSION['user_id'], $document_type, $target_file);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success = "Document uploaded successfully!";
                    } else {
                        $error = "Something went wrong. Please try again later.";
                    }
                }
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $error = "Please select a document to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document - Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Real Estate Platform</h1>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="document-form">
            <h2>Upload Document</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Document Type</label>
                    <select name="document_type" class="form-control" required>
                        <option value="id_proof">ID Proof</option>
                        <option value="address_proof">Address Proof</option>
                        <option value="property_documents">Property Documents</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Document File</label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="form-text text-muted">Allowed file types: PDF, JPG, JPEG, PNG</small>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Upload Document">
                </div>
            </form>
        </div>
    </div>
</body>
</html> 