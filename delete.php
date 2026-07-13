<?php
// needs authenticator and database to ensure user exists and is logged in
require 'includes/auth.php';
require 'includes/db.php';

// session using user id
$userId = $_SESSION['user_id'];

// runs if post
if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // query to delete user
    $stmt = $pdo->prepare(
        "DELETE FROM users
         WHERE user_id=?"
    );

    // runs query
    $stmt->execute([$userId]);

    // destroys session to ensure user is logged out
    session_destroy();

    // sends user to index
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Delete Account</title>
</head>
<body>
    <h1>Delete Account</h1>

    <p> Warning: This action cannot be undone. All quests, skills, achievements and progress will be permanently removed.</p>

    <form method="POST">

    <button type="submit" onclick="return confirm('Are you sure you want to delete your account?');">Delete My Account</button>

    </form>

<br>

    <a href="index.php">Cancel</a>
</body>
</html>