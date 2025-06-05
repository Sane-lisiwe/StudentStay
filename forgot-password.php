<?php 
include 'includes/header.php';
require 'vendor/autoload.php';
require_once 'config/mail_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

date_default_timezone_set('Africa/Johannesburg'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
          
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour', time()));
            
          
            error_log("Setting expiry time to: " . $expiry);
            
           
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (user_id, token, expiry, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user['id'], $token, $expiry]);
            
           
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/studentStaySAFinal/reset-password.php?token=" . $token;
            
           
            $mail = new PHPMailer(true);
            
            try {
                
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

              
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($email);

                
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your StudentStay Password';
                $mail->Body = "
                    <html>
                    <head>
                        <title>Reset Your Password</title>
                    </head>
                    <body>
                        <h2>Reset Your StudentStay Password</h2>
                        <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                        <p><a href='$reset_link'>Reset Password</a></p>
                        <p>If you didn't request this, please ignore this email.</p>
                        <p>Best regards,<br>StudentStay Team</p>
                    </body>
                    </html>
                ";

                $mail->send();
                $success = "Password reset instructions have been sent to your email.";
            } catch (Exception $e) {
                $error = "Failed to send reset email. Error: " . $mail->ErrorInfo;
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
        } else {
            
            $success = "If your email is registered, you will receive password reset instructions.";
        }
    } else {
        $error = "Please enter a valid email address.";
    }
}
?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Forgot Password
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Enter your email address and we'll send you instructions to reset your password.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                    <p class="text-red-700"><?php echo $error; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <p class="text-green-700"><?php echo $success; ?></p>
                </div>
            <?php endif; ?>

            <form class="space-y-6" method="POST">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required 
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Send Reset Link
                    </button>
                </div>

                <div class="text-sm text-center">
                    <a href="login.php" class="font-medium text-primary hover:text-primary-dark">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 