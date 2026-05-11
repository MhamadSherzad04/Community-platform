<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id      = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get the post first so we can delete its photo file too
$result = mysqli_query($conn, "SELECT * FROM posts WHERE id = $id AND user_id = $user_id");
$post   = mysqli_fetch_assoc($result);

if ($post) {
    // Delete the photo file from the server if there is one
    if (!empty($post['photo']) && file_exists("uploads/" . $post['photo'])) {
        unlink("uploads/" . $post['photo']);
    }
    // Delete the post from the database
    mysqli_query($conn, "DELETE FROM posts WHERE id = $id AND user_id = $user_id");
}

header("Location: index.php");
exit();
