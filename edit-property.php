<?php 
include 'includes/header.php';

// Check if user is logged in and is a landlord
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Landlord') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch property details
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND landlord_id = ?");
$stmt->execute([$property_id, $_SESSION['user_id']]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $university = filter_input(INPUT_POST, 'university', FILTER_SANITIZE_STRING);
    $campus = filter_input(INPUT_POST, 'campus', FILTER_SANITIZE_STRING);
    $room_type = filter_input(INPUT_POST, 'room_type', FILTER_SANITIZE_STRING);
    $available_rooms = filter_input(INPUT_POST, 'available_rooms', FILTER_SANITIZE_NUMBER_INT);
    $amenities = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';
    $payment_types = isset($_POST['payment_types']) ? $_POST['payment_types'] : ['Self-Paid'];

    // Handle image upload
    $images = json_decode($property['images'], true) ?: [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = __DIR__ . '/uploads/properties/';
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . '_' . $_FILES['images']['name'][$key];
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($tmp_name, $file_path)) {
                $images[] = $file_name;
            }
        }
    }

    //  image deletions
    if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $image_index) {
            if (isset($images[$image_index])) {
                $image_path = __DIR__ . '/uploads/properties/' . $images[$image_index];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                unset($images[$image_index]);
            }
        }
        $images = array_values($images); 
    }

    try {
        $stmt = $pdo->prepare('
            UPDATE properties 
            SET name = ?, description = ?, price = ?, location = ?, 
                university = ?, campus = ?, room_type = ?, 
                available_rooms = ?, amenities = ?, images = ?, payment_types = ?
            WHERE id = ? AND landlord_id = ?
        ');
        
        $stmt->execute([
            $name,
            $description,
            $price,
            $location,
            $university,
            $campus,
            $room_type,
            $available_rooms,
            $amenities,
            json_encode($images),
            json_encode($payment_types),
            $property_id,
            $_SESSION['user_id']
        ]);

        $success = 'Property updated successfully!';
        
        
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ? AND landlord_id = ?");
        $stmt->execute([$property_id, $_SESSION['user_id']]);
        $property = $stmt->fetch();
    } catch(PDOException $e) {
        $error = 'Failed to update property. Please try again.';
    }
}


$current_amenities = json_decode($property['images'], true) ?: [];
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Property</h2>
                <a href="dashboard.php" class="text-primary hover:text-primary-dark">← Back to Dashboard</a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Property Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($property['name']); ?>" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="4" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    ><?php echo htmlspecialchars($property['description']); ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price (per month)</label>
                        <input type="number" name="price" value="<?php echo $property['price']; ?>" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Available Rooms</label>
                        <input type="number" name="available_rooms" value="<?php echo $property['available_rooms']; ?>" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">University</label>
                        <input type="text" name="university" value="<?php echo htmlspecialchars($property['university']); ?>" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Campus</label>
                        <input type="text" name="campus" value="<?php echo htmlspecialchars($property['campus']); ?>" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Room Type</label>
                    <select name="room_type" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="Single" <?php echo $property['room_type'] === 'Single' ? 'selected' : ''; ?>>Single Room</option>
                        <option value="Shared" <?php echo $property['room_type'] === 'Shared' ? 'selected' : ''; ?>>Shared Room</option>
                        <option value="Apartment" <?php echo $property['room_type'] === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Amenities</label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <?php
                        $amenities = json_decode($property['amenities'], true) ?: [];
                        $available_amenities = ['wifi', 'parking', 'security', 'laundry'];
                        foreach ($available_amenities as $amenity):
                        ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="amenities[]" value="<?php echo $amenity; ?>" 
                                    <?php echo in_array($amenity, $amenities) ? 'checked' : ''; ?>
                                    class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="ml-2"><?php echo ucfirst($amenity); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Images</label>
                    <div class="grid grid-cols-4 gap-4 mt-2">
                        <?php 
                        $images = json_decode($property['images'], true) ?: [];
                        foreach ($images as $index => $image): 
                        ?>
                            <div class="relative">
                                <img src="uploads/properties/<?php echo htmlspecialchars($image); ?>" 
                                     alt="Property image" 
                                     class="w-full h-24 object-cover rounded">
                                <label class="absolute top-0 right-0 p-1">
                                    <input type="checkbox" name="delete_images[]" value="<?php echo $index; ?>"
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Check images to delete them</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Add New Images</label>
                    <input type="file" name="images[]" multiple accept="image/*" 
                        class="mt-1 block w-full">
                    <p class="mt-1 text-sm text-gray-500">You can upload multiple new images</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Accepted Payment Types
                    </label>
                    <?php $current_payment_types = json_decode($property['payment_types'], true); ?>
                    <div class="space-y-2">
                        <div>
                            <input type="checkbox" id="self_paid" name="payment_types[]" value="Self-Paid" 
                                <?php echo in_array('Self-Paid', $current_payment_types) ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="self_paid" class="ml-2 text-gray-700">Self-Paid Students</label>
                        </div>
                        <div>
                            <input type="checkbox" id="nsfas" name="payment_types[]" value="NSFAS"
                                <?php echo in_array('NSFAS', $current_payment_types) ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="nsfas" class="ml-2 text-gray-700">NSFAS Funded</label>
                        </div>
                        <div>
                            <input type="checkbox" id="bursary" name="payment_types[]" value="Bursary"
                                <?php echo in_array('Bursary', $current_payment_types) ? 'checked' : ''; ?>
                                class="rounded border-gray-300 text-primary focus:ring-primary">
                            <label for="bursary" class="ml-2 text-gray-700">Bursary Students</label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="dashboard.php" 
                        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Update Property
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 