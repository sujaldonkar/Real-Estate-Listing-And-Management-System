<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in and is a seller
requireLogin();
requireSeller();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $price = sanitizeInput($_POST['price']);
    $property_type = sanitizeInput($_POST['property_type']);

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "public/uploads/properties/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($error)) {
        // Insert property into database
        $sql = "INSERT INTO properties (seller_id, title, description, city, state, price, property_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "issssds", 
                $_SESSION['user_id'], 
                $title, 
                $description, 
                $city, 
                $state, 
                $price, 
                $property_type
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $property_id = mysqli_insert_id($conn);
                
                // Insert image if uploaded
                if (!empty($image_path)) {
                    $sql = "INSERT INTO property_images (property_id, image_path) VALUES (?, ?)";
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "is", $property_id, $image_path);
                        mysqli_stmt_execute($stmt);
                    }
                }
                
                $success = "Property listed successfully!";
            } else {
                $error = "Something went wrong. Please try again later.";
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
    <title>List Property - Real Estate Platform</title>
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
        <div class="property-form">
            <h2>List Your Property</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="5" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" class="form-control" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Property Type</label>
                    <select name="property_type" class="form-control" required>
                        <option value="flat">Flat</option>
                        <option value="plot">Plot</option>
                        <option value="bungalow">Bungalow</option>
                        <option value="house">House</option>
                        <option value="room">Room</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Property Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="List Property">
                </div>
            </form>
        </div>
    </div>
</body>
</html> 