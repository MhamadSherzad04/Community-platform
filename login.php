<?php
include 'db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Find the user in the database by username
    $sql    = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    $user   = mysqli_fetch_assoc($result);

    // Check if user exists AND password matches
    if ($user && password_verify($password, $user['password'])) {

        // Save user info in the session (this keeps them "logged in")
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Send them to the homepage
        header("Location: index.php");
        exit();

    } else {
        $error = "Wrong username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <span class="site-name">📢 Community</span>
    <a href="index.php">Home</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</nav>

<div class="container">
    <div class="box" style="max-width: 420px; margin: 0 auto;">

        <h1>Login</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p class="small">No account? <a href="register.php">Register here</a></p>

    </div>
</div>

</body>
</html>
