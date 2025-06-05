<?php include 'includes/header.php'; ?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Hero Section -->
            <div class="relative py-16">
                <!-- Background image -->
                <div class="absolute inset-0 z-0">
                    <img src="images/about.jpg" alt="About StudentStay" 
                         class="w-full h-full object-cover">
                    <!-- Dark overlay for better text readability -->
                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                </div>

                <!-- Content -->
                <div class="relative z-10 text-center text-white">
                    <h1 class="text-4xl font-bold mb-4">About StudentStaySA</h1>
                    <p class="text-xl">Your Trusted Student Accommodation Platform in South Africa</p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="p-8">
                <!-- Mission Statement -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                    <p class="text-gray-600 text-lg">
                        StudentStaySA is dedicated to connecting South African students with quality, affordable accommodation 
                        near their universities. We strive to make the housing search process simple, transparent, and secure 
                        for both students and landlords.
                    </p>
                </div>

                <!-- What We Offer -->
                <div class="grid md:grid-cols-2 gap-8 mb-12">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">For Students</h2>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Easy search for accommodation near your university
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Verified property listings with detailed information
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Direct communication with landlords
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Bookmark and save favorite properties
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">For Landlords</h2>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                List your properties easily and efficiently
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Reach thousands of potential student tenants
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Manage your listings from a simple dashboard
                            </li>
                            <li class="flex items-start">
                                <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Receive direct inquiries from interested students
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Why Choose Us -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Why Choose StudentStaySA?</h2>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Trusted Platform</h3>
                            <p class="text-gray-600">
                                We verify all listings and landlords to ensure a safe and reliable accommodation search experience.
                            </p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Student-Focused</h3>
                            <p class="text-gray-600">
                                Our platform is specifically designed for South African students, with features that matter most to them.
                            </p>
                        </div>
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy to Use</h3>
                            <p class="text-gray-600">
                                Simple, intuitive interface makes it easy to find or list student accommodation.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Contact Section -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Get in Touch</h2>
                    <p class="text-gray-600 mb-4">
                        Have questions or suggestions? We'd love to hear from you. Contact our support team:
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center text-gray-600">
                            <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            support@studentstaysa.co.za
                        </div>
                        <div class="flex items-center text-gray-600">
                            <svg class="h-6 w-6 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            +27 (0) 12 345 6789
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 