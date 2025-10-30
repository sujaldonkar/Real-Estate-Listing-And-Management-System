<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Get search parameters
$city = isset($_GET['city']) ? sanitizeInput($_GET['city']) : '';
$property_type = isset($_GET['property_type']) ? sanitizeInput($_GET['property_type']) : '';
$min_price = isset($_GET['min_price']) ? sanitizeInput($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? sanitizeInput($_GET['max_price']) : '';

// Build search query
$sql = "SELECT p.*, u.name as seller_name 
        FROM properties p 
        JOIN users u ON p.seller_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($city)) {
    $sql .= " AND p.city LIKE ?";
    $params[] = "%$city%";
    $types .= "s";
}

if (!empty($property_type)) {
    $sql .= " AND p.property_type = ?";
    $params[] = $property_type;
    $types .= "s";
}

if (!empty($min_price)) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if (!empty($max_price)) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$sql .= " ORDER BY p.created_at DESC";

$properties = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Properties - Real Estate Platform</title>
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
        <div class="search-section">
            <h2>Search Properties</h2>
            <form action="search.php" method="GET" class="search-form">
                <div class="form-group">
                    <input type="text" name="city" placeholder="City" class="form-control" value="<?php echo $city; ?>">
                </div>
                
                <div class="form-group">
                    <select name="property_type" class="form-control">
                        <option value="">Property Type</option>
                        <option value="flat" <?php echo $property_type == 'flat' ? 'selected' : ''; ?>>Flat</option>
                        <option value="plot" <?php echo $property_type == 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="bungalow" <?php echo $property_type == 'bungalow' ? 'selected' : ''; ?>>Bungalow</option>
                        <option value="house" <?php echo $property_type == 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="room" <?php echo $property_type == 'room' ? 'selected' : ''; ?>>Room</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="number" name="min_price" placeholder="Min Price" class="form-control" value="<?php echo $min_price; ?>">
                    </div>
                    
                    <div class="form-group">
                        <input type="number" name="max_price" placeholder="Max Price" class="form-control" value="<?php echo $max_price; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <section class="search-results">
            <h3>Search Results</h3>
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
                            <p class="seller-info">Seller: <?php echo $property['seller_name']; ?></p>
                            <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</body>
</html> 