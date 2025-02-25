<?php
session_start();

//if logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}


$cars_data = file_get_contents('cars.json');
$cars = json_decode($cars_data, true);
$users_data = file_get_contents('users.json');
$users = json_decode($users_data, true);

//current user's data
$current_user = null;
foreach ($users as $user) {
    if ($user['email'] === $_SESSION['user']['email']) {
        $current_user = $user;
        break;
    }
}


$rented_cars = [];
foreach ($current_user['rented_cars'] as $rental) {
    foreach ($cars as $car) {
        if ($car['id'] === $rental['car_id']) {
            $rented_cars[] = [
                'car' => $car,
                'from_date' => $rental['from_date'],
                'to_date' => $rental['to_date']
            ];
            break;
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals - My Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <?php if($_SESSION['user']['email'] === 'admin@ikarrental.hu'): ?>
            <button class="nav-button" onclick="window.location.href='admin.php'">Admin Panel</button>
        <?php endif; ?>
        <button class="nav-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <div class="profile-containerp">
        <h2>Welcome, <?= htmlspecialchars($current_user['fullname']) ?></h2>
        <h3>Your Rentals</h3>

        <div class="car-container">
            <?php if(!empty($rented_cars)): ?>
                <?php foreach($rented_cars as $rental): ?>
                    <div class="car-card">
                        <img src="<?=$rental['car']['image']; ?>" alt="<?php echo $rental['car']['brand'] . ' ' . $rental['car']['model']; ?>" class="car-image">
                        <div class="car-info">
                            <h3 class="car-title"><?php echo $rental['car']['brand'] . ' ' . $rental['car']['model']; ?></h3>
                            <p class="car-price">
                                From: <?= $rental['from_date'] ?><br>
                                To: <?= $rental['to_date'] ?>
                            </p>
                            <button class="book-button" onclick="window.location.href='cancel_booking.php?car_id=<?= $rental['car']['id'] ?>'">Cancel Booking</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>You haven't rented any cars yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>