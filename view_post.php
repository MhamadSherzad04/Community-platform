<?php
include 'db.php';

$id = $_GET['id'];

// Get the post with author and category info
$sql    = "SELECT posts.*, users.username, categories.name AS category_name
           FROM posts
           JOIN users      ON posts.user_id     = users.id
           JOIN categories ON posts.category_id = categories.id
           WHERE posts.id = $id";
$result = mysqli_query($conn, $sql);
$post   = mysqli_fetch_assoc($result);

if (!$post) {
    echo "Post not found.";
    exit();
}

// Handle new comment submission
$comment_error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {

    if (!isset($_SESSION['user_id'])) {
        $comment_error = "You must be logged in to comment.";
    } elseif (empty(trim($_POST['comment']))) {
        $comment_error = "Comment cannot be empty.";
    } else {
        $comment = mysqli_real_escape_string($conn, $_POST['comment']);
        $user_id = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO comments (post_id, user_id, content)
                             VALUES ($id, $user_id, '$comment')");
        header("Location: view_post.php?id=$id");
        exit();
    }
}

// Get all comments for this post
$comments_result = mysqli_query($conn, "
    SELECT comments.*, users.username
    FROM comments
    JOIN users ON comments.user_id = users.id
    WHERE comments.post_id = $id
    ORDER BY comments.created_at ASC
");

$cat_colors = [
    'Science'    => 'cat-science',
    'Technology' => 'cat-technology',
    'Health'     => 'cat-health',
    'History'    => 'cat-history',
    'Art'        => 'cat-art',
    'Gaming'     => 'cat-gaming',
    'Books'      => 'cat-books',
    'General'    => 'cat-general',
];
$css = isset($cat_colors[$post['category_name']]) ? $cat_colors[$post['category_name']] : 'cat-general';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $post['title']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <span class="site-name">📢 Community</span>
    <a href="index.php">Home</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="new_post.php">+ New Post</a>
        <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>

<div class="container">

    <!-- The Post -->
    <div class="box">

        <span class="category-badge <?php echo $css; ?>"><?php echo $post['category_name']; ?></span>

        <h1 style="margin-top: 12px;"><?php echo $post['title']; ?></h1>

        <p class="small">
            👤 <strong><?php echo $post['username']; ?></strong>
            &nbsp;·&nbsp;
            🕐 <?php echo $post['created_at']; ?>
        </p>

        <!-- Show photo if there is one -->
        <?php if (!empty($post['photo'])): ?>
            <img src="uploads/<?php echo $post['photo']; ?>" class="post-full-photo" alt="Post photo">
        <?php endif; ?>

        <hr>

        <!-- Post content — nl2br turns new lines into <br> tags -->
        <p style="font-size:15px; line-height:1.8; color:#444;">
            <?php echo nl2br($post['content']); ?>
        </p>

        <!-- Edit and Delete buttons — only for the post author -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
            <div class="post-actions">
                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn">✏️ Edit Post</a>
                <a href="delete_post.php?id=<?php echo $post['id']; ?>"
                   class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete this post?')">🗑️ Delete</a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Comments Section -->
    <div class="box">
        <h2>💬 Comments (<?php echo mysqli_num_rows($comments_result); ?>)</h2>

        <?php if ($comment_error): ?>
            <div class="error"><?php echo $comment_error; ?></div>
        <?php endif; ?>

        <!-- List of comments -->
        <?php if (mysqli_num_rows($comments_result) == 0): ?>
            <p class="small">No comments yet. Be the first!</p>
        <?php else: ?>
            <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
                <div class="comment-item">
                    <strong><?php echo $comment['username']; ?></strong>
                    <span class="small">&nbsp;·&nbsp; <?php echo $comment['created_at']; ?></span>
                    <p><?php echo nl2br($comment['content']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <!-- Comment form -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <h3 style="margin-top: 20px;">Leave a Comment</h3>
            <form method="POST">
                <textarea name="comment" placeholder="Write your comment..."></textarea>
                <button type="submit">Post Comment</button>
            </form>
        <?php else: ?>
            <p class="small" style="margin-top:16px;">
                <a href="login.php">Login</a> to leave a comment.
            </p>
        <?php endif; ?>

    </div>

    <a href="index.php" class="small">← Back to all posts</a>

</div>
</body>
</html>
