<?php include 'includes/header.php'; ?>

<div class="min-h-screen">
   
    <div class="relative py-24">
       
        <div class="absolute inset-0 z-0">
            <img src="images/studentStaybackground.jpg" alt="Student Accommodation" 
                 class="w-full h-full object-cover">
            
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        </div>

       
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-white">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    Find Your Perfect Student Accommodation
                </h1>
                <p class="text-xl mb-8">
                    Connecting students with quality accommodation near South African universities
                </p>
                <div class="flex justify-center space-x-4">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="signup.php" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100">
                            Get Started
                        </a>
                        <a href="search.php" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-primary">
                            Browse Properties
                        </a>
                    <?php else: ?>
                        <a href="search.php" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100">
                            Browse Properties
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

   
    <div id="how-it-works" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">1</div>
                    <h3 class="text-xl font-semibold mb-2">Search</h3>
                    <p class="text-gray-600">Browse through verified properties near your university</p>
                </div>
                <div class="text-center">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">2</div>
                    <h3 class="text-xl font-semibold mb-2">Connect</h3>
                    <p class="text-gray-600">Contact landlords directly and schedule viewings</p>
                </div>
                <div class="text-center">
                    <div class="bg-primary text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">3</div>
                    <h3 class="text-xl font-semibold mb-2">Move In</h3>
                    <p class="text-gray-600">Secure your perfect student accommodation</p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Featured Properties</h2>
                <a href="search.php" class="text-primary hover:text-primary-dark">View All →</a>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php
                
                $stmt = $pdo->query("
                    SELECT p.*, u.full_name as landlord_name 
                    FROM properties p 
                    JOIN users u ON p.landlord_id = u.id 
                    ORDER BY p.created_at DESC 
                    LIMIT 3
                ");
                $properties = $stmt->fetchAll();

                foreach ($properties as $property):
                    $images = json_decode($property['images'], true);
                    $image = !empty($images) ? 'uploads/properties/' . $images[0] : 'images/placeholder.jpg';
                ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="h-48 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($property['name']); ?>"
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold mb-2">
                                <?php echo htmlspecialchars($property['name']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($property['location']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold text-primary">
                                    R<?php echo number_format($property['price']); ?>
                                </span>
                                <a href="property-details.php?id=<?php echo $property['id']; ?>" 
                                   class="text-primary hover:text-primary-dark">
                                    View Details →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

   
    <div id="testimonials" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12">Recent Reviews</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <?php
               
                $stmt = $pdo->query("
                    SELECT r.*, u.full_name, p.name as property_name 
                    FROM reviews r 
                    JOIN users u ON r.student_id = u.id 
                    JOIN properties p ON r.property_id = p.id 
                    ORDER BY r.created_at DESC 
                    LIMIT 3
                ");
                $reviews = $stmt->fetchAll();

                foreach ($reviews as $review):
                ?>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="text-yellow-400">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-4">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                        <p class="font-semibold"><?php echo htmlspecialchars($review['full_name']); ?></p>
                        <p class="text-sm text-gray-500">
                            Review for <?php echo htmlspecialchars($review['property_name']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($reviews)): ?>
                    <div class="col-span-3 text-center text-gray-500">
                        <p>No reviews yet. Be the first to review a property!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="search.php" class="text-primary hover:text-primary-dark">
                    View All Properties and Reviews →
                </a>
            </div>
        </div>
    </div>

  
    <div class="relative py-16">
       
        <div class="absolute inset-0 z-0">
            <img src="images/studentStaybackground.jpg" alt="Student Accommodation" 
                 class="w-full h-full object-cover">
          
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        </div>

       
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Ready to Find Your Student Home?</h2>
            <p class="text-white text-xl mb-8">Join thousands of students who have found their perfect accommodation</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="signup.php" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 inline-block">
                    Get Started Today
                </a>
            <?php else: ?>
                <a href="search.php" class="bg-white text-primary px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 inline-block">
                    Browse Properties
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 