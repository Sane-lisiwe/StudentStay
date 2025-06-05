<?php 
include 'includes/header.php';
require_once 'config/check_permissions.php';

// Move the permission check to a function that only logs errors
function checkUploadDirectories() {
    $permission_errors = check_directory_permissions();
    if (!empty($permission_errors)) {
        foreach ($permission_errors as $error) {
            error_log("Directory Permission Error: " . $error);
        }
    }
}

// Run the check silently
checkUploadDirectories();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle property deletion for landlords
if (isset($_POST['delete_property']) && $_SESSION['role'] === 'Landlord') {
    $property_id = (int)$_POST['property_id'];
    
    // Verify the property belongs to this landlord
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND landlord_id = ?");
    $stmt->execute([$property_id, $_SESSION['user_id']]);
    $property = $stmt->fetch();
    
    if ($property) {
        // Delete property images
        $images = json_decode($property['images'], true);
        foreach ($images as $image) {
            $image_path = __DIR__ . '/uploads/properties/' . $image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete property from database
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$property_id]);
    }
}

// Handle bookmark removal for students
if (isset($_POST['remove_bookmark']) && $_SESSION['role'] === 'Student') {
    $bookmark_id = (int)$_POST['bookmark_id'];
    $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE id = ? AND student_id = ?");
    $stmt->execute([$bookmark_id, $_SESSION['user_id']]);
}

// Fetch user's data based on role
if ($_SESSION['role'] === 'Landlord') {
    // Fetch landlord's properties
    $stmt = $pdo->prepare("
        SELECT p.*, 
               (SELECT COUNT(*) FROM reviews r WHERE r.property_id = p.id) as review_count,
               (SELECT AVG(rating) FROM reviews r WHERE r.property_id = p.id) as avg_rating
        FROM properties p 
        WHERE p.landlord_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $properties = $stmt->fetchAll();
} else {
    // Fetch student's bookmarked properties
    $stmt = $pdo->prepare("
        SELECT b.id as bookmark_id, p.*, u.full_name as landlord_name 
        FROM bookmarks b 
        JOIN properties p ON b.property_id = p.id 
        JOIN users u ON p.landlord_id = u.id 
        WHERE b.student_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookmarks = $stmt->fetchAll();
}

// Fetch recent properties for students to browse
if ($_SESSION['role'] === 'Student') {
    $stmt = $pdo->prepare("
        SELECT p.*, u.full_name as landlord_name,
        CASE WHEN b.id IS NOT NULL THEN 1 ELSE 0 END as is_bookmarked
        FROM properties p 
        JOIN users u ON p.landlord_id = u.id
        LEFT JOIN bookmarks b ON b.property_id = p.id AND b.student_id = ?
        ORDER BY p.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_properties = $stmt->fetchAll();
}
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Dashboard Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        <?php echo $_SESSION['role'] === 'Landlord' ? 'Manage Properties' : 'My Bookmarks'; ?>
                    </h1>
                    <?php if ($_SESSION['role'] === 'Landlord'): ?>
                        <a href="list-property.php" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">
                            Add New Property
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <?php if ($_SESSION['role'] === 'Landlord'): ?>
                    <!-- Landlord Properties -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($properties as $property): ?>
                            <div class="bg-white rounded-lg shadow-sm border">
                                <div class="h-48 overflow-hidden">
                                    <?php 
                                    $images = json_decode($property['images'], true);
                                    $image = !empty($images) ? 'uploads/properties/' . $images[0] : 'images/placeholder.jpg';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($image); ?>" 
                                         alt="<?php echo htmlspecialchars($property['name']); ?>" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold mb-2">
                                        <?php echo htmlspecialchars($property['name']); ?>
                                    </h3>
                                    <p class="text-gray-600 mb-2">
                                        <?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="text-xl font-bold text-primary">
                                            R<?php echo number_format($property['price']); ?>
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <?php echo $property['review_count']; ?> reviews
                                            <?php if ($property['avg_rating']): ?>
                                                (<?php echo number_format($property['avg_rating'], 1); ?> ★)
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <a href="edit-property.php?id=<?php echo $property['id']; ?>" 
                                           class="text-primary hover:text-primary-dark">
                                            Edit
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this property?');">
                                            <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                                            <button type="submit" name="delete_property" class="text-red-600 hover:text-red-800">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (empty($properties)): ?>
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500 text-lg">You haven't listed any properties yet.</p>
                                <a href="list-property.php" class="text-primary hover:text-primary-dark mt-2 inline-block">
                                    List your first property
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Student Bookmarks -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-2xl font-bold text-gray-900">My Bookmarks</h2>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($bookmarks as $bookmark): ?>
                                    <div class="bg-white rounded-lg shadow-sm border">
                                        <div class="h-48 overflow-hidden">
                                            <?php 
                                            $images = json_decode($bookmark['images'], true);
                                            $image = !empty($images) ? 'uploads/properties/' . $images[0] : 'images/placeholder.jpg';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                                 alt="<?php echo htmlspecialchars($bookmark['name']); ?>" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="p-4">
                                            <h3 class="text-lg font-semibold mb-2">
                                                <a href="property-details.php?id=<?php echo $bookmark['id']; ?>" 
                                                   class="text-primary hover:text-primary-dark">
                                                    <?php echo htmlspecialchars($bookmark['name']); ?>
                                                </a>
                                            </h3>
                                            <p class="text-gray-600 mb-2">
                                                <?php echo htmlspecialchars($bookmark['location']); ?>
                                            </p>
                                            <div class="flex justify-between items-center mb-4">
                                                <span class="text-xl font-bold text-primary">
                                                    R<?php echo number_format($bookmark['price']); ?>
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    By <?php echo htmlspecialchars($bookmark['landlord_name']); ?>
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <a href="property-details.php?id=<?php echo $bookmark['id']; ?>" 
                                                   class="text-primary hover:text-primary-dark">
                                                    View Details
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Remove this bookmark?');">
                                                    <input type="hidden" name="bookmark_id" value="<?php echo $bookmark['bookmark_id']; ?>">
                                                    <button type="submit" name="remove_bookmark" class="text-red-600 hover:text-red-800">
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (empty($bookmarks)): ?>
                                    <div class="col-span-full text-center py-12">
                                        <p class="text-gray-500 text-lg">You haven't bookmarked any properties yet.</p>
                                        <a href="search.php" class="text-primary hover:text-primary-dark mt-2 inline-block">
                                            Browse properties
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Browse More Properties -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h2 class="text-2xl font-bold text-gray-900">Browse Properties</h2>
                                <a href="search.php" class="text-primary hover:text-primary-dark">
                                    View All Properties →
                                </a>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($recent_properties as $property): ?>
                                    <div class="bg-white rounded-lg shadow-sm border">
                                        <div class="h-48 overflow-hidden">
                                            <?php 
                                            $images = json_decode($property['images'], true);
                                            $image = !empty($images) ? 'uploads/properties/' . $images[0] : 'images/placeholder.jpg';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                                 alt="<?php echo htmlspecialchars($property['name']); ?>" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <div class="p-4">
                                            <h3 class="text-lg font-semibold mb-2">
                                                <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                                   class="text-primary hover:text-primary-dark">
                                                    <?php echo htmlspecialchars($property['name']); ?>
                                                </a>
                                            </h3>
                                            <p class="text-gray-600 mb-2">
                                                <?php echo htmlspecialchars($property['location']); ?>
                                            </p>
                                            <div class="flex justify-between items-center mb-4">
                                                <span class="text-xl font-bold text-primary">
                                                    R<?php echo number_format($property['price']); ?>
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    By <?php echo htmlspecialchars($property['landlord_name']); ?>
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                                   class="text-primary hover:text-primary-dark">
                                                    View Details
                                                </a>
                                                <?php if ($property['is_bookmarked']): ?>
                                                    <span class="text-gray-500">
                                                        <i class="fas fa-bookmark"></i> Bookmarked
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="text-center mt-8">
                                <a href="search.php" 
                                   class="inline-block bg-primary text-white px-6 py-3 rounded-md hover:bg-primary-dark">
                                    Browse All Properties
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 