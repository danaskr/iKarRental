<?php
session_start();

// only aadmins
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: index.php');
    exit();
}


$bookings_data = file_get_contents('bookings.json');
$bookings = json_decode($bookings_data, true) ?? [];
$cars_data = file_get_contents('cars.json');
$cars = json_decode($cars_data, true) ?? [];


$cars_lookup = [];
foreach ($cars as $car) {
    $cars_lookup[$car['id']] = $car;
}


function calculateTotalPrice($start_date, $end_date, $daily_price) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $duration = $end->diff($start)->days + 1; // to include the begining and end days
    return $duration * $daily_price;
}

// Process bookings 
$processed_bookings = [];
foreach ($bookings as $booking) {
    $car = $cars_lookup[$booking['car_id']] ?? null;
    if ($car) {
        $processed_booking = [
            'user_email' => $booking['user_email'],
            'car' => $car['brand'] . ' ' . $car['model'],
            'start_date' => $booking['start_date'],
            'end_date' => $booking['end_date'],
            'total_price' => calculateTotalPrice(
                $booking['start_date'],
                $booking['end_date'],
                $car['daily_price_huf']
            )
        ];
        $processed_bookings[] = $processed_booking;
    }
}


$admin_email = $_SESSION['user']['email'];
$admin_bookings = array_filter($processed_bookings, fn($booking) => $booking['user_email'] === $admin_email);
$other_bookings = array_filter($processed_bookings, fn($booking) => $booking['user_email'] !== $admin_email);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Bookings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <button class="nav-button" onclick="window.location.href='admin.php'">Admin Panel</button>
        <button class="nav-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="profile-container">
        <h2>Admin Profile - Bookings</h2>

        <div class="section-container">
            <h3>Your Bookings</h3>
            <div class="table-container">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($admin_bookings)): ?>
                            <?php foreach ($admin_bookings as $booking): ?>
                                <tr>
                                    <td><?= ($booking['car']) ?></td>
                                    <td><?= ($booking['start_date']) ?></td>
                                    <td><?= ($booking['end_date']) ?></td>
                                    <td><?= ($booking['total_price']) ?> HUF</td>
                                    <td><?= ($booking['end_date']) < time() ? 'Completed' : 'Active' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-container">
            <h3>All User Bookings</h3>
            <div class="table-container">
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>User Email</th>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($other_bookings)): ?>
                            <?php foreach ($other_bookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['user_email']) ?></td>
                                    <td><?= htmlspecialchars($booking['car']) ?></td>
                                    <td><?= htmlspecialchars($booking['start_date']) ?></td>
                                    <td><?= htmlspecialchars($booking['end_date']) ?></td>
                                    <td><?= number_format($booking['total_price']) ?> HUF</td>
                                    <td><?= strtotime($booking['end_date']) < time() ? 'Completed' : 'Active' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>