<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Check if user is logged in and is a seller
if (!isLoggedIn() || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit();
}

$errors = [];
$success = false;

// Get property ID from URL
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$property_id = sanitizeInput($_GET['id']);

// Get property details
$sql = "SELECT * FROM properties WHERE id = ? AND seller_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $property_id, $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $property = mysqli_fetch_assoc($result);
    
    if (!$property) {
        header("Location: dashboard.php");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $property_type = sanitizeInput($_POST['property_type']);
    $transaction_type = sanitizeInput($_POST['transaction_type']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $location = sanitizeInput($_POST['location']);
    $bedrooms = isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : null;
    $bathrooms = isset($_POST['bathrooms']) ? intval($_POST['bathrooms']) : null;
    $area = isset($_POST['area']) ? floatval($_POST['area']) : null;
    $plot_size = isset($_POST['plot_size']) ? floatval($_POST['plot_size']) : null;
    $floor = isset($_POST['floor']) ? intval($_POST['floor']) : null;
    $furnishing = isset($_POST['furnishing']) ? sanitizeInput($_POST['furnishing']) : null;
    $verification_doc = $property['verification_doc']; // Keep existing doc by default

    // Basic validation
    if (empty($title)) $errors[] = "Title is required";
    if (empty($price)) $errors[] = "Price is required";
    if (empty($property_type)) $errors[] = "Property type is required";
    if (empty($transaction_type)) $errors[] = "Transaction type is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($state)) $errors[] = "State is required";
    if (empty($location)) $errors[] = "Location is required";

    // Check if new verification document was uploaded
    if (isset($_FILES['verification_doc']) && $_FILES['verification_doc']['error'] === UPLOAD_ERR_OK) {
        $doc_file = $_FILES['verification_doc'];
        $doc_ext = strtolower(pathinfo($doc_file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        
        if (!in_array($doc_ext, $allowed_ext)) {
            $errors[] = "Invalid document format. Only PDF, JPG, and PNG files are allowed.";
        } else {
            $upload_dir = 'public/uploads/documents/';
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    $errors[] = "Failed to create upload directory.";
                }
            }

            if (empty($errors)) {
                $new_doc_name = uniqid() . '.' . $doc_ext;
                $target_path = $upload_dir . $new_doc_name;

                if (!move_uploaded_file($doc_file['tmp_name'], $target_path)) {
                    $errors[] = "Failed to upload verification document.";
                } else {
                    // Delete old document if exists
                    if ($property['verification_doc'] && file_exists($property['verification_doc'])) {
                        unlink($property['verification_doc']);
                    }
                    $verification_doc = $target_path;
                }
            }
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE properties SET 
                title = ?, description = ?, price = ?, property_type = ?, 
                transaction_type = ?, city = ?, state = ?, location = ?, 
                bedrooms = ?, bathrooms = ?, area = ?, plot_size = ?, 
                floor = ?, furnishing = ?, verification_doc = ? 
                WHERE id = ? AND seller_id = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssdssssssidddssii", 
                $title, $description, $price, $property_type,
                $transaction_type, $city, $state, $location, $bedrooms, $bathrooms,
                $area, $plot_size, $floor, $furnishing, $verification_doc,
                $property_id, $_SESSION['user_id']);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                
                // Handle new image uploads
                if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                    $upload_dir = 'public/uploads/properties/';
                    if (!file_exists($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            $errors[] = "Failed to create image upload directory.";
                        }
                    }

                    if (empty($errors)) {
                        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                                $file_name = $_FILES['images']['name'][$key];
                                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                $new_file_name = uniqid() . '.' . $file_ext;
                                $target_path = $upload_dir . $new_file_name;

                                if (move_uploaded_file($tmp_name, $target_path)) {
                                    $image_sql = "INSERT INTO property_images (property_id, image_path) VALUES (?, ?)";
                                    if ($img_stmt = mysqli_prepare($conn, $image_sql)) {
                                        mysqli_stmt_bind_param($img_stmt, "is", $property_id, $target_path);
                                        mysqli_stmt_execute($img_stmt);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Real Estate Platform</title>
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
        <h1 style="text-align: center;">Edit Property</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Property updated successfully! <a href="property.php?id=<?php echo $property_id; ?>">View Property</a>
            </div>
        <?php endif; ?>

        <form action="edit_property.php?id=<?php echo $property_id; ?>" method="POST" enctype="multipart/form-data" class="property-form">
            <div class="form-group">
                <label for="title">Property Title</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($property['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($property['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select id="property_type" name="property_type" class="form-control" required>
                        <option value="">Select Property Type</option>
                        <option value="flat" <?php echo $property['property_type'] == 'flat' ? 'selected' : ''; ?>>Flat/Apartment</option>
                        <option value="house" <?php echo $property['property_type'] == 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="plot" <?php echo $property['property_type'] == 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="bungalow" <?php echo $property['property_type'] == 'bungalow' ? 'selected' : ''; ?>>Bungalow</option>
                        <option value="room" <?php echo $property['property_type'] == 'room' ? 'selected' : ''; ?>>Room</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transaction_type">Transaction Type</label>
                    <select id="transaction_type" name="transaction_type" class="form-control" required>
                        <option value="">Select Transaction Type</option>
                        <option value="sale" <?php echo $property['transaction_type'] == 'sale' ? 'selected' : ''; ?>>For Sale</option>
                        <option value="rent" <?php echo $property['transaction_type'] == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo $property['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="area">Area (sq ft)</label>
                    <input type="number" id="area" name="area" class="form-control" step="0.01" value="<?php echo $property['area']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($property['city']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" value="<?php echo htmlspecialchars($property['state']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="location">Location/Address</label>
                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($property['location']); ?>" required>
            </div>

            <!-- Dynamic fields based on property type -->
            <div id="property-specific-fields">
                <!-- Fields for Flat/Apartment -->
                <div class="property-type-fields" data-type="flat">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="bedrooms">Bedrooms</label>
                            <input type="number" id="bedrooms" name="bedrooms" class="form-control" min="0" value="<?php echo $property['bedrooms']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bathrooms">Bathrooms</label>
                            <input type="number" id="bathrooms" name="bathrooms" class="form-control" min="0" value="<?php echo $property['bathrooms']; ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="floor">Floor Number</label>
                            <input type="number" id="floor" name="floor" class="form-control" min="0" value="<?php echo $property['floor']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="furnishing">Furnishing</label>
                            <select id="furnishing" name="furnishing" class="form-control" required>
                                <option value="">Select Furnishing</option>
                                <option value="furnished" <?php echo $property['furnishing'] == 'furnished' ? 'selected' : ''; ?>>Furnished</option>
                                <option value="semi-furnished" <?php echo $property['furnishing'] == 'semi-furnished' ? 'selected' : ''; ?>>Semi-Furnished</option>
                                <option value="unfurnished" <?php echo $property['furnishing'] == 'unfurnished' ? 'selected' : ''; ?>>Unfurnished</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Fields for Plot -->
                <div class="property-type-fields" data-type="plot">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="plot_size">Plot Size (sq ft)</label>
                            <input type="number" id="plot_size" name="plot_size" class="form-control" min="0" value="<?php echo $property['plot_size']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="plot_type">Plot Type</label>
                            <select id="plot_type" name="plot_type" class="form-control" required>
                                <option value="">Select Plot Type</option>
                                <option value="residential" <?php echo $property['plot_type'] == 'residential' ? 'selected' : ''; ?>>Residential</option>
                                <option value="commercial" <?php echo $property['plot_type'] == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="agricultural" <?php echo $property['plot_type'] == 'agricultural' ? 'selected' : ''; ?>>Agricultural</option>
                                <option value="industrial" <?php echo $property['plot_type'] == 'industrial' ? 'selected' : ''; ?>>Industrial</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Fields for Room -->
                <div class="property-type-fields" data-type="room">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="room_type">Room Type</label>
                            <select id="room_type" name="room_type" class="form-control" required>
                                <option value="">Select Room Type</option>
                                <option value="single" <?php echo $property['room_type'] == 'single' ? 'selected' : ''; ?>>Single Room</option>
                                <option value="double" <?php echo $property['room_type'] == 'double' ? 'selected' : ''; ?>>Double Room</option>
                                <option value="shared" <?php echo $property['room_type'] == 'shared' ? 'selected' : ''; ?>>Shared Room</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bathroom_attached">Bathroom Attached</label>
                            <select id="bathroom_attached" name="bathroom_attached" class="form-control" required>
                                <option value="">Select Option</option>
                                <option value="yes" <?php echo $property['bathroom_attached'] == 'yes' ? 'selected' : ''; ?>>Yes</option>
                                <option value="no" <?php echo $property['bathroom_attached'] == 'no' ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="images">Additional Property Images</label>
                <input type="file" id="images" name="images[]" class="form-control" multiple accept="image/*">
                <small class="form-text text-muted">You can select multiple images (Maximum 5 images)</small>
            </div>

            <div class="form-group">
                <label for="verification_doc">Property Verification Document (7/12, Sale Deed, etc.)</label>
                <input type="file" id="verification_doc" name="verification_doc" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text text-muted">Upload property verification document (PDF, JPG, PNG)</small>
            </div>

            <button type="submit" class="btn btn-primary">Update Property</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.property-form');
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Show/hide property-specific fields based on property type
            const propertyTypeSelect = document.getElementById('property_type');
            const propertySpecificFields = document.getElementById('property-specific-fields');
            
            function updatePropertyFields() {
                const selectedType = propertyTypeSelect.value;
                const fields = propertySpecificFields.querySelectorAll('.property-type-fields');
                
                fields.forEach(field => {
                    if (field.dataset.type === selectedType) {
                        field.style.display = 'block';
                        // Make all fields required
                        field.querySelectorAll('input, select').forEach(input => {
                            input.required = true;
                        });
                    } else {
                        field.style.display = 'none';
                        // Remove required attribute from hidden fields
                        field.querySelectorAll('input, select').forEach(input => {
                            input.required = false;
                        });
                    }
                });
            }
            
            propertyTypeSelect.addEventListener('change', updatePropertyFields);
            updatePropertyFields(); // Initial call
            
            // Form submission handling
            form.addEventListener('submit', function(e) {
                // Validate file sizes
                const verificationDoc = document.getElementById('verification_doc');
                const images = document.getElementById('images');
                
                if (verificationDoc.files.length > 0) {
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    if (verificationDoc.files[0].size > maxSize) {
                        e.preventDefault();
                        alert('Verification document must be less than 5MB');
                        return;
                    }
                }
                
                if (images.files.length > 0) {
                    const maxSize = 5 * 1024 * 1024; // 5MB per image
                    for (let i = 0; i < images.files.length; i++) {
                        if (images.files[i].size > maxSize) {
                            e.preventDefault();
                            alert('Each image must be less than 5MB');
                            return;
                        }
                    }
                }
                
                // Disable submit button to prevent double submission
                submitButton.disabled = true;
                submitButton.textContent = 'Updating...';
            });
            
            // Show file names after selection
            const fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const fileName = this.files[0]?.name || 'No file chosen';
                    const label = this.nextElementSibling;
                    if (label && label.classList.contains('form-text')) {
                        label.textContent = fileName;
                    }
                });
            });
        });
    </script>
</body>
</html> 