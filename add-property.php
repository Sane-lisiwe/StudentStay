<!-- Add this in the property form, before the submit button -->
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Accepted Payment Types
    </label>
    <div class="space-y-2">
        <div>
            <input type="checkbox" id="self_paid" name="payment_types[]" value="Self-Paid" class="rounded border-gray-300 text-primary focus:ring-primary">
            <label for="self_paid" class="ml-2 text-gray-700">Self-Paid Students</label>
        </div>
        <div>
            <input type="checkbox" id="nsfas" name="payment_types[]" value="NSFAS" class="rounded border-gray-300 text-primary focus:ring-primary">
            <label for="nsfas" class="ml-2 text-gray-700">NSFAS Funded</label>
        </div>
        <div>
            <input type="checkbox" id="bursary" name="payment_types[]" value="Bursary" class="rounded border-gray-300 text-primary focus:ring-primary">
            <label for="bursary" class="ml-2 text-gray-700">Bursary Students</label>
        </div>
    </div>
</div>

<!-- Add this to the PHP processing section -->
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... existing validation code ...

    $payment_types = isset($_POST['payment_types']) ? $_POST['payment_types'] : ['Self-Paid'];
    
    // Update the SQL query to include payment_types
    $stmt = $pdo->prepare("
        INSERT INTO properties (
            landlord_id, name, description, price, location, 
            university, campus, room_type, available_rooms, 
            amenities, images, payment_types
        ) VALUES (
            ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, 
            ?, ?, ?
        )
    ");
    
    $stmt->execute([
        $_SESSION['user_id'], $name, $description, $price, $location,
        $university, $campus, $room_type, $available_rooms,
        json_encode($amenities), json_encode($images), json_encode($payment_types)
    ]);
}
?> 