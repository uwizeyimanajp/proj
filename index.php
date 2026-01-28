<?php
// Landing Page for Vegetable and Fruit Stock Management System

// Connect to database to show some stats
require_once 'includes/config.php';
require_once 'whatsapp_config.php';
$stats = ['vegetables' => 0, 'fruits' => 0, 'total_items' => 0, 'low_stock' => 0];
$conn = null;
$dbAvailable = false;

try {
    $conn = getDBConnection();
    $dbAvailable = true;

    $result = $conn->query("SELECT COUNT(*) as total FROM items");
    $stats['total_items'] = $result->fetch_assoc()['total'];

    $result = $conn->query("SELECT COUNT(*) as count FROM items i JOIN categories c ON i.category_id = c.id WHERE c.type = 'vegetable'");
    $stats['vegetables'] = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT COUNT(*) as count FROM items i JOIN categories c ON i.category_id = c.id WHERE c.type = 'fruit'");
    $stats['fruits'] = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT COUNT(*) as count FROM items WHERE quantity < 20");
    $stats['low_stock'] = $result->fetch_assoc()['count'];

} catch (Exception $e) {
    // Database not set up yet, use default values
    $dbAvailable = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshHarvest - allVegetable & Fruit Stock Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-leaf"></i>
                <h1>FreshHarvest</h1>
            </div>
            <nav>
                <a href="#home">Murugo</a>
                <a href="#features">IWACU</a>
                <a href="#order">Order</a>
                <a href="#about">About</a>
                <a href="admin/login.php" class="admin-btn">Admin Login</a>
            </nav>
        </div>
    </header>

    <main>
        <section id="home" class="hero">
            <div class="hero-content">
                <h2>Fresh Produce Stock Management System</h2>
                <p>Manage your vegetable and fruit inventory efficiently with our comprehensive system. Keep track of stock levels, categories, and ensure freshness for your customers.</p>
                <div class="hero-buttons">
                    <a href="admin/login.php" class="btn btn-primary">Get Started</a>
                    <a href="#features" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <i class="fas fa-store"></i>
            </div>
        </section>

        <section class="stats">
            <div class="stat-card">
                <i class="fas fa-carrot vegetable-icon"></i>
                <div class="stat-number"><?php echo $stats['vegetables']; ?></div>
                <div class="stat-label">Vegetables</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-apple fruit-icon"></i>
                <div class="stat-number"><?php echo $stats['fruits']; ?></div>
                <div class="stat-label">Fruits</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-boxes"></i>
                <div class="stat-number"><?php echo $stats['total_items']; ?></div>
                <div class="stat-label">Total Items</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="stat-number"><?php echo $stats['low_stock']; ?></div>
                <div class="stat-label">Low Stock Items</div>
            </div>
        </section>

        <section id="features" class="features">
            <h2>Why Choose FreshHarvest?</h2>
            <div class="features-grid">
                <div class="feature">
                    <i class="fas fa-chart-line"></i>
                    <h3>Real-time Stock Tracking</h3>
                    <p>Monitor inventory levels in real-time with color-coded alerts for low stock items.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-tags"></i>
                    <h3>Category Management</h3>
                    <p>Organize your produce into vegetable and fruit categories for easy management.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-edit"></i>
                    <h3>Easy Management</h3>
                    <p>Add, update, and remove items with our intuitive admin dashboard.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Secure & Reliable</h3>
                    <p>Secure admin authentication ensures your inventory data stays protected.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Responsive Design</h3>
                    <p>Access your stock management system from any device, anywhere.</p>
                </div>
                <div class="feature">
                    <i class="fas fa-clock"></i>
                    <h3>Quick Setup</h3>
                    <p>Get started in minutes with our simple setup process.</p>
                </div>
            </div>
        </section>

        <!-- WhatsApp Ordering Section -->
        <section id="order" class="order-section">
            <h2><i class="fab fa-whatsapp"></i> Order Fresh Produce via WhatsApp</h2>
            <p class="order-intro">Browse our fresh selection and place your order instantly through WhatsApp for quick delivery!</p>

            <?php
            // Get available items for ordering
            $availableItems = [];
            if ($dbAvailable && $conn) {
                try {
                    $result = $conn->query("
                        SELECT i.*, c.name as category_name, c.type as category_type
                        FROM items i
                        JOIN categories c ON i.category_id = c.id
                        WHERE i.quantity > 0
                        ORDER BY c.type, i.name
                        LIMIT 12
                    ");
                    if ($result) {
                        $availableItems = $result->fetch_all(MYSQLI_ASSOC);
                    }
                } catch (Exception $e) {
                    // Database query failed, use sample data
                    $availableItems = [
                        ['name' => 'Organic Spinach', 'price' => 2.99, 'category_type' => 'vegetable', 'quantity' => 50],
                        ['name' => 'Fresh Carrots', 'price' => 1.49, 'category_type' => 'vegetable', 'quantity' => 100],
                        ['name' => 'Green Broccoli', 'price' => 3.49, 'category_type' => 'vegetable', 'quantity' => 30],
                        ['name' => 'Sweet Oranges', 'price' => 0.99, 'category_type' => 'fruit', 'quantity' => 75],
                        ['name' => 'Strawberries', 'price' => 4.99, 'category_type' => 'fruit', 'quantity' => 40],
                        ['name' => 'Bananas', 'price' => 0.59, 'category_type' => 'fruit', 'quantity' => 60],
                    ];
                }
            } else {
                // Database not available, use sample data
                $availableItems = [
                    ['name' => 'Organic Spinach', 'price' => 2.99, 'category_type' => 'vegetable', 'quantity' => 50],
                    ['name' => 'Fresh Carrots', 'price' => 1.49, 'category_type' => 'vegetable', 'quantity' => 100],
                    ['name' => 'Green Broccoli', 'price' => 3.49, 'category_type' => 'vegetable', 'quantity' => 30],
                    ['name' => 'Sweet Oranges', 'price' => 0.99, 'category_type' => 'fruit', 'quantity' => 75],
                    ['name' => 'Strawberries', 'price' => 4.99, 'category_type' => 'fruit', 'quantity' => 40],
                    ['name' => 'Bananas', 'price' => 0.59, 'category_type' => 'fruit', 'quantity' => 60],
                ];
            }

            // Close database connection if it was opened
            if ($conn) {
                $conn->close();
            }
            ?>

            <div class="products-grid">
                <?php foreach ($availableItems as $item): ?>
                    <div class="product-card">
                        <div class="product-header">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <span class="product-category <?php echo $item['category_type']; ?>">
                                <?php echo ucfirst($item['category_type']); ?>
                            </span>
                        </div>
                        <div class="product-price">
                            <span class="price">$<?php echo number_format($item['price'], 2); ?></span>
                            <span class="unit">per lb</span>
                        </div>
                        <div class="product-stock">
                            <i class="fas fa-check-circle"></i>
                            <span>In Stock (<?php echo $item['quantity']; ?> available)</span>
                        </div>
                        <a href="<?php echo generateWhatsAppOrderURL($item['name'], number_format($item['price'], 2)); ?>"
                           class="whatsapp-order-btn"
                           target="_blank">
                            <i class="fab fa-whatsapp"></i>
                            Order via WhatsApp
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-info">
                <div class="info-card">
                    <i class="fas fa-truck"></i>
                    <h4>Fast Delivery</h4>
                    <p>Same-day delivery available for orders placed before 2 PM</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Fresh Guarantee</h4>
                    <p>All produce is hand-picked and delivered fresh daily</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-headset"></i>
                    <h4>24/7 Support</h4>
                    <p>Contact us anytime via WhatsApp for assistance</p>
                </div>
            </div>

            <div class="bulk-order">
                <h3>Need Bulk Orders?</h3>
                <p>For wholesale orders or large quantities, contact us directly for special pricing.</p>
                <a href="<?php echo generateBulkOrderURL(); ?>"
                   class="whatsapp-bulk-btn"
                   target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    Contact for Bulk Orders
                </a>
            </div>
        </section>

        <section id="about" class="about">
            <div class="about-content">
                <h2>About FreshHarvest</h2>
                <p>FreshHarvest is designed specifically for produce vendors, grocery stores, and farmers markets who need to maintain accurate inventory of their fresh vegetables and fruits. Our system helps you:</p>
                <ul>
                    <li>Track stock levels to prevent stockouts</li>
                    <li>Monitor product freshness and quality</li>
                    <li>Generate insights for better purchasing decisions</li>
                    <li>Streamline operations with an easy-to-use interface</li>
                </ul>
                <div class="testimonials">
                    <div class="testimonial">
                        <p>"FreshHarvest has transformed how we manage our produce inventory. It's intuitive and saves us hours every week!"</p>
                        <cite>- Sarah Johnson, Local Grocery Store Owner</cite>
                    </div>
                    <div class="testimonial">
                        <p>"The real-time stock tracking helps us maintain freshness and reduce waste. Highly recommended!"</p>
                        <cite>- Mike Chen, Farmers Market Vendor</cite>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </section>

        <section class="cta">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of produce vendors who trust FreshHarvest for their inventory management needs.</p>
            <a href="admin/login.php" class="btn btn-primary btn-large">Access Admin Dashboard</a>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>FreshHarvest</h3>
                <p>Your trusted partner in produce inventory management.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#order">Order</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="admin/login.php">Admin Login</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Questions about FreshHarvest?<br>
                Email: support@freshtarvest.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 FreshHarvest - Vegetable & Fruit Stock Manager. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
