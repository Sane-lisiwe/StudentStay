<?php 
include 'includes/header.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    header('Location: login.php');
    exit();
}

// Add timezone setting
date_default_timezone_set('Africa/Johannesburg');

// Add this at the top after getting the token
if ($token) {
    // Simpler debug query
    $debug_stmt = $pdo->prepare("
        SELECT 
            pr.*,
            u.email,
            u.id as user_id
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE pr.token = ?
    ");
    $debug_stmt->execute([$token]);
    $debug_reset = $debug_stmt->fetch();
    
    if ($debug_reset) {
        $current_time = date('Y-m-d H:i:s');
        $expiry_time = $debug_reset['expiry'];
        $is_valid = strtotime($expiry_time) >= strtotime($current_time);
        
        error_log("Token found: " . print_r($debug_reset, true));
        error_log("Token Status: " . ($is_valid ? 'Valid' : 'Expired'));
        error_log("Current Time: " . $current_time);
        error_log("Expiry Time: " . $expiry_time);
    } else {
        error_log("No token found for: " . $token);
    }
}

// Update the verification query
$stmt = $pdo->prepare("
    SELECT 
        pr.*,
        u.email,
        u.id as user_id 
    FROM password_resets pr 
    JOIN users u ON pr.user_id = u.id 
    WHERE pr.token = ? 
    AND pr.used = 0 
    AND pr.expiry >= NOW()
    ORDER BY pr.created_at DESC 
    LIMIT 1
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    error_log("Token validation failed. Token: " . $token);
    if ($debug_reset) {
        error_log("Debug info: Expiry: " . $debug_reset['expiry'] . ", Current: " . date('Y-m-d H:i:s'));
    }
    $error = "Invalid or expired reset link. Please request a new one.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            try {
                // Start transaction
                $pdo->beginTransaction();
                
                // Update password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $reset['user_id']]);
                
                // Mark token as used
                $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                $stmt->execute([$reset['id']]);
                
                // Commit transaction
                $pdo->commit();
                
                $success = "Password has been reset successfully. You can now login with your new password.";
            } catch (Exception $e) {
                // Rollback transaction on error
                $pdo->rollBack();
                $error = "An error occurred. Please try again.";
                error_log($e->getMessage());
            }
        }
    }
}
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Reset Your Password
        </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <p class="text-red-700"><?php echo $error; ?></p>
                    <?php if ($error === "Invalid or expired reset link. Please request a new one."): ?>
                        <a href="forgot-password.php" class="text-primary hover:text-primary-dark mt-2 inline-block">
                            Request New Reset Link
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-green-700"><?php echo $success; ?></p>
                    <a href="login.php" class="text-primary hover:text-primary-dark mt-2 inline-block">
                        Go to Login
                    </a>
                </div>
            <?php elseif (!$error): ?>
                <form class="space-y-6" method="POST">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            New Password
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                minlength="8">
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            Confirm New Password
                        </label>
                        <div class="mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"
                                minlength="8">
                        </div>
                    </div>

                    <div>
                        <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Reset Password
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 