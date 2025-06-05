<?php
session_start();
include_once __DIR__ . '/../config/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentStay - Find Your Perfect Student Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#1e293b',
                    }
                }
            }
        }
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="index.php" class="flex items-center">
                        <span class="text-2xl font-bold text-primary">StudentStay</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="about.php" class="<?php echo $current_page === 'about.php' ? 'text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                        About
                    </a>
                    <a href="search.php" class="<?php echo $current_page === 'search.php' ? 'text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                        Properties
                    </a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-primary">
                                <span class="font-medium">
                                    <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User'; ?>
                                    <?php if(isset($_SESSION['role'])): ?>
                                        <span class="text-sm text-gray-500">(<?php echo htmlspecialchars($_SESSION['role']); ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1">
                                    <a href="dashboard.php" 
                                       class="<?php echo $current_page === 'dashboard.php' ? 'text-primary' : 'text-gray-700'; ?> block px-4 py-2 text-sm hover:bg-gray-100">
                                        Dashboard
                                    </a>
                                    <a href="logout.php" 
                                       class="text-red-600 block px-4 py-2 text-sm hover:bg-gray-100">
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="<?php echo $current_page === 'login.php' ? 'text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                            Log In
                        </a>
                        <a href="signup.php" class="border border-primary text-primary px-4 py-2 rounded-lg hover:bg-primary hover:text-white">
                            Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav> 