<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$cars_data = file_get_contents('cars.json');
$cars = json_decode($cars_data, true);
$users_data = file_get_contents('users.json');
$users = json_decode($users_data, true);
$bookings_data = file_get_contents('bookings.json');
$bookings = json_decode($bookings_data, true) ?? [];

$car_id = (int)$_GET['id'];
$car = null;
foreach ($cars as $c) {
    if ($c['id'] === $car_id) {
        $car = $c;
        break;
    }
}

if ($car === null) {
    header('Location: index.php');
    exit();
}

function isCarAvailable($car_id, $from_date, $to_date, $bookings) {
    if (empty($bookings)) return true;
    
    foreach ($bookings as $booking) {
        if ($booking['car_id'] === $car_id) {
            $rental_start = strtotime($booking['start_date']);
            $rental_end = strtotime($booking['end_date']);
            $requested_start = strtotime($from_date);
            $requested_end = strtotime($to_date);

            if (($requested_start >= $rental_start && $requested_start <= $rental_end) ||
                ($requested_end >= $rental_start && $requested_end <= $rental_end) ||
                ($requested_start <= $rental_start && $requested_end >= $rental_end)) {
                return false;
            }
        }
    }
    return true;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';

    if (empty($from_date) || empty($to_date)) {
        $errors[] = "Both dates are required";
    } else if (strtotime($from_date) < strtotime('today')) {
        $errors[] = "Start date cannot be in the past";
    } else if (strtotime($to_date) <= strtotime($from_date)) {
        $errors[] = "End date must be after start date";
    }

    if (empty($errors)) {
        if (!isCarAvailable($car_id, $from_date, $to_date, $bookings)) {
            $errors[] = "Car is not available for the selected dates";
        } else {
            $new_booking = [
                'start_date' => $from_date,
                'end_date' => $to_date,
                'user_email' => $_SESSION['user']['email'],
                'car_id' => $car_id
            ];
            
           
            $bookings[] = $new_booking;

            // save to bookings.json
            if (file_put_contents('bookings.json', json_encode($bookings, JSON_PRETTY_PRINT))) {
                //update user's rented cars
                foreach ($users as &$user) {
                    if ($user['email'] === $_SESSION['user']['email']) {
                        $user['rented_cars'][] = [
                            'car_id' => $car_id,
                            'from_date' => $from_date,
                            'to_date' => $to_date
                        ];
                        break;
                    }
                }
                
                if (file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT))) {
                    $_SESSION['user'] = $user; // update session data
                    $success = true;
                } else {
                    $errors[] = "Error updating user data";
                }
            } else {
                $errors[] = "Error saving booking";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals - Book Car</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <button class="nav-button" onclick="window.location.href='profile.php'">My Profile</button>
    </div>

    <div class="booking-container">
        <?php if ($success): ?>
            <div class="success-messageb">
                <h2>Booking Confirmed!</h2>
                <p>You have successfully booked the <?= $car['brand'] . ' ' . $car['model'] ?></p>
                <p>From: <?= $from_date ?><br>To: <?= $to_date ?></p>
                <div class="button-group">
                    <button onclick="window.location.href='profile.php'" class="success-button">View My Bookings</button>
                    <button onclick="window.location.href='index.php'" class="secondary-button">Back to Home</button>
                </div>
            </div>
        <?php else: ?>
            <h2>Book <?= $car['brand'] . ' ' . $car['model'] ?></h2>
            
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="booking-form">
                <div class="form-group">
                    <label for="from_date">From Date:</label>
                    <input type="date" id="from_date" name="from_date" 
                           min="<?= date('Y-m-d') ?>" 
                           value="<?= $_POST['from_date'] ?? '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="to_date">To Date:</label>
                    <input type="date" id="to_date" name="to_date" 
                           min="<?= date('Y-m-d') ?>" 
                           value="<?= $_POST['to_date'] ?? '' ?>" required>
                </div>

                <button type="submit" class="submit-button">Book Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>