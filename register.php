<?php
include 'db.php';

// This variable will hold any error message
$error = "";

// This runs only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get what the user typed in the form
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Basic checks
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";

    } elseif ($password != $confirm) {
        $error = "Passwords do not match.";

    } else {
        // Check if username already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");

        if (mysqli_num_rows($check) > 0) {
            $error = "That username is already taken.";
        } else {
            // Hash the password so it's not stored as plain text
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database
            $sql = "INSERT INTO users (username, email, password)
                    VALUES ('$username', '$email', '$hashed_password')";

            mysqli_query($conn, $sql);

            // Redirect to login page
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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

        <h1>Register</h1>

        <!-- Show error if there is one -->
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Registration form -->
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm" required>

            <button type="submit">Create Account</button>
        </form>

        <p class="small">Already have an account? <a href="login.php">Login here</a></p>

    </div>
</div>

</body>
</html>
