<?php
session_start();
$users_data = file_get_contents('users.json');
$users = json_decode($users_data, true);

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'The e-mail address is not valid';
    }

    $user = array_filter($users, function($user) use ($email) {
        return $user['email'] === $email;
    });

    if (empty($user)) {
        $errors['email'] = 'Email does not exist! Please register first.';
    } else {
        $user = array_shift($user);
        if (!password_verify($password, $user['password'])) {
            $errors['password'] = 'Incorrect password!';
        }
    }

    if (count($errors) == 0) {
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKar Rentals - Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="navbar">  
        <h2 class="logo">iKar Rentals</h2>    
        <button class="nav-button" onclick="window.location.href='index.php'">Home</button>
    </div>

    <div class="auth-container">
        <div class="auth-box">
            <h2>Welcome Back</h2>
            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    <?php if(isset($errors['email'])): ?>
                        <span class="error-message"><?= $errors['email'] ?></span>
                        <?php if(strpos($errors['email'], 'register') !== false): ?>
                            <a href="reg.php" class="register-link">Click here to register</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password">
                    <?php if(isset($errors['password'])): ?>
                        <span class="error-message"><?= $errors['password'] ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="auth-button">Login</button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="reg.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>