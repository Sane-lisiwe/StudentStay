<?php 
include 'includes/header.php';

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$university = isset($_GET['university']) ? trim($_GET['university']) : '';
$room_type = isset($_GET['room_type']) ? trim($_GET['room_type']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : '';

// Build query
$query = "SELECT p.*, u.full_name as landlord_name 
          FROM properties p 
          JOIN users u ON p.landlord_id = u.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.location LIKE ? OR p.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($university) {
    $query .= " AND p.university = ?";
    $params[] = $university;
}

if ($room_type) {
    $query .= " AND p.room_type = ?";
    $params[] = $room_type;
}

if ($min_price) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}

if (isset($_GET['payment_type']) && $_GET['payment_type']) {
    $query .= " AND JSON_CONTAINS(p.payment_types, ?)";
    $params[] = json_encode($_GET['payment_type']);
}

$query .= " ORDER BY p.created_at DESC";

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Get unique universities for filter
$universities = $pdo->query("SELECT DISTINCT university FROM properties WHERE verified = 1")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <form method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="Search properties...">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">University</label>
                        <select name="university" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">All Universities</option>
                            <?php foreach($universities as $uni): ?>
                                <option value="<?php echo htmlspecialchars($uni); ?>" <?php echo $university === $uni ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($uni); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Room Type</label>
                        <select name="room_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">All Types</option>
                            <option value="Single" <?php echo $room_type === 'Single' ? 'selected' : ''; ?>>Single Room</option>
                            <option value="Shared" <?php echo $room_type === 'Shared' ? 'selected' : ''; ?>>Shared Room</option>
                            <option value="Apartment" <?php echo $room_type === 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" value="<?php echo $min_price; ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                placeholder="Min">
                            <input type="number" name="max_price" value="<?php echo $max_price; ?>" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                                placeholder="Max">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Type</label>
                        <select name="payment_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">All Payment Types</option>
                            <option value="Self-Paid" <?php echo isset($_GET['payment_type']) && $_GET['payment_type'] === 'Self-Paid' ? 'selected' : ''; ?>>Self-Paid</option>
                            <option value="NSFAS" <?php echo isset($_GET['payment_type']) && $_GET['payment_type'] === 'NSFAS' ? 'selected' : ''; ?>>NSFAS</option>
                            <option value="Bursary" <?php echo isset($_GET['payment_type']) && $_GET['payment_type'] === 'Bursary' ? 'selected' : ''; ?>>Bursary</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Search Properties
                    </button>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($properties as $property): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="h-48 overflow-hidden">
                        <?php 
                        $images = json_decode($property['images'], true);
                        $image = !empty($images) ? 'uploads/properties/' . $images[0] : 'images/placeholder.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($property['name']); ?>" 
                            class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="text-primary hover:text-primary-dark">
                                <?php echo htmlspecialchars($property['name']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($property['location']); ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-primary">R<?php echo number_format($property['price']); ?></span>
                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($property['room_type']); ?></span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($property['university']); ?>
                            </span>
                            <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                class="text-primary hover:text-primary-dark text-sm font-medium">
                                View Details →
                            </a>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <?php 
                            $payment_types = json_decode($property['payment_types'], true);
                            foreach ($payment_types as $type): 
                            ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
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
                </div>
            <?php endforeach; ?>

            <?php if (empty($properties)): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 text-lg">No properties found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 