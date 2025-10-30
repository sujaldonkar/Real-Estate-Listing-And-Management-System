<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if property ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$property_id = sanitizeInput($_GET['id']);

// Get property details to verify ownership
$sql = "SELECT * FROM properties WHERE id = ? AND seller_id = ?";
$property = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $property_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $property = $row;
    } else {
        header("Location: dashboard.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete property images first
        $sql = "SELECT image_path FROM property_images WHERE property_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $property_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                if (file_exists($row['image_path'])) {
                    unlink($row['image_path']);
                }
            }
        }
        
        // Delete property images from database
        $sql = "DELETE FROM property_images WHERE property_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $property_id);
            mysqli_stmt_execute($stmt);
        }
        
        // Delete property
        $sql = "DELETE FROM properties WHERE id = ? AND seller_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $property_id, $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                mysqli_commit($conn);
                $_SESSION['success'] = "Property deleted successfully.";
                header("Location: dashboard.php");
                exit();
            } else {
                throw new Exception("Failed to delete property.");
            }
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error deleting property: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Property - Real Estate Platform</title>
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
        <div class="delete-confirmation">
            <h2>Delete Property</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="property-details">
                <h3><?php echo htmlspecialchars($property['title']); ?></h3>
                <p><strong>Property Type:</strong> <?php echo ucfirst($property['property_type']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($property['location']); ?></p>
                <p><strong>Price:</strong> $<?php echo number_format($property['price']); ?></p>
            </div>

            <div class="confirmation-message">
                <p>Are you sure you want to delete this property? This action cannot be undone.</p>
                <p>All associated images will also be deleted.</p>
            </div>

            <form action="delete_property.php?id=<?php echo $property_id; ?>" method="POST" class="delete-form">
                <div class="form-group">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete Property</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .delete-confirmation {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .delete-confirmation h2 {
            color: #dc3545;
            margin-bottom: 1.5rem;
        }

        .property-details {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .confirmation-message {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            color: #856404;
        }

        .delete-form {
            margin-top: 2rem;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 1rem;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</body>
</html> 