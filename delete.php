<?php
require 'includes/auth.php';
require 'includes/db.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    // Get current profile picture
    $stmt = $pdo->prepare("
        SELECT profile_picture
        FROM users
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $profilePicture = $user['profile_picture'];

    // Delete user
    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);

    // Delete image if it isn't the default
    if ($profilePicture !== 'default.png')
    {
        // Check whether anyone else is using it
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE profile_picture = ?
        ");
        $stmt->execute([$profilePicture]);

        if ($stmt->fetchColumn() == 0)
        {
            $file = "uploads/profile_pictures/" . $profilePicture;

            if (file_exists($file))
            {
                unlink($file);
            }
        }
    }

    session_destroy();

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