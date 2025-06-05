<?php 
include 'includes/header.php';


$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$property_id) {
    header('Location: search.php');
    exit();
}

// Fetch property details
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name as landlord_name, u.email as landlord_email, u.phone_number as landlord_phone
    FROM properties p 
    JOIN users u ON p.landlord_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: search.php');
    exit();
}

// bookmark toggle
if (isset($_POST['toggle_bookmark']) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE student_id = ? AND property_id = ?");
    $stmt->execute([$_SESSION['user_id'], $property_id]);
    $bookmark = $stmt->fetch();

    if ($bookmark) {
        $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE student_id = ? AND property_id = ?");
        $stmt->execute([$_SESSION['user_id'], $property_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO bookmarks (student_id, property_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $property_id]);
    }
    header("Location: property-details.php?id=$property_id");
    exit();
}

// Check if property is bookmarked
$is_bookmarked = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE student_id = ? AND property_id = ?");
    $stmt->execute([$_SESSION['user_id'], $property_id]);
    $is_bookmarked = (bool)$stmt->fetch();
}

// Fetch reviews
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name 
    FROM reviews r 
    JOIN users u ON r.student_id = u.id 
    WHERE r.property_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$property_id]);
$reviews = $stmt->fetchAll();

// Add this near the top of the file with other POST handlers
if (isset($_POST['delete_review']) && isset($_SESSION['user_id'])) {
    $review_id = (int)$_POST['delete_review'];
    
    // Verify the review belongs to this user
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND student_id = ?");
    $stmt->execute([$review_id, $_SESSION['user_id']]);
    
    header("Location: property-details.php?id=$property_id");
    exit();
}
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Image Gallery -->
            <div class="h-96 relative">
                <?php 
                $images = json_decode($property['images'], true);
                $image = !empty($images) ? 'uploads/properties/' . $images[0] : 'images/placeholder.jpg';
                ?>
                <img src="<?php echo htmlspecialchars($image); ?>" 
                    alt="<?php echo htmlspecialchars($property['name']); ?>" 
                    class="w-full h-full object-cover">
            </div>

            <!-- Property Details -->
            <div class="p-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($property['name']); ?>
                        </h1>
                        <p class="text-gray-600 text-lg mb-4">
                            <?php echo htmlspecialchars($property['location']); ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-primary">
                            R<?php echo number_format($property['price']); ?> /month
                        </p>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Student'): ?>
                            <form method="POST" class="mt-2">
                                <button type="submit" name="toggle_bookmark" class="text-primary hover:text-primary-dark">
                                    <i class="fas fa-bookmark<?php echo $is_bookmarked ? ' text-primary' : ' text-gray-400'; ?>"></i>
                                    <?php echo $is_bookmarked ? 'Bookmarked' : 'Bookmark'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                    <div>
                        <h2 class="text-xl font-semibold mb-4">Property Details</h2>
                        <div class="space-y-2">
                            <p><strong>Room Type:</strong> <?php echo htmlspecialchars($property['room_type']); ?></p>
                            <p><strong>Available Rooms:</strong> <?php echo htmlspecialchars($property['available_rooms']); ?></p>
                            <p><strong>University:</strong> <?php echo htmlspecialchars($property['university']); ?></p>
                            <p><strong>Campus:</strong> <?php echo htmlspecialchars($property['campus']); ?></p>
                        </div>

                        <h2 class="text-xl font-semibold mt-6 mb-4">Amenities</h2>
                        <div class="grid grid-cols-2 gap-2">
                            <?php foreach(json_decode($property['amenities'], true) as $amenity): ?>
                                <div class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    <?php echo htmlspecialchars(ucfirst($amenity)); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold mb-4">Description</h2>
                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>

                        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-2">Contact Landlord</h3>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($property['landlord_name']); ?></p>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($property['landlord_email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($property['landlord_phone']); ?></p>
                            <?php else: ?>
                                <p class="text-gray-600">Please <a href="login.php" class="text-primary">log in</a> to view contact details</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Add this in the property details section -->
                <div class="mt-4">
                    <h3 class="text-lg font-semibold mb-2">Accepted Payment Types</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $payment_types = json_decode($property['payment_types'], true);
                        foreach ($payment_types as $type): 
                        ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                <?php echo match($type) {
                                    'NSFAS' => 'bg-green-100 text-green-800',
                                    'Bursary' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }; ?>">
                                <?php echo htmlspecialchars($type); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Review Form for logged-in students -->
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Student'): ?>
                    <div class="mt-8 border-t border-gray-200 pt-8">
                        <h3 class="text-xl font-semibold mb-4">Write a Review</h3>
                        <?php
                        // Check if user has already reviewed
                        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE student_id = ? AND property_id = ?");
                        $stmt->execute([$_SESSION['user_id'], $property_id]);
                        $existing_review = $stmt->fetch();
                        
                        if (isset($_POST['submit_review'])) {
                            $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
                            $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
                            
                            if ($rating >= 1 && $rating <= 5 && $comment) {
                                if ($existing_review) {
                                    // Update existing review
                                    $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE student_id = ? AND property_id = ?");
                                    $stmt->execute([$rating, $comment, $_SESSION['user_id'], $property_id]);
                                } else {
                                    // Create new review
                                    $stmt = $pdo->prepare("INSERT INTO reviews (student_id, property_id, rating, comment) VALUES (?, ?, ?, ?)");
                                    $stmt->execute([$_SESSION['user_id'], $property_id, $rating, $comment]);
                                }
                                // Refresh the page to show the new review
                                header("Location: property-details.php?id=$property_id");
                                exit();
                            }
                        }
                        ?>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <div class="flex space-x-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                                   class="hidden" 
                                                   <?php echo ($existing_review && $existing_review['rating'] == $i) ? 'checked' : ''; ?>>
                                            <span class="text-2xl text-gray-300 hover:text-yellow-400 peer-checked:text-yellow-400">★</span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                                <textarea name="comment" rows="4" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                ><?php echo $existing_review ? htmlspecialchars($existing_review['comment']) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="submit_review" 
                                class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark">
                                <?php echo $existing_review ? 'Update Review' : 'Submit Review'; ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Update the reviews section to show all reviews -->
                <div class="mt-12">
                    <h2 class="text-2xl font-semibold mb-6">Reviews</h2>
                    <?php if (empty($reviews)): ?>
                        <p class="text-gray-500">No reviews yet.</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach($reviews as $review): ?>
                                <div class="border-b border-gray-200 pb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <span class="font-medium"><?php echo htmlspecialchars($review['full_name']); ?></span>
                                            <span class="text-gray-500 text-sm ml-4">
                                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                            </span>
                                        </div>
                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['student_id']): ?>
                                            <div class="flex space-x-2">
                                                <button onclick="editReview(<?php echo htmlspecialchars(json_encode($review)); ?>)"
                                                    class="text-primary hover:text-primary-dark">
                                                    Edit
                                                </button>
                                                <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                                    <input type="hidden" name="delete_review" value="<?php echo $review['id']; ?>">
                                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="text-yellow-400"><?php echo $i <= $review['rating'] ? '★' : '☆'; ?></span>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($review['comment']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 