<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get properties from database with their first image
$sql = "SELECT p.*, u.name as seller_name, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY id ASC LIMIT 1) as first_image
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 12";

$properties = [];
if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
    mysqli_free_result($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Platform</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <h1>Real Estate Platform</h1>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if (isSeller()): ?>
                        <a href="admin/">Seller Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="search-section">
            <h2>Find Your Dream Property</h2>
            <form action="search.php" method="GET" class="search-form">
                <!-- <input type="text" name="location" placeholder="Location" class="form-control">
               <select name="property_type" class="form-control">
                    <option value="">Property Type</option>
                    <option value="flat">Flat</option>
                    <option value="bungalow">Bungalow</option>
                    <option value="plot">Plot</option>
                    <option value="house">House</option>
                    <option value="room">Room</option>
                </select>
                <select name="transaction_type" class="form-control">
                    <option value="">Transaction Type</option>
                    <option value="buy">Buy</option>
                    <option value="rent">Rent</option>
                </select>-->
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Featured Properties -->
        <section style="padding:15px;" class="featured-properties">
            <h2>Featured Properties</h2>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <div class="property-card">
                        <img src="<?php echo !empty($property['first_image']) ? $property['first_image'] : 'public/images/default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($property['title']); ?>" 
                             class="property-image">
                        <div class="property-info">
                            <h3 class="property-title"><?php echo $property['title']; ?></h3>
                            <p class="property-price">Rs <?php echo number_format($property['price']); ?></p>
                            <p class="property-details">
                                <?php echo ucfirst($property['property_type']); ?>
                                <?php if ($property['property_type'] !== 'plot'): ?>
                                    | <?php echo (isset($property['bedrooms']) && $property['bedrooms'] !== null ? $property['bedrooms'] . ' Beds' : 'N/A Beds'); ?> 
                                    | <?php echo (isset($property['bathrooms']) && $property['bathrooms'] !== null ? $property['bathrooms'] . ' Baths' : 'N/A Baths'); ?>
                                <?php else: ?>
                                    | <?php echo (isset($property['plot_size']) && $property['plot_size'] !== null ? number_format($property['plot_size']) . ' sq ft' : 'N/A'); ?>
                                <?php endif; ?>
                            </p>
                            <p class="property-location"><?php echo $property['city']; ?>, <?php echo $property['state']; ?></p>
                            <p class="seller-info">Seller: <?php echo $property['seller_name']; ?></p>
                            <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Real Estate Platform. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
