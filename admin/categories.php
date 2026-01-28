<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/config.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: categories.php?message=Category deleted successfully');
    exit;
}

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];

    if (empty($name) || !in_array($type, ['vegetable', 'fruit'])) {
        $error = 'Please provide a valid category name and type.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO categories (name, type) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $type);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: categories.php?message=Category added successfully');
            exit;
        } else {
            $error = 'Failed to add category.';
        }

        $stmt->close();
        $conn->close();
    }
}

// Get all categories
$conn = getDBConnection();
$query = "SELECT c.*, COUNT(i.id) as item_count FROM categories c LEFT JOIN items i ON c.id = i.category_id GROUP BY c.id ORDER BY c.type, c.name";
$result = $conn->query($query);
$categories = $result->fetch_all(MYSQLI_ASSOC);
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
    <title>Manage Categories - Vegetable & Fruit Stock Manager</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #ecf0f1;
            border-radius: 8px;
        }

        .dashboard-header h2 {
            margin: 0;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background-color: #219a52;
        }

        .category-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .category-table th, .category-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .category-table th {
            background-color: #2c3e50;
            color: white;
        }

        .category-table tr:hover {
            background-color: #f8f9fa;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .category-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-vegetable {
            background-color: #27ae60;
            color: white;
        }

        .category-fruit {
            background-color: #e67e22;
            color: white;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .add-category-form {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"], select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .form-row {
                flex-direction: column;
            }

            .category-table {
                font-size: 0.9rem;
            }

            .category-table th, .category-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Manage Categories</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="../index.php">Home</a>
            <a href="dashboard.php?logout=1">Logout</a>
        </nav>
    </header>

    <main>
        <div class="dashboard-header">
            <h2>Category Management</h2>
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="add-category-form">
            <h3>Add New Category</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Category Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type:</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="vegetable">Vegetable</option>
                            <option value="fruit">Fruit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">Add Category</button>
                    </div>
                </div>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="category-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Items Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td>
                                <span class="category-badge category-<?php echo $category['type']; ?>">
                                    <?php echo ucfirst($category['type']); ?>
                                </span>
                            </td>
                            <td><?php echo $category['item_count']; ?> items</td>
                            <td class="actions">
                                <?php if ($category['item_count'] == 0): ?>
                                    <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                <?php else: ?>
                                    <span style="color: #7f8c8d; font-size: 0.8rem;">Cannot delete (has items)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($categories)): ?>
            <p style="text-align: center; margin-top: 2rem; font-style: italic;">No categories found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 Vegetable & Fruit Stock Manager</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
