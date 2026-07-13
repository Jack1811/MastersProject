<?php
// require authentication and database to run
require 'includes/auth.php';
require 'includes/db.php';

// runs when post, gets user id, skill selected, title and xp
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    // Count today's quests
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM quests
        WHERE user_id = ?
        AND DATE(created_at) = CURDATE()"
    );

    $stmt->execute([$userId]);

    $today = $stmt->fetch();
    
    $skillId = (int) $_POST['skill_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $difficulty = $_POST['difficulty'];
    switch ($difficulty)
    {
    case "Easy":
        $xpReward = 50;
        break;

    case "Medium":
        $xpReward = 100;
        break;

    case "Hard":
        $xpReward = 150;
        break;

    case "Legendary":
        $xpReward = 200;
        break;

    default:
        $xpReward = 25;
    }   

    if ($today['total'] >= 50)
    {
        die("You have already created the maximum of 50 quests today.");
        
    }
    
    // ensure valid entries and write query
    if ($title !== "" && $skillId > 0 && $xpReward > 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO quests
            (
                user_id,
                skill_id,
                title,
                xp_reward,
                description
            )
            VALUES
            (?, ?, ?, ?, ?)"
        );

        // execute query to insert quest
        $stmt->execute([
            $userId,
            $skillId,
            $title,
            $xpReward,
            $description
        ]);

        header("Location: dashboard.php");
        exit;
    }
}
// query to show skills
$skills = $pdo->query("SELECT * FROM skills");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Create Task</title>
</head>
<body>
<section class="page-container">
    
    <form method="POST" class="login-box">

        <input name="title" placeholder="Quest Title" required>

        <select name="skill_id" required>

            <?php foreach ($skills as $skill): // loop through skills so user can select one?>
                <option value="<?php echo htmlspecialchars($skill['skill_id']); ?>">
                    <?php echo htmlspecialchars($skill['skill_name']); ?>
                </option>
            <?php endforeach; ?>

        </select>

        <select name="difficulty" required>

            <option value="Easy">Easy</option>
            <option value="Medium">Medium</option>
            <option value="Hard">Hard</option>
            <option value="Legendary">Legendary</option>

        </select>

        <input type="text" name="description" placeholder="Description">

        <button type="submit">Create Quest</button>

</form>

</section>
</body>
</html>