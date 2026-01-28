<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../includes/config.php';

// Get categories for dropdown
$conn = getDBConnection();
$categories_result = $conn->query("SELECT id, name, type FROM categories ORDER BY type, name");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $description = trim($_POST['description']);

    $errors = [];

    if (empty($name)) {
        $errors[] = 'Item name is required.';
    }

    if ($category_id <= 0) {
        $errors[] = 'Please select a category.';
    }

    if ($quantity < 0) {
        $errors[] = 'Quantity cannot be negative.';
    }

    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero.';
    }

    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO items (name, category_id, quantity, price, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siids", $name, $category_id, $quantity, $price, $description);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: dashboard.php?message=Item added successfully');
            exit;
        } else {
            $errors[] = 'Failed to add item. Please try again.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - Vegetable & Fruit Stock Manager</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #27ae60;
            color: white;
        }

        .btn-primary:hover {
            background-color: #219a52;
        }

        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: #fdf2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .category-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .category-option {
            flex: 1;
            min-width: 200px;
        }

        .category-option label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
    </style>
</head>
<body>
    <header>
        <h1>Add New Item</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="../index.php">Home</a>
            <a href="dashboard.php?logout=1">Logout</a>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <h2>Add New Stock Item</h2>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Item Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>Category:</label>
                    <div class="category-group">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-option">
                                <label>
                                    <input type="radio" name="category_id" value="<?php echo $category['id']; ?>"
                                           <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'checked' : ''; ?> required>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <span class="category-badge category-<?php echo $category['type']; ?>">
                                        <?php echo ucfirst($category['type']); ?>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="0" value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : '0'; ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price ($):</label>
                    <input type="number" id="price" name="price" min="0" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Vegetable & Fruit Stock Manager</p>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
