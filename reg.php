<?php
session_start();
$users_data = file_get_contents('users.json');
$users = json_decode($users_data, true);

$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$rented_cars = [];
$errors = [];
$current_user = [];

if (count($_POST) > 0) {
    if (trim($fullname) === '') {
        $errors['fullname'] = 'Name field is required!';
    } else if (count(explode(' ', trim($fullname))) < 2) {
        $errors['fullname'] = 'The name should contain at least two words!';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'The e-mail address is not valid';
    } else if($email == "") {
        $errors['email'] = "Email is required!";
    }

    if(sizeof(array_filter($users, function($user) use ($email) {
        return $user['email'] === $email;
    })) != 0 ) {
        $errors['email'] = "Email already exists!";
    }

    if($confirm_password == "" || $password == "") {
        $errors['password'] = "Password cannot be empty!";
    }
    
    if ($password !== $confirm_password && $password !== "" && $confirm_password !== "") {
        $errors['passwords'] = "Passwords do not match!";
    }

    $errors = array_map(fn($e) => "<span style='color: red'> $e </span>", $errors);

    if(count($errors) == 0) {
        $users[] = [
            'fullname' => $fullname,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_admin' => false,
            'rented_cars' => $rented_cars
        ];
        if(file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT))) {
            $_SESSION['success'] = "Registration successful!";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals - Sign In</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
        <button class="nav-button" onclick="window.location.href='login.php'">Login</button>
    </div>
    
    <div class="registration-container">
        <form class="registration-form" action="" method="POST">
            <h2>Create Account</h2>
            
            <?php if (count($_POST) > 0 && count($errors) == 0): ?>
                <span style="color: green;">Successfully saved!</span><br>
            <?php endif; ?>
            
            <div class="form-groupR">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname" 
                       value="<?= $current_user["fullname"] ?? $fullname ?>">
                <?= $errors['fullname'] ?? "" ?>
            </div>
            
            <div class="form-groupR">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                       value="<?= $current_user["email"] ?? $email ?>">
                <?= $errors['email'] ?? "" ?>
            </div>
            
            <div class="form-groupR">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       value="<?= $current_user["password"] ?? $password ?>">
                <?= $errors['password'] ?? "" ?>
            </div>
            
            <div class="form-groupR">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       value="<?= $current_user["confirm_password"] ?? $confirm_password ?>">
                <?= $errors['passwords'] ?? "" ?>
            </div>
            
            <button type="submit" class="submit-buttonR">Create Account</button>
            
            <div class="form-footer">
                Already have an account? <a href="login.php">Log in</a>
            </div>
        </form>
    </div>
</body>
</html>