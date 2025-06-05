<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the project root directory
$project_root = __DIR__;

// List of required directories
$required_directories = [
    'images',
    'uploads',
    'uploads/properties',
    'uploads/profiles'
];

// Create directories 
foreach ($required_directories as $dir) {
    $full_path = $project_root . '/' . $dir;
    if (!file_exists($full_path)) {
        if (mkdir($full_path, 0755, true)) {
            echo "Created directory: $dir<br>";
        } else {
            echo "Failed to create directory: $dir<br>";
        }
    } else {
        echo "Directory exists: $dir<br>";
    }
}

// Check directory permissions
foreach ($required_directories as $dir) {
    $full_path = $project_root . '/' . $dir;
    if (is_writable($full_path)) {
        echo "Directory is writable: $dir<br>";
    } else {
        echo "Warning: Directory is not writable: $dir<br>";
    }
}

// Check PHP configuration
echo "<h3>PHP Configuration</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";

// Check Apache modules
echo "<h3>Apache Modules</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite loaded: " . (in_array('mod_rewrite', $modules) ? 'Yes' : 'No') . "<br>";
} else {
    echo "Unable to check Apache modules<br>";
}

// Test database connection
echo "<h3>Database Connection</h3>";
try {
    require_once 'config/db_connect.php';
    echo "Database connection successful<br>";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}
?>

<div style="margin-top: 20px; padding: 10px; background-color: #f0f0f0;">
    <h3>Directory Structure:</h3>
    <pre>
studentStaySAFinal/
├── images/               (for static images like villa-verdi.jpg)
├── uploads/             
│   ├── properties/      (for property images uploaded by landlords)
│   └── profiles/        (for user profile images)
├── includes/
├── config/
└── other files...
    </pre>
</div> 