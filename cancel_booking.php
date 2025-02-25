<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['car_id'])) {
    header('Location: profile.php');
    exit();
}

$car_id = (int)$_GET['car_id'];
$users_data = file_get_contents('users.json');
$users = json_decode($users_data, true);
$cars_data = file_get_contents('cars.json');
$cars = json_decode($cars_data, true);

// Find the car details
$car = null;
foreach ($cars as $c) {
    if ($c['id'] === $car_id) {
        $car = $c;
        break;
    }
}

if ($car === null) {
    header('Location: profile.php');
    exit();
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($users as &$user) {
        if ($user['email'] === $_SESSION['user']['email']) {
            
            foreach ($user['rented_cars'] as $key => $rental) {
                if ($rental['car_id'] === $car_id) {
                    unset($user['rented_cars'][$key]);
                    $user['rented_cars'] = array_values($user['rented_cars']); // reindex array
                    $_SESSION['user'] = $user; // Update session
                    break;
                }
            }
            break;
        }
    }

    // Save updated users data
    if (file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT))) {
        $success = true;
    } else {
        $error = 'Failed to cancel booking. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals - Cancel Booking</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <button class="nav-button" onclick="window.location.href='profile.php'">My Profile</button>
    </div>

    <div class="cancel-container">
        <?php if ($success): ?>
            <div class="success-message">
                <h2>Booking Cancelled</h2>
                <p>Your booking for the <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> has been cancelled.</p>
                <div class="button-group">
                    <button onclick="window.location.href='profile.php'" class="success-button">Back to Profile</button>
                    <button onclick="window.location.href='index.php'" class="secondary-button">Browse Cars</button>
                </div>
            </div>
        <?php else: ?>
            <h2>Cancel Booking</h2>
            <?php if ($error): ?>
                <div class="errors">
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <div class="cancel-car-preview">
                <img src="<?= htmlspecialchars($car['image']) ?>" 
                     alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
            </div>

            <p class="cancel-confirmation">
                Are you sure you want to cancel this booking?
            </p>

            <form method="POST" class="cancel-buttons">
                <button type="submit" class="cancel-confirm">Yes, Cancel Booking</button>
                <button type="button" onclick="window.location.href='profile.php'" class="cancel-deny">No, Keep Booking</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>