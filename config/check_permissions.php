<?php
function check_directory_permissions() {
    $project_root = dirname(__DIR__); // Get parent directory
    $required_directories = [
        'images',
        'uploads',
        'uploads/properties',
        'uploads/profiles'
    ];

    $errors = [];

    foreach ($required_directories as $dir) {
        $full_path = $project_root . '/' . $dir;
        
        // Check if directory exists
        if (!file_exists($full_path)) {
            if (!mkdir($full_path, 0755, true)) {
                $errors[] = "Failed to create directory: $dir";
            }
        }
        
        // Check if directory is writable
        if (!is_writable($full_path)) {
            $errors[] = "Directory is not writable: $dir";
        }
    }

    return $errors;
}

// Remove the direct output code
// Only return the function for use elsewhere 