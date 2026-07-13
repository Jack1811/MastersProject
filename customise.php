<?php
require 'includes/auth.php';
require 'includes/db.php';

$userId = $_SESSION['user_id'];

// Get current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Keep existing picture unless a new one is uploaded
    $profilePicture = $user['profile_picture'];


    // Check if a new file was uploaded
    if (!empty($_FILES['profile_picture']['name'])) {

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        $extension = strtolower(
            pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION)
        );


        if (in_array($extension, $allowed)) {

            // Create unique filename
            $newFileName = uniqid() . "." . $extension;

            // Save location
            $destination = "uploads/profile_pictures/" . $newFileName;


            // Upload new image
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $destination)) {


                // Delete old image if unused
                if ($user['profile_picture'] != 'default.png') {

                    $stmt = $pdo->prepare("
                        SELECT COUNT(*)
                        FROM users
                        WHERE profile_picture = ?
                    ");

                    $stmt->execute([
                        $user['profile_picture']
                    ]);


                    if ($stmt->fetchColumn() == 1) {

                        $oldFile = "uploads/profile_pictures/" . $user['profile_picture'];

                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }
                }


                // Use new image
                $profilePicture = $newFileName;
            }
        }
    }


    // Update user profile
    $stmt = $pdo->prepare("
        UPDATE users
        SET
            bio = ?,
            profile_picture = ?
        WHERE user_id = ?
    ");


    $stmt->execute([
        $_POST['bio'],
        $profilePicture,
        $userId
    ]);


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
    <title>Customise Profile</title>
</head>

<body>
<section class="page-container">
<div class="customise-page">

    <form method="POST" enctype="multipart/form-data" class="customise-box">

        <h1>Customise Character</h1>
        
        <img src="uploads/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>" class="profile-preview" id="profilePreview" alt="Profile Picture">

        <label class="upload-label">Profile Picture</label>
        <input type="file" name="profile_picture" id="profilePicture" accept="image/*">

        <label class="bio-label">Character Bio</label>
        <textarea name="bio" maxlength="300" placeholder="Tell everyone about your adventurer..."><?= htmlspecialchars($user['bio']) ?></textarea>

        <button>Save Changes</button>

        <a href="dashboard.php" class="btn-cancel">Cancel</a>

    </form>

</div>
</section>
<script>
    const input = document.getElementById("profilePicture");
    const preview = document.getElementById("profilePreview");

    input.addEventListener("change", function () {

        const file = this.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
</script>
</body>
</html>