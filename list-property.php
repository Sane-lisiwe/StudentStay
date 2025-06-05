<?php 
include 'includes/header.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Landlord') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

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

  
    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = __DIR__ . '/uploads/properties/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . '_' . $_FILES['images']['name'][$key];
            $file_path = $upload_dir . $file_name;
            
            if (!move_uploaded_file($tmp_name, $file_path)) {
                $error = "Failed to upload image: " . $_FILES['images']['error'][$key];
                error_log("Upload failed: " . error_get_last()['message']);
                break;
            }
            $images[] = $file_name;
        }
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO properties (landlord_id, name, description, price, location, university, campus, room_type, available_rooms, amenities, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        
        $stmt->execute([
            $_SESSION['user_id'],
            $name,
            $description,
            $price,
            $location,
            $university,
            $campus,
            $room_type,
            $available_rooms,
            $amenities,
            json_encode($images)
        ]);

        $success = 'Property listed successfully!';
        header('refresh:2;url=dashboard.php');
    } catch(PDOException $e) {
        $error = 'Failed to list property. Please try again.';
    }
}
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6">List Your Property</h2>

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
                    <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price (per month)</label>
                        <input type="number" name="price" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Available Rooms</label>
                        <input type="number" name="available_rooms" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">University</label>
                        <input type="text" name="university" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Campus</label>
                        <input type="text" name="campus" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Room Type</label>
                    <select name="room_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="Single">Single Room</option>
                        <option value="Shared">Shared Room</option>
                        <option value="Apartment">Apartment</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Amenities</label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="amenities[]" value="wifi" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2">WiFi</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="amenities[]" value="parking" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2">Parking</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="amenities[]" value="security" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2">Security</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="amenities[]" value="laundry" class="rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2">Laundry</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Property Images</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="mt-1 block w-full">
                    <p class="mt-1 text-sm text-gray-500">You can upload multiple images</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        List Property
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 