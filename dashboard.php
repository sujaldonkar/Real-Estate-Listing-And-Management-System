<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in
requireLogin();

// Get user's properties if they are a seller
$properties = [];
if (isSeller()) {
    $sql = "SELECT * FROM properties WHERE seller_id = ? ORDER BY created_at DESC";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $properties[] = $row;
        }
    }
}

// Get recent properties for buyers
$recent_properties = [];
if (isBuyer()) {
    $sql = "SELECT p.*, u.name as seller_name 
            FROM properties p 
            JOIN users u ON p.seller_id = u.id 
            ORDER BY p.created_at DESC 
            LIMIT 6";
    
    if ($result = mysqli_query($conn, $sql)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recent_properties[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Real Estate Platform</h1>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="search.php">Search Properties</a>
                <?php if (isSeller()): ?>
                    <a href="add_property.php">List Property</a>
                    <a href="admin/">Seller Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard">
            <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
            
            <?php if (isSeller()): ?>
                <!-- Seller Dashboard -->
                <section class="dashboard-section">
                    <h3>Your Properties</h3>
                    <a href="add_property.php" class="btn btn-primary">Add New Property</a>
                    
                    <div class="property-grid">
                        <?php foreach ($properties as $property): ?>
                            <div class="property-card">
                                <div class="property-info">
                                    <h3 class="property-title"><?php echo $property['title']; ?></h3>
                                    <p class="property-price">$<?php echo number_format($property['price']); ?></p>
                                    <p class="property-details">
                                        <?php echo $property['property_type']; ?> | 
                                        <?php echo $property['city']; ?>, <?php echo $property['state']; ?>
                                    </p>
                                    <div class="property-actions">
                                        <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Edit</a>
                                        <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-danger">Delete</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php else: ?>
                <!-- Buyer Dashboard -->
                <section class="dashboard-section">
                    <h3>Recent Properties</h3>
                    <div class="property-grid">
                        <?php foreach ($recent_properties as $property): ?>
                            <div class="property-card">
                                <div class="property-info">
                                    <h3 class="property-title"><?php echo $property['title']; ?></h3>
                                    <p class="property-price">$<?php echo number_format($property['price']); ?></p>
                                    <p class="property-details">
                                        <?php echo $property['property_type']; ?> | 
                                        <?php echo $property['city']; ?>, <?php echo $property['state']; ?>
                                    </p>
                                    <p class="seller-info">Seller: <?php echo $property['seller_name']; ?></p>
                                    <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 