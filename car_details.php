<?php
session_start();

$car_id = $_GET['id'] ?? null;

if (!$car_id) {
    header('Location: index.php');
    exit();
}

$cars_data = file_get_contents('cars.json');
$cars = json_decode($cars_data, true);

$car = null;
foreach($cars as $c) {
    if($c['id'] == $car_id) {
        $car = $c;
        break;
    }
}

if(!$car) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details - iKar Rentals</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <?php if(isset($_SESSION['user'])): ?>
            <button class="nav-button" onclick="window.location.href='profile.php'">My Profile</button>
            <?php if(isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                <button class="nav-button" onclick="window.location.href='admin.php'">Admin Panel</button>
            <?php endif; ?>
            <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
            <button class="nav-button" onclick="window.location.href='logout.php'">Logout</button>
        <?php else: ?>
            <button class="nav-button" onclick="window.location.href='login.php'">Login</button>
            <button class="nav-button" onclick="window.location.href='reg.php'">Sign In</button>
            <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <?php endif; ?>
    </div>

    <div class="car-details">
        <img src="<?=$car['image']; ?>" alt="<?=$car['brand'] . ' ' . $car['model']; ?>" class="detail-image">
        <div class="detail-info">
            <h1><?=$car['brand'] . ' ' . $car['model']; ?></h1>
            <p class="detail-price">Price: <?=number_format($car['daily_price_huf'])?> Ft / Day</p>
            <div class="specifications">
                <p>Year: <?=$car['year']?></p>
                <p>Transmission: <?=$car['transmission']?></p>
                <p>Fuel Type: <?=$car['fuel_type']?></p>
                <p>Passengers: <?=$car['passengers']?></p>
            </div>
            <?php if(isset($_SESSION['user'])): ?>
                <button class="book-button" onclick="window.location.href='book.php?id=<?=$car['id']?>'">Book Now</button>
            <?php else: ?>
                <div class="login-prompt">
                    <p>Please log in to book this car</p>
                    <button class="book-button" onclick="window.location.href='login.php?redirect=car_details.php?id=<?=$car['id']?>'">Login to Book</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>