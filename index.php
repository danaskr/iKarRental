<?php
session_start();


$cars_data = file_get_contents('cars.json');
$cars = json_decode($cars_data, true);
$users_data = file_get_contents('users.json');
$users = json_decode($users_data, true);


$seats = isset($_GET['seats']) && $_GET['seats'] !== "0" ? (int)$_GET['seats'] : "";
$geartype = isset($_GET['geartype']) && $_GET['geartype'] !== "" ? $_GET['geartype'] : "";
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : "";
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : "";
$pricerange = isset($_GET['pricerange']) ? (int)$_GET['pricerange'] : 48000;


function isCarAvailable($car_id, $from_date, $to_date, $users) {
    if (empty($from_date) || empty($to_date)) return true;
    
    foreach ($users as $user) {
        if (isset($user['rented_cars'])) {
            foreach ($user['rented_cars'] as $rental) {
                if ($rental['car_id'] === $car_id) {
                    $rental_start = strtotime($rental['from_date']);
                    $rental_end = strtotime($rental['to_date']);
                    $requested_start = strtotime($from_date);
                    $requested_end = strtotime($to_date);

                    if (($requested_start >= $rental_start && $requested_start <= $rental_end) ||
                        ($requested_end >= $rental_start && $requested_end <= $rental_end) ||
                        ($requested_start <= $rental_start && $requested_end >= $rental_end)) {
                        return false;
                    }
                }
            }
        }
    }
    return true;
}


if (isset($_GET['seats']) || isset($_GET['geartype']) || isset($_GET['from_date']) || isset($_GET['to_date']) || isset($_GET['pricerange'])) {
    $filtered_cars = array_filter($cars, function($car) use ($seats, $geartype, $pricerange, $from_date, $to_date, $users) {
        
        if ($seats !== "" && $car['passengers'] < $seats) {
            return false;
        }
        
        if ($geartype !== "" && strtolower($car['transmission']) !== strtolower($geartype)) {
            return false;
        }
        
        if ($pricerange !== "" && $car['daily_price_huf'] > $pricerange) {
            return false;
        }
        if ($from_date !== "" && $to_date !== "") {
            if (!isCarAvailable($car['id'], $from_date, $to_date, $users)) {
                return false;
            }
        }

        return true;
    });
} else {
    $filtered_cars = $cars;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <?php if(isset($_SESSION['user'])): ?>
            
            <?php if(isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin']): ?>
                <button class="nav-button" onclick="window.location.href='admin.php'">Admin Panel</button>
                <button class="nav-button" onclick="window.location.href='admin_profile.php'">Admin Profile</button>
            <?php endif; ?>
            <button class="nav-button" onclick="window.location.href='profile.php'">My Profile</button>
            <button class="nav-button" onclick="window.location.href='logout.php'">Logout</button>
        <?php else: ?>
            <button class="nav-button" onclick="window.location.href='login.php'">Login</button>
            <button class="nav-button" onclick="window.location.href='reg.php'">Sign In</button>
        <?php endif; ?>
    </div>
    
    <h2>Need to rent cars? You're in the right place!</h2> 

    <form action="index.php" method="GET" class="search-form">
        <input type="number" name="seats" class="seats-input" placeholder="0" min="0" value="<?= $seats ?>">
        <span class="label">seats</span>
        <span class="label">from</span>
        <input type="date" name="from_date" class="date-input" min="<?=date('Y-m-d')?>" value="<?= $from_date ?>">
        <span class="label">until</span>
        <input type="date" name="to_date" class="date-input" min="<?=date('Y-m-d')?>" value="<?= $to_date ?>">
        <select class="dropdown" name="geartype">
            <option value="">Select gear type</option>
            <option value="Manual" <?= $geartype === 'Manual' ? 'selected' : '' ?>>Manual</option>
            <option value="Automatic" <?= $geartype === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
        </select>
        <div class="price-range">
            <input type="range" name="pricerange" min="12000" max="100000" step="1000" value="<?= $pricerange ?>" class="slider" id="pricerange">
            <span class="label">Price: <span id="price-value"><?= $pricerange ?></span> Ft</span>
        </div>
        <button type="submit" class="filter-button">Filter</button>
    </form>

    <div class="car-container">
        <?php if(!empty($filtered_cars)): ?>
            <?php foreach($filtered_cars as $car): ?>
                <div class="car-card">
                    <a href="car_details.php?id=<?= $car['id'] ?>" class="car-link">
                        <img src="<?=$car['image']; ?>" alt="<?php echo $car['brand'] . ' ' . $car['model']; ?>" class="car-image">
                        <div class="car-info">
                            <h3 class="car-title"><?php echo $car['brand'] . ' ' . $car['model']; ?></h3>
                            <p class="car-price"><?php echo number_format($car['daily_price_huf']) . ' Ft    |  seats: ' . ($car['passengers']); ?></p>
                            <p class="view-details">Click to view details</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <p>No cars match your criteria</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        //price range value display :) 
        const pricerange = document.getElementById('pricerange');
        const priceValue = document.getElementById('price-value');
        pricerange.addEventListener('input', function() {
            priceValue.textContent = this.value;
        });
    </script>
</body>
</html>