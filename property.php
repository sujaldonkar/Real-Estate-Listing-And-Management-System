<?php
require_once 'config/database.php';
require_once 'config/session.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$property_id = sanitizeInput($_GET['id']);

// Get property details
$sql = "SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone 
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        WHERE p.id = ?";

$property = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $property = $row;
    } else {
        header("Location: index.php");
        exit();
    }
}

// Get property images
$sql = "SELECT * FROM property_images WHERE property_id = ?";
$images = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $property_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $property['title']; ?> - Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Real Estate Platform</h1>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="property-details">
            <!-- Property Images -->
            <div class="property-gallery">
                <?php if (!empty($images)): ?>
                    <div class="main-image">
                        <img src="<?php echo $images[0]['image_path']; ?>" alt="<?php echo $property['title']; ?>">
                    </div>
                    <div class="thumbnail-images">
                        <?php foreach ($images as $image): ?>
                            <img src="<?php echo $image['image_path']; ?>" alt="<?php echo $property['title']; ?>">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="main-image">
                        <img src="public/images/default.jpg" alt="<?php echo $property['title']; ?>">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Property Information -->
            <div class="property-info">
                <h1><?php echo $property['title']; ?></h1>
                <p class="property-price">Rs <?php echo number_format($property['price']); ?></p>
                
                <div class="property-meta">
                    <p><strong>Property Type:</strong> <?php echo ucfirst($property['property_type']); ?></p>
                    <p><strong>Transaction Type:</strong> <?php echo ucfirst($property['transaction_type']); ?></p>
                    <p><strong>Location:</strong> <?php echo $property['location']; ?></p>
                    <p><strong>City:</strong> <?php echo $property['city']; ?></p>
                    <p><strong>State:</strong> <?php echo $property['state']; ?></p>
                    <p><strong>Area:</strong> <?php echo (isset($property['area']) && $property['area'] !== null ? number_format($property['area']) . ' sq ft' : 'N/A'); ?></p>
                    <p><strong>Price:</strong> Rs <?php echo number_format($property['price']); ?></p>
                    
                    <?php if ($property['property_type'] === 'flat' || $property['property_type'] === 'house' || $property['property_type'] === 'bungalow'): ?>
                        <p><strong>Bedrooms:</strong> <?php echo (isset($property['bedrooms']) && $property['bedrooms'] !== null ? $property['bedrooms'] : 'N/A'); ?></p>
                        <p><strong>Bathrooms:</strong> <?php echo (isset($property['bathrooms']) && $property['bathrooms'] !== null ? $property['bathrooms'] : 'N/A'); ?></p>
                        <p><strong>Furnishing:</strong> <?php echo (!empty($property['furnishing']) ? ucfirst($property['furnishing']) : 'N/A'); ?></p>
                        
                        <?php if ($property['property_type'] === 'flat'): ?>
                            <p><strong>Floor:</strong> <?php echo (isset($property['floor']) && $property['floor'] !== null ? $property['floor'] : 'N/A'); ?></p>
                            <p><strong>Parking:</strong> <?php echo (!empty($property['parking']) ? ucfirst($property['parking']) : 'N/A'); ?></p>
                        <?php elseif ($property['property_type'] === 'house'): ?>
                            <p><strong>Floors:</strong> <?php echo (isset($property['floors']) && $property['floors'] !== null ? $property['floors'] : 'N/A'); ?></p>
                            <p><strong>Age:</strong> <?php echo (isset($property['age']) && $property['age'] !== null ? $property['age'] . ' years' : 'N/A'); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($property['property_type'] === 'bungalow'): ?>
                            <p><strong>Plot Size:</strong> <?php echo (isset($property['plot_size']) && $property['plot_size'] !== null ? number_format($property['plot_size']) . ' sq ft' : 'N/A'); ?></p>
                        <?php endif; ?>
                        
                    <?php elseif ($property['property_type'] === 'plot'): ?>
                        <p><strong>Plot Size:</strong> <?php echo (isset($property['plot_size']) && $property['plot_size'] !== null ? number_format($property['plot_size']) . ' sq ft' : 'N/A'); ?></p>
                        <p><strong>Plot Type:</strong> <?php echo (!empty($property['plot_type']) ? ucfirst($property['plot_type']) : 'N/A'); ?></p>
                        <p><strong>Facing:</strong> <?php echo (!empty($property['facing']) ? ucfirst($property['facing']) : 'N/A'); ?></p>
                        <p><strong>Boundary Wall:</strong> <?php echo (!empty($property['boundary_wall']) ? ucfirst($property['boundary_wall']) : 'N/A'); ?></p>
                        
                    <?php elseif ($property['property_type'] === 'room'): ?>
                        <p><strong>Room Type:</strong> <?php echo (!empty($property['room_type']) ? ucfirst($property['room_type']) : 'N/A'); ?></p>
                        <p><strong>Furnishing:</strong> <?php echo (!empty($property['furnishing']) ? ucfirst($property['furnishing']) : 'N/A'); ?></p>
                        <p><strong>Floor:</strong> <?php echo (isset($property['floor']) && $property['floor'] !== null ? $property['floor'] : 'N/A'); ?></p>
                        <p><strong>Bathroom Attached:</strong> <?php echo (!empty($property['bathroom_attached']) ? ucfirst($property['bathroom_attached']) : 'N/A'); ?></p>
                    <?php endif; ?>
                </div>

                <div class="property-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br($property['description']); ?></p>
                </div>

                <!-- Contact Information -->
                <div class="property-contact">
                    <h3>Contact Seller</h3>
                    <p>Name: <?php echo $property['seller_name']; ?></p>
                    <p>Email: <?php echo $property['seller_email']; ?></p>
                    <p>Phone: <?php echo $property['seller_phone']; ?></p>
                </div>

                <!-- Verification Document -->
                <div class="property-verification">
                    <h3>Verification Document</h3>
                    <?php if ($property['verification_doc']): ?>
                        <?php
                        $doc_ext = strtolower(pathinfo($property['verification_doc'], PATHINFO_EXTENSION));
                        if ($doc_ext === 'pdf'): ?>
                            <embed src="<?php echo htmlspecialchars($property['verification_doc']); ?>" type="application/pdf" width="100%" height="600px">
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars($property['verification_doc']); ?>" alt="Verification Document" class="verification-doc">
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No verification document available.</p>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="property-actions">
                    <?php if (isLoggedIn() && $_SESSION['user_id'] == $property['seller_id']): ?>
                        <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Edit Property</a>
                        <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property? This action cannot be undone.');">Delete Property</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple image gallery functionality
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.thumbnail-images img');
            const mainImage = document.querySelector('.main-image img');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    mainImage.src = this.src;
                });
            });
        });
    </script>
</body>
</html> 