<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php?message=Item deleted successfully');
    exit;
}

// Get dashboard statistics
$conn = getDBConnection();

// Total items count
$totalItemsResult = $conn->query("SELECT COUNT(*) as total FROM items");
$totalItems = $totalItemsResult->fetch_assoc()['total'];

// Total value calculation
$valueResult = $conn->query("SELECT SUM(quantity * price) as total_value FROM items");
$totalValue = $valueResult->fetch_assoc()['total_value'] ?? 0;

// Low stock items (quantity < 20)
$lowStockResult = $conn->query("SELECT COUNT(*) as low_stock FROM items WHERE quantity < 20");
$lowStockCount = $lowStockResult->fetch_assoc()['low_stock'];

// Out of stock items
$outOfStockResult = $conn->query("SELECT COUNT(*) as out_of_stock FROM items WHERE quantity = 0");
$outOfStockCount = $outOfStockResult->fetch_assoc()['out_of_stock'];

// Category breakdown
$categoryStats = $conn->query("
    SELECT c.type, COUNT(i.id) as count
    FROM categories c
    LEFT JOIN items i ON c.id = i.category_id
    GROUP BY c.type
");
$categoryData = [];
while ($row = $categoryStats->fetch_assoc()) {
    $categoryData[$row['type']] = $row['count'];
}

// Recent items (last 5 added)
$recentItems = $conn->query("
    SELECT i.*, c.name as category_name, c.type as category_type
    FROM items i
    JOIN categories c ON i.category_id = c.id
    ORDER BY i.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Get all items with category info (for table)
$query = "SELECT i.*, c.name as category_name, c.type as category_type
          FROM items i
          JOIN categories c ON i.category_id = c.id
          ORDER BY c.type, c.name, i.name";
$result = $conn->query($query);
$items = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Get success/error messages
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FreshHarvest</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Modern Admin Dashboard Styles */
        :root {
            --primary-color: #27ae60;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: var(--white);
            box-shadow: var(--shadow);
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), #219a52);
            color: white;
            text-align: center;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-item {
            display: block;
            padding: 1rem 1.5rem;
            color: var(--dark-color);
            text-decoration: none;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.3s ease;
        }

        .menu-item:hover, .menu-item.active {
            background: var(--light-color);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        .menu-item i {
            margin-right: 0.75rem;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        /* Top Bar */
        .top-bar {
            background: var(--white);
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark-color);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .menu-toggle:hover {
            background: var(--light-color);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.vegetables { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .stat-icon.fruits { background: linear-gradient(135deg, #e67e22, #f39c12); }
        .stat-icon.total { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stat-icon.alerts { background: linear-gradient(135deg, #e74c3c, #c0392b); }

        .stat-content h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
        }

        .stat-content p {
            margin: 0;
            color: #666;
            font-weight: 500;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .chart-card h3 {
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            text-align: center;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        /* Inventory Table */
        .inventory-section {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h3 {
            margin: 0;
            color: var(--dark-color);
        }

        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e1e8ed;
            border-radius: 25px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #f1f3f4;
        }

        .inventory-table th {
            background: var(--light-color);
            font-weight: 600;
            color: var(--dark-color);
        }

        .inventory-table tr:hover {
            background: #f8f9fa;
        }

        .category-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-badge.vegetable {
            background: #d4edda;
            color: #155724;
        }

        .category-badge.fruit {
            background: #fff3cd;
            color: #856404;
        }

        .quantity-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-weight: bold;
        }

        .quantity-badge.low { background: #f8d7da; color: #721c24; }
        .quantity-badge.medium { background: #fff3cd; color: #856404; }
        .quantity-badge.high { background: #d4edda; color: #155724; }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary { background: var(--primary-color); color: white; }
        .btn-primary:hover { background: #219a52; transform: translateY(-2px); }

        .btn-secondary { background: var(--secondary-color); color: white; }
        .btn-secondary:hover { background: #2980b9; transform: translateY(-2px); }

        .btn-success { background: var(--success-color); color: white; }
        .btn-success:hover { background: #219a52; transform: translateY(-2px); }

        .btn-warning { background: var(--warning-color); color: white; }
        .btn-warning:hover { background: #e67e22; transform: translateY(-2px); }

        .btn-danger { background: var(--danger-color); color: white; }
        .btn-danger:hover { background: #c0392b; transform: translateY(-2px); }

        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }

        /* Recent Activity */
        .recent-activity {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .activity-icon.added { background: var(--success-color); }
        .activity-icon.updated { background: var(--warning-color); }
        .activity-icon.deleted { background: var(--danger-color); }

        .activity-content h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.875rem;
            color: var(--dark-color);
        }

        .activity-content p {
            margin: 0;
            font-size: 0.75rem;
            color: #666;
        }

        /* Messages */
        .message {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message i {
            font-size: 1.25rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-section {
                grid-template-columns: 1fr;
            }

            .top-bar {
                padding: 1rem;
            }

            .inventory-table {
                font-size: 0.875rem;
            }

            .inventory-table th,
            .inventory-table td {
                padding: 0.75rem 0.5rem;
            }
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-leaf"></i> FreshHarvest</h2>
            <p>Admin Dashboard</p>
        </div>
        <nav class="sidebar-menu">
            <a href="#dashboard" class="menu-item active" data-section="dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#inventory" class="menu-item" data-section="inventory">
                <i class="fas fa-boxes"></i> Inventory
            </a>
            <a href="add_item.php" class="menu-item">
                <i class="fas fa-plus"></i> Add Item
            </a>
            <a href="categories.php" class="menu-item">
                <i class="fas fa-tags"></i> Categories
            </a>
            <a href="settings.php" class="menu-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../index.php" class="menu-item">
                <i class="fas fa-home"></i> Public Site
            </a>
            <a href="?logout=1" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                    <br>
                    <small>Administrator</small>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon vegetables">
                    <i class="fas fa-carrot"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $categoryData['vegetable'] ?? 0; ?></h3>
                    <p>Vegetable Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon fruits">
                    <i class="fas fa-apple-alt"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $categoryData['fruit'] ?? 0; ?></h3>
                    <p>Fruit Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalItems; ?></h3>
                    <p>Total Items</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon alerts">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $lowStockCount; ?></h3>
                    <p>Low Stock Alerts</p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Category Distribution</h3>
                <canvas id="categoryChart" width="300" height="300"></canvas>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Inventory Value</h3>
                <div style="text-align: center; padding: 2rem;">
                    <div style="font-size: 3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">
                        $<?php echo number_format($totalValue, 2); ?>
                    </div>
                    <p style="color: #666; margin: 0;">Total Inventory Value</p>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Inventory Section -->
            <div class="inventory-section">
                <div class="section-header">
                    <h3><i class="fas fa-boxes"></i> Inventory Management</h3>
                    <a href="add_item.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Item
                    </a>
                </div>

                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search items...">
                </div>

                <div style="overflow-x: auto;">
                    <table class="inventory-table" id="inventoryTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                    <td>
                                        <span class="category-badge <?php echo $item['category_type']; ?>">
                                            <?php echo ucfirst($item['category_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="quantity-badge <?php
                                            if ($item['quantity'] < 10) echo 'low';
                                            elseif ($item['quantity'] < 50) echo 'medium';
                                            else echo 'high';
                                        ?>">
                                            <?php echo $item['quantity']; ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="action-buttons">
                                        <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this item?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($items)): ?>
                    <div style="text-align: center; padding: 3rem; color: #666;">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p>No items in inventory yet.</p>
                        <a href="add_item.php" class="btn btn-primary">Add Your First Item</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <?php if (!empty($recentItems)): ?>
                    <?php foreach ($recentItems as $item): ?>
                        <div class="activity-item">
                            <div class="activity-icon added">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="activity-content">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Added to <?php echo htmlspecialchars($item['category_name']); ?> • <?php echo date('M j, Y', strtotime($item['created_at'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const menuToggle = document.getElementById('menuToggle');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const inventoryTable = document.getElementById('inventoryTable');
        const tableRows = inventoryTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();

            for (let i = 0; i < tableRows.length; i++) {
                const row = tableRows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length - 1; j++) { // Exclude actions column
                    const cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        });

        // Category distribution chart
        const categoryData = <?php echo json_encode($categoryData); ?>;
        const ctx = document.getElementById('categoryChart').getContext('2d');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Vegetables', 'Fruits'],
                datasets: [{
                    data: [categoryData.vegetable || 0, categoryData.fruit || 0],
                    backgroundColor: ['#27ae60', '#e67e22'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Menu navigation
        const menuItems = document.querySelectorAll('.menu-item[data-section]');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.hasAttribute('data-section')) {
                    e.preventDefault();
                    menuItems.forEach(mi => mi.classList.remove('active'));
                    this.classList.add('active');

                    // Close sidebar on mobile
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                }
            });
        });
    </script>
</body>
</html>
