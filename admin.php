<?php
session_start();

//only admin users
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: index.php');
    exit();
}

$success_message = '';
$error_message = '';

$cars_file = 'cars.json';
if (file_exists($cars_file)) {
    $cars_data = file_get_contents($cars_file);
    $cars = json_decode($cars_data, true);
    if (!is_array($cars)) {
        $cars = [];
    }
} else {
    $cars = [];
}

// adding and editing 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = (int)$_POST['year'];
    $transmission = $_POST['transmission'];
    $fuel_type = $_POST['fuel_type'];
    $passengers = (int)$_POST['passengers'];
    $daily_price_huf = (int)$_POST['daily_price_huf'];
    $image = $_POST['image'];

    if (empty($brand) || empty($model) || empty($year) || empty($transmission) ||
        empty($fuel_type) || empty($passengers) || empty($daily_price_huf) || empty($image)) {
        $error_message = "All fields are required!";
    } else {
        if ($id) {
            // Edit existing car
            foreach ($cars as &$car) {
                if ($car['id'] === $id) {
                    $car['brand'] = $brand;
                    $car['model'] = $model;
                    $car['year'] = $year;
                    $car['transmission'] = $transmission;
                    $car['fuel_type'] = $fuel_type;
                    $car['passengers'] = $passengers;
                    $car['daily_price_huf'] = $daily_price_huf;
                    $car['image'] = $image;
                    break;
                }
            }
            $success_message = "Car updated successfully!";
        } else {
            // Add new car
            $new_car = [
                'id' => count($cars) > 0 ? max(array_column($cars, 'id')) + 1 : 1,
                'brand' => $brand,
                'model' => $model,
                'year' => $year,
                'transmission' => $transmission,
                'fuel_type' => $fuel_type,
                'passengers' => $passengers,
                'daily_price_huf' => $daily_price_huf,
                'image' => $image
            ];
            $cars[] = $new_car;
            $success_message = "Car added successfully!";
        }

        // Save 
        if (!file_put_contents($cars_file, json_encode($cars, JSON_PRETTY_PRINT))) {
            $error_message = "Error saving car data!";
        }
    }
}

// deleting a car
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $cars = array_filter($cars, fn($car) => $car['id'] !== $delete_id);
    $cars = array_values($cars);
    if (file_put_contents($cars_file, json_encode($cars, JSON_PRETTY_PRINT))) {
        $success_message = "Car deleted successfully!";
    } else {
        $error_message = "Error deleting car!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals - Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <button class="nav-button" onclick="window.location.href='profile.php'">My Profile</button>
        <button class="nav-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="admin-container">
        <?php if ($success_message): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="admin-grid">
            <div class="admin-section">
                <h2>Current Cars</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Year</th>
                                <th>Fuel Type</th>
                                <th>Passengers</th>
                                <th>Transmission</th>
                                <th>Daily Price (HUF)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <td><?= htmlspecialchars($car['brand']) ?></td>
                                    <td><?= htmlspecialchars($car['model']) ?></td>
                                    <td><?= htmlspecialchars($car['year']) ?></td>
                                    <td><?= htmlspecialchars($car['fuel_type']) ?></td>
                                    <td><?= htmlspecialchars($car['passengers']) ?></td>
                                    <td><?= htmlspecialchars($car['transmission']) ?></td>
                                    <td><?= number_format($car['daily_price_huf']) ?></td>
                                    <td>
                                        <a href="?edit_id=<?= $car['id'] ?>">Edit</a> |
                                        <a href="?delete_id=<?= $car['id'] ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-section">
                <h2><?= isset($_GET['edit_id']) ? "Edit Car" : "Add New Car" ?></h2>
                <form method="POST" class="admin-form">
                    <?php if (isset($_GET['edit_id'])): ?>
                        <?php $edit_id = (int)$_GET['edit_id']; ?>
                        <input type="hidden" name="id" value="<?= $edit_id ?>">
                        <?php $edit_car = array_filter($cars, fn($car) => $car['id'] === $edit_id)[0] ?? null; ?>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="brand">Brand:</label>
                        <input type="text" id="brand" name="brand" value="<?= $edit_car['brand'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="model">Model:</label>
                        <input type="text" id="model" name="model" value="<?= $edit_car['model'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="year">Year:</label>
                        <input type="number" id="year" name="year" min="1900" max="2025" value="<?= $edit_car['year'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="transmission">Transmission:</label>
                        <select id="transmission" name="transmission">
                            <option value="Manual" <?= (isset($edit_car) && $edit_car['transmission'] === "Manual") ? 'selected' : '' ?>>Manual</option>
                            <option value="Automatic" <?= (isset($edit_car) && $edit_car['transmission'] === "Automatic") ? 'selected' : '' ?>>Automatic</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fuel_type">Fuel Type:</label>
                        <select id="fuel_type" name="fuel_type">
                            <option value="Petrol" <?= (isset($edit_car) && $edit_car['fuel_type'] === "Petrol") ? 'selected' : '' ?>>Petrol</option>
                            <option value="Diesel" <?= (isset($edit_car) && $edit_car['fuel_type'] === "Diesel") ? 'selected' : '' ?>>Diesel</option>
                            <option value="Electric" <?= (isset($edit_car) && $edit_car['fuel_type'] === "Electric") ? 'selected' : '' ?>>Electric</option>
                            <option value="Hybrid" <?= (isset($edit_car) && $edit_car['fuel_type'] === "Hybrid") ? 'selected' : '' ?>>Hybrid</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="passengers">Number of Passengers:</label>
                        <input type="number" id="passengers" name="passengers" min="1" max="9" value="<?= $edit_car['passengers'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="daily_price_huf">Daily Price (HUF):</label>
                        <input type="number" id="daily_price_huf" name="daily_price_huf" min="1000" value="<?= $edit_car['daily_price_huf'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Image URL:</label>
                        <input type="url" id="image" name="image" value="<?= $edit_car['image'] ?? '' ?>">
                    </div>

                    <button type="submit" class="submit-button"><?= isset($edit_car) ? "Update Car" : "Add Car" ?></button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
