<?php
// requires database and authenticator to run
require 'includes/auth.php';
require 'includes/db.php';

// get quest id from the URL
$questId = $_GET['quest_id'] ?? 0;

// query to get the selected quest
$stmt = $pdo->prepare(
"
SELECT *
FROM quests
WHERE quest_id = ?
"
);

// run query using the quest id
$stmt->execute([$questId]);

$quest = $stmt->fetch();

// if quest doesn't exist, stop the script
if(!$quest)
{
    die("Quest not found");
}

// if quest is already completed, return to dashboard
if($quest['status'] === 'Completed')
{
    header("Location: dashboard.php");
    exit;
}

// query to mark the quest as completed
$pdo->prepare(
"
UPDATE quests
SET status='Completed'
WHERE quest_id=?
"
)->execute([$questId]);

// query to record the quest completion and XP earned
$pdo->prepare(
"
INSERT INTO quest_completions
(
    quest_id,
    user_id,
    xp_awarded
)
VALUES
(
    ?,
    ?,
    ?
)
"
)->execute([
    $questId,
    $quest['user_id'],
    $quest['xp_reward']
]);

// query to add the quest XP to the user's skill
$pdo->prepare(
"
UPDATE user_skills
SET xp = xp + ?
WHERE user_id = ?
AND skill_id = ?
"
)->execute([
    $quest['xp_reward'],
    $quest['user_id'],
    $quest['skill_id']
]);

// query to get the updated skill XP
$stmt = $pdo->prepare(
"
SELECT xp
FROM user_skills
WHERE user_id = ?
AND skill_id = ?
"
);

$stmt->execute([
    $quest['user_id'],
    $quest['skill_id']
]);

// get the updated skill data
$skill = $stmt->fetch();

// store the current skill XP
$skillXp = $skill['xp'];

// function to calculate level
function calculateLevel($xp)
{
    $level = 1;
    $requiredXp = 100;

    while ($xp >= $requiredXp)
    {
        $level++;
        $requiredXp += $level * 50;
    }

    return $level;
}

$skillLevel = calculateLevel($skillXp);

// query to update the user's skill level
$pdo->prepare(
"
UPDATE user_skills
SET level = ?
WHERE user_id = ?
AND skill_id = ?
"
)->execute([
    $skillLevel,
    $quest['user_id'],
    $quest['skill_id']
]);

// query to get the user's total XP across all skills
$stmt = $pdo->prepare(
"
SELECT SUM(xp) AS total_xp
FROM user_skills
WHERE user_id = ?
"
);

$stmt->execute([
    $quest['user_id']
]);

// get the total XP value
$total = $stmt->fetch();

// store the user's total XP
$totalXp = $total['total_xp'] ?? 0;

// calculate the user's overall account level
$accountLevel =
floor($totalXp / 250) + 1;

// query to update the user's total XP and account level
$pdo->prepare(
"
UPDATE users
SET
    xp = ?,
    level = ?
WHERE user_id = ?
"
)->execute([
    $totalXp,
    $accountLevel,
    $quest['user_id']
]);

// return the user to the dashboard
header("Location: dashboard.php");
exit;