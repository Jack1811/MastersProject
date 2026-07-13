<?php
// Needs database to work
require 'includes/db.php';

// checks if user is logged in, if so, send to dashboard instead
if(isset($_SESSION['user_id']))
{
    header("Location: dashboard.php");
    exit();
}

$error = "";

// Runs if post
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Check if username or email already exists
    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE username = ? OR email = ?"
    );

    $stmt->execute([
        $_POST['username'],
        $_POST['email']
    ]);

    $existingUser = $stmt->fetch();

    if($existingUser)
    {
        $error = 'Username or email already exists. Please try again. Alternatively, <a href="login.php" class="btn">Login</a>';
    }
    else
    {
        // hashes password
        $passwordHash = password_hash(
            $_POST['password'],
            PASSWORD_DEFAULT
        );

        // writes sql query
        $stmt = $pdo->prepare(
            "INSERT INTO users
            (username,email,password_hash)
            VALUES (?,?,?)"
        );

        // sends SQL query with inserted values
        $stmt->execute([
            $_POST['username'],
            $_POST['email'],
            $passwordHash
        ]);

        // auto increments user id
        $userId = $pdo->lastInsertId();

        // selects skills
        $skills = $pdo->query(
            "SELECT skill_id FROM skills"
        );

        // query to add user skills
        foreach($skills as $skill)
        {
            $stmt = $pdo->prepare(
                "INSERT INTO user_skills
                (user_id, skill_id)
                VALUES (?,?)"
            );

            $stmt->execute([
                $userId,
                $skill['skill_id']
            ]);
        }

        // sends user to login
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Create Account</title>
</head>
<body>
    <section class="page-container">
        <div class="login-page">   

            <form method="POST" class="login-box">
                <h1>Register</h1>

                <input name="username" placeholder="Username" required>
                <input name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button>Register</button>

                <p>Already got an account?<a href="login.php" class="register-link">Login here</a></p>
            </form>
            
            <?php if($error): ?>
                <p class="error">
                    <?= $error ?>
                </p>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>