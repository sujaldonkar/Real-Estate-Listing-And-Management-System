<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Check if user is logged in and is a seller
requireLogin();
requireSeller();

// Get all properties
$sql = "SELECT p.*, u.name as seller_name 
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        ORDER BY p.created_at DESC";
$properties = [];

if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
}

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$users = [];

if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Real Estate Platform</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Seller Dashboard</h1>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="../dashboard.php">Dashboard</a>
                <a href="../list_property.php">List Property</a>
                <a href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="admin-dashboard">
            <!-- Properties Section -->
            <section class="dashboard-section">
                <h2>All Properties</h2>
                <a href="../add_property.php" class="btn btn-primary">Add New Property</a>
                
                <div class="property-grid">
                    <?php foreach ($properties as $property): ?>
                        <div class="property-card">
                            <div class="property-info">
                                <h3 class="property-title"><?php echo $property['title']; ?></h3>
                                <p class="property-price">Rs<?php echo number_format($property['price']); ?></p>
                                <p class="property-details">
                                    <?php echo $property['property_type']; ?> | 
                                    <?php echo $property['city']; ?>, <?php echo $property['state']; ?>
                                </p>
                                <p>Posted by: <?php echo $property['seller_name']; ?></p>
                                <div class="property-actions">
                                    <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">Edit</a>
                                    <a href="delete_property.php?id=<?php echo $property['id']; ?>" class="btn btn-danger">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Users Section -->
            <section class="dashboard-section">
                <h2>All Users</h2>
                <div class="users-list">
                    <?php foreach ($users as $user): ?>
                        <div class="user-card">
                            <h4><?php echo $user['name']; ?></h4>
                            <p>Email: <?php echo $user['email']; ?></p>
                            <p>Role: <?php echo $user['role']; ?></p>
                            <p>Joined: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</body>
</html> 