<?php
// starts session, needs database to run
ob_start();
session_start();
require 'includes/db.php';

// checks if user is logged in, if so, send to dashboard instead
if(isset($_SESSION['user_id']))
{
    header("Location: dashboard.php");
    exit();
}

$error = "";

// Runs when post
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login = trim($_POST['login']);

    // Find user by username 
    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE username = ?
    ");

    $stmt->execute([$login]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($_POST['password'], $user['password_hash'])) {
        $error = 'The details entered are incorrect. Please try again.';
    }
    else {
        $_SESSION['user_id'] = $user['user_id'];

        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Login</title>
</head>
<body>
<section class="page-container">
    <div class="login-page">

    <form method="POST" class="login-box">

            <h1>Login</h1>

            <input name="login" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>

            <p>Not got an account?<a href="register.php" class="register-link">Register here</a></p>
            <a href="policy.php">User Policy</a>

        </form>

        <?php if($error): ?>
            <p class="error">
            <?= $error ?>
            </p>
        <?php endif; ?>
    </div>
</section>  
</body>
</html>
