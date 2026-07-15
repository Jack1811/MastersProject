<?php

function checkAchievements($pdo, $userId)
{

    // Get user data
    $stmt = $pdo->prepare("
        SELECT level
        FROM users
        WHERE user_id = ?
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch();


    // Count completed quests
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM quests
        WHERE user_id = ?
        AND status = 'Completed'
    ");

    $stmt->execute([$userId]);

    $completedTasks = $stmt->fetchColumn();



    // skill level
    $stmt = $pdo->prepare("
        SELECT xp
        FROM user_skills us
        JOIN skills s 
        ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        AND s.skill_name = 'Fitness'
    ");

    $stmt->execute([$userId]);

    $fitness = $stmt->fetch();

    $fitnessLevel = 0;


    if($fitness)
    {
        $xp = $fitness['xp'];
        $fitnessLevel = 1;

        $needed = 250;

        while($xp >= $needed)
        {
            $xp -= $needed;
            $fitnessLevel++;
            $needed = $fitnessLevel * 250;
        }
    }


    $stmt = $pdo->prepare("
        SELECT xp
        FROM user_skills us
        JOIN skills s 
        ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        AND s.skill_name = 'Knowledge'
    ");

    $stmt->execute([$userId]);

    $knowledge = $stmt->fetch();

    $knowledgeLevel = 0;


    if($knowledge)
    {
        $xp = $knowledge['xp'];
        $knowledgeLevel = 1;

        $needed = 250;

        while($xp >= $needed)
        {
            $xp -= $needed;
            $knowledgeLevel++;
            $needed = $knowledgeLevel * 250;
        }
    }

    $stmt = $pdo->prepare("
        SELECT xp
        FROM user_skills us
        JOIN skills s 
        ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        AND s.skill_name = 'Discipline'
    ");

    $stmt->execute([$userId]);

    $discipline = $stmt->fetch();

    $disciplineLevel = 0;


    if($discipline)
    {
        $xp = $discipline['xp'];
        $disciplineLevel = 1;

        $needed = 250;

        while($xp >= $needed)
        {
            $xp -= $needed;
            $disciplineLevel++;
            $needed = $disciplineLevel * 250;
        }
    }

    $stmt = $pdo->prepare("
        SELECT xp
        FROM user_skills us
        JOIN skills s 
        ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        AND s.skill_name = 'Creativity'
    ");

    $stmt->execute([$userId]);

    $creativity = $stmt->fetch();

    $creativityLevel = 0;


    if($creativity)
    {
        $xp = $creativity['xp'];
        $creativityLevel = 1;

        $needed = 250;

        while($xp >= $needed)
        {
            $xp -= $needed;
            $creativityLevel++;
            $needed = $creativityLevel * 250;
        }
    }

    $stmt = $pdo->prepare("
        SELECT xp
        FROM user_skills us
        JOIN skills s 
        ON us.skill_id = s.skill_id
        WHERE us.user_id = ?
        AND s.skill_name = 'Social'
    ");

    $stmt->execute([$userId]);

    $Social = $stmt->fetch();

    $SocialLevel = 0;


    if($Social)
    {
        $xp = $Social['xp'];
        $SocialLevel = 1;

        $needed = 250;

        while($xp >= $needed)
        {
            $xp -= $needed;
            $SocialLevel++;
            $needed = $SocialLevel * 250;
        }
    }


    // Check achievements
    $achievements = [];


    // Level achievements
    if($user['level'] >= 5)
    {
        $achievements[] = 2;
    }
    
    if($user['level'] >= 10)
    {
        $achievements[] = 4;
    }

    if($user['level'] >= 15)
    {
        $achievements[] = 11;
    }


    // Task achievements
    if($completedTasks >= 1)
    {
        $achievements[] = 1;
    }

    if($completedTasks >= 10)
    {
        $achievements[] = 5;
    }

    if($completedTasks >= 15)
    {
        $achievements[] = 10;
    }

    // Skill level achievements
    if($fitnessLevel >= 3)
    {
        $achievements[] = 3;
    }
        
    if($disciplineLevel >= 3)
    {
        $achievements[] = 6;
    }

    if($knowledgeLevel >= 3)
    {
        $achievements[] = 8;
    }

    if($creativityLevel >= 3)
    {
        $achievements[] = 7;
    }
    if($SocialLevel >= 3)
    {
        $achievements[] = 9;
    }

    if($fitnessLevel >= 5)
    {
        $achievements[] = 12;
    }
        
    if($disciplineLevel >= 5)
    {
        $achievements[] = 13;
    }

    if($knowledgeLevel >= 5)
    {
        $achievements[] = 14;
    }

    if($creativityLevel >= 5)
    {
        $achievements[] = 15;
    }

    if($SocialLevel >= 5)
    {
        $achievements[] = 20;
    }

    if($fitnessLevel >= 10)
    {
        $achievements[] = 16;
    }
        
    if($disciplineLevel >= 10)
    {
        $achievements[] = 17;
    }

    if($knowledgeLevel >= 10)
    {
        $achievements[] = 18;
    }

    if($creativityLevel >= 10)
    {
        $achievements[] = 19;
    }

    if($SocialLevel >= 10)
    {
        $achievements[] = 21;
    }


    // Give achievements
    foreach($achievements as $achievementId)
    {

        // Prevent duplicates
        $stmt = $pdo->prepare("
            SELECT *
            FROM user_achievements
            WHERE user_id = ?
            AND achievement_id = ?
        ");

        $stmt->execute([
            $userId,
            $achievementId
        ]);

    if(!$stmt->fetch()){

        // Unlock achievement
        $stmt = $pdo->prepare("
            INSERT INTO user_achievements
            (user_id, achievement_id)
            VALUES (?,?)
        ");

        $stmt->execute([
            $userId,
            $achievementId
        ]);


        // Get achievement XP reward
        $stmt = $pdo->prepare("
            SELECT xp_reward
            FROM achievements
            WHERE achievement_id = ?
        ");

        $stmt->execute([$achievementId]);

        $reward = $stmt->fetchColumn();


        // Add XP reward to user
        $stmt = $pdo->prepare("
            UPDATE users
            SET xp = xp + ?
            WHERE user_id = ?
        ");

        $stmt->execute([
            $reward,
            $userId
        ]);

    }

    }

}

?>