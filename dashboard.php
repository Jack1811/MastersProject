<?php
// require authentication and database to run
require 'includes/auth.php';
require 'includes/db.php';

$userId = $_SESSION['user_id'];

// Fetch user profile information
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

// Calculate overall experience metrics
$currentLevel = $user['level'];
$currentXp = $user['xp'];

$currentLevelXp = ($currentLevel - 1) * 250;
$nextLevelXp = $currentLevel * 250;

$progress = $currentXp - $currentLevelXp;
$needed = $nextLevelXp - $currentLevelXp;
$percentage = ($progress / $needed) * 100;

// Fetch user's skills
$stmtSkills = $pdo->prepare("
    SELECT s.skill_name, us.xp
    FROM user_skills us
    JOIN skills s ON us.skill_id = s.skill_id
    WHERE us.user_id = ?
");
$stmtSkills->execute([$userId]);
$skills = $stmtSkills->fetchAll(); // Store all skills in an array

// Fetch user quest statistics
$stmtStats = $pdo->prepare("
    SELECT
        COUNT(*) as total,
        SUM(status='Completed') as completed,
        SUM(status='Pending') as pending
    FROM quests
    WHERE user_id = ?
");
$stmtStats->execute([$userId]);
$stats = $stmtStats->fetch();

// Fetch user's pending active quests
$stmtQuests = $pdo->prepare("
    SELECT *
    FROM quests
    WHERE user_id = ? AND status = 'Pending'
");
$stmtQuests->execute([$userId]);
$activeQuests = $stmtQuests->fetchAll();

// Fetch global leaderboard top 10 players
$stmtLeaderboard = $pdo->query("
    SELECT username, level, xp
    FROM users
    ORDER BY xp DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Quest Tracker Dashboard</title>
</head>
<body>

    <div class="dashboard-layout">
        
        <aside class="sidebar">

            <nav class="nav-links">
                <a href="logout.php">Logout</a>
                <a href="delete.php" class="danger-link">Delete Account</a>
            </nav>
            
            <div class="profile-area">
                <img src="uploads/profile_pictures/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">

                <h1>
                    <a href="customise.php" class="profile-link">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </a>
                </h1>
                <br>
                <p><?php echo htmlspecialchars($user['bio']);?></p>
                <br>
            </div>

            <div class="overall-progress">
                <label>XP</label>
                <div class="progress-container">
                    <progress value="<?php echo $progress; ?>" max="<?php echo $needed; ?>"></progress>
                    <span class="xp-text"><?php echo $currentXp; ?> / <?php echo $nextLevelXp; ?> XP (<?php echo round($percentage); ?>%)</span>
                    <p class="lvl-text">Level <?php echo $currentLevel; ?></p>
                </div>
            </div>

            <div class="skills-section">
                <div class="section-header">
                    <h3>Skills</h3>
                </div>
                
                <div class="skills-list">
                    <?php if (empty($skills)): ?>
                        <p class="empty-text">No skills found.</p>
                    <?php else: ?>
                    <?php foreach ($skills as $skill): 
                        $skillXp = $skill['xp'];
                        $skillLevel = 1;
                        $skillNeeded = 250;

                        while ($skillXp >= $skillNeeded) {
                            $skillXp -= $skillNeeded;
                            $skillLevel++;
                            $skillNeeded = $skillLevel * 250;
                        }
                        
                        $skillProgress = $skillXp;

                    ?>
                            <div class="skill-item">
                                <div class="skill-info">
                                    <strong><?php echo htmlspecialchars($skill['skill_name']); ?></strong>
                                    <span><?php echo $skillProgress; ?>/<?php echo $skillNeeded; ?> XP</span>
                                </div>
                                <progress value="<?php echo $skillProgress; ?>" max="<?php echo $skillNeeded; ?>"></progress>
                                <span class="skill-level">Lvl <?php echo $skillLevel; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-section">
                <h3>Quest Statistics</h3>
                <p>Total: <?php echo $stats['total'] ?? 0; ?></p>
                <p>Completed: <?php echo $stats['completed'] ?? 0; ?></p>
                <p>Pending: <?php echo $stats['pending'] ?? 0; ?></p>
            </div>

            <section class="leaderboard-section">
                <h3>Global Leaderboard</h3>
                <div class="leaderboard-list">
                    <?php 
                    $position = 1;
                    foreach ($stmtLeaderboard as $player): 
                    ?>
                        <p>
                            <strong>
                                <?php 
                                if ($position == 1) echo "1st";
                                elseif ($position == 2) echo "2nd";
                                elseif ($position == 3) echo "3rd";
                                else echo $position . "th";
                                ?>:
                            </strong>
                            <?php echo htmlspecialchars($player['username']); ?> 
                            (Lvl <?php echo htmlspecialchars($player['level']); ?>)
                        </p>
                    <?php 
                        $position++;
                    endforeach; 
                    ?>
                </div>
            </section>


        </aside>

        <main class="main-content">
            
        
            <header class="main-header">
                <a href="create_task.php" class="btn-add-task">Add New Task</a>
            </header>

            <section class="quests-grid">
                <?php if (empty($activeQuests)): ?>
                    <div class="no-quests">
                        <p>No active tasks! Click "Add New Task" to create one.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $taskCounter = 1;
                    foreach ($activeQuests as $quest): 
                    ?>
                        <div class="task-card">
                            <div class="task-card-header">
                                Task <?php echo $taskCounter++; ?>
                            </div>
                            <div class="task-card-body">
                                <p><strong>Title:</strong> <?php echo htmlspecialchars($quest['title']); ?></p>
                                <p><strong>XP Reward:</strong> <?php echo htmlspecialchars($quest['xp_reward']); ?> XP</p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($quest['description']); ?></p>
                                
                                <a href="complete_task.php?quest_id=<?php echo urlencode($quest['quest_id']); ?>" class="btn-complete">Mark as completed</a>

                                <a href="delete_task.php?quest_id=<?php echo urlencode($quest['quest_id']); ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this quest?');">Delete Quest</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>


        </main>

    </div>

</body>
</html>
