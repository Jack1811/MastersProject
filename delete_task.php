<?php
require 'includes/auth.php';
require 'includes/db.php';

$userId = $_SESSION['user_id'];

if (isset($_GET['quest_id'])) {

    $questId = (int) $_GET['quest_id'];

    // Delete only the user's own quest
    $stmt = $pdo->prepare("
        DELETE FROM quests
        WHERE quest_id = ?
        AND user_id = ?
    ");

    $stmt->execute([
        $questId,
        $userId
    ]);
}

header("Location: dashboard.php");
exit;
?>