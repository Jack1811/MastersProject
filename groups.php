<?php
// needs authenticator and database to work
require 'includes/auth.php';
require 'includes/db.php';

// ensure user is logged in
$userId = $_SESSION['user_id'];

// query to get party
$stmt = $pdo->prepare("
SELECT p.*
FROM parties p
JOIN party_members pm
ON p.party_id = pm.party_id
WHERE pm.user_id=?
");

// execute
$stmt->execute([$userId]);

// get data from query
$party = $stmt->fetch();

// if no party, error
if(!$party)
{
    echo "<h1>No Party</h1>";
    exit;
}

// show party name
echo "<h1>";
echo $party['party_name'];
echo "</h1>"; ?>