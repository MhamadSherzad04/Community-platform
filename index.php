<?php
include 'db.php';

// Map category names to CSS color classes
$cat_colors = [
    'Science' => 'cat-science',
    'Technology' => 'cat-technology',
    'Health' => 'cat-health',
    'History' => 'cat-history',
    'Art' => 'cat-art',
    'Gaming' => 'cat-gaming',
    'Books' => 'cat-books',
    'General' => 'cat-general',
];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Community Platform</title>
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

        <h1>All Posts</h1>

        <!-- Category filter buttons -->
        <?php $cats = mysqli_query($conn, "SELECT * FROM categories"); ?>
        <div class="category-filters">
            <a href="index.php" class="category-badge cat-all">All</a>
            <?php while ($cat = mysqli_fetch_assoc($cats)):
                $css = isset($cat_colors[$cat['name']]) ? $cat_colors[$cat['name']] : 'cat-general';
                ?>
                <a href="index.php?cat=<?php echo $cat['id']; ?>" class="category-badge <?php echo $css; ?>">
                    <?php echo $cat['name']; ?>
                </a>
            <?php endwhile; ?>
        </div>

        <?php
        // If a category filter is selected, use it in the query
        if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
            $cat_id = $_GET['cat'];
            $sql = "SELECT posts.*, users.username, categories.name AS category_name
                FROM posts
                JOIN users      ON posts.user_id     = users.id
                JOIN categories ON posts.category_id = categories.id
                WHERE posts.category_id = $cat_id
                ORDER BY posts.created_at DESC";
        } else {
            $sql = "SELECT posts.*, users.username, categories.name AS category_name
                FROM posts
                JOIN users      ON posts.user_id     = users.id
                JOIN categories ON posts.category_id = categories.id
                ORDER BY posts.created_at DESC";
        }

        $posts = mysqli_query($conn, $sql);

        if (mysqli_num_rows($posts) == 0): ?>
            <div class="box" style="text-align:center; padding:40px;">
                <p style="color:#aaa;">No posts yet. <a href="register.php">Register</a> and be the first!</p>
            </div>
        <?php else:
            while ($post = mysqli_fetch_assoc($posts)):
                $css = isset($cat_colors[$post['category_name']]) ? $cat_colors[$post['category_name']] : 'cat-general';
                ?>
                <div class="post-card">

                    <!-- Show photo if the post has one -->
                    <?php if (!empty($post['photo'])): ?>
                        <img src="uploads/<?php echo $post['photo']; ?>" class="post-card-photo" alt="Post photo">
                    <?php endif; ?>

                    <div class="post-card-body">
                        <h3><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a></h3>

                        <div class="meta">
                            👤 <strong><?php echo $post['username']; ?></strong>
                            &nbsp;·&nbsp;
                            🕐 <?php echo $post['created_at']; ?>
                            &nbsp;·&nbsp;
                            <span class="category-badge <?php echo $css; ?>" style="font-size:11px; padding:3px 10px;">
                                <?php echo $post['category_name']; ?>
                            </span>
                        </div>

                        <p><?php echo substr($post['content'], 0, 150); ?>...</p>
                        <a href="view_post.php?id=<?php echo $post['id']; ?>" class="small">Read more →</a>
                    </div>
                </div>
            <?php endwhile;
        endif; ?>

    </div>
</body>

</html>