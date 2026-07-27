<?php
// starts session
session_start();

// checks if user is logged in, if so, send to dashboard instead
if(isset($_SESSION['user_id']))
{
    header("Location: dashboard.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Quest Tracker</title>
</head>
<section class="page-container">

    <div class="hero">
        <h1>QUESTS</h1>
        <a href="login.php" class="btn">Login</a>
        <a href="register.php" class="btn">Register</a>
        <a href="policy.php" class="policy-link">User Policy</a>
    </div>

</section>
</body>
</html>