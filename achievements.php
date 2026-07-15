<?php

require 'includes/auth.php';
require 'includes/db.php';

$userId = $_SESSION['user_id'];


// Get every achievement
$stmtAchievements = $pdo->prepare("
SELECT 
    a.*,
    ua.unlocked_at
FROM achievements a
LEFT JOIN user_achievements ua
ON a.achievement_id = ua.achievement_id
AND ua.user_id = ?

ORDER BY a.xp_reward ASC
");

$stmtAchievements->execute([$userId]);

$achievements = $stmtAchievements->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Achievements</title>
</head>
<body>

<h1>Achievements</h1>
<a href="index.php" class="btn-cancel">Go Back</a>
<div class="achievements-grid">
    <?php foreach($achievements as $achievement): ?>
    <div class="achievement-card">
        <?php if($achievement['unlocked_at']): ?>
            <img src="uploads/system/<?php echo htmlspecialchars($achievement['icon']); ?>" alt="<?php echo htmlspecialchars($achievement['title']); ?>" class="achievement-icon">
            <h2><?php echo htmlspecialchars($achievement['title']); ?></h2>
                <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                <p><strong>XP Reward:</strong><?php echo htmlspecialchars($achievement['xp_reward']); ?> XP</p>
                <small>Unlocked:<?php echo htmlspecialchars($achievement['unlocked_at']); ?></small>
            <?php else: ?>
                <img src="uploads/system/lock.png" alt="Locked Achievement" class="achievement-icon">
                <h2>Hidden Achievement</h2>
                    <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                    <small>Not unlocked yet</small>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
</body>
</html>