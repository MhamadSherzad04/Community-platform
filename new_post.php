<?php
include 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $content     = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = $_POST['category_id'];
    $user_id     = $_SESSION['user_id'];
    $photo       = "";  // will store the filename if a photo is uploaded

    if (empty($title) || empty($content) || empty($category_id)) {
        $error = "Please fill in all fields.";
    } else {

        // Handle photo upload if a file was selected
        if (!empty($_FILES['photo']['name'])) {

            $file     = $_FILES['photo'];
            $filename = $file['name'];
            $tmp      = $file['tmp_name'];
            $size     = $file['size'];

            // Get file extension (e.g. jpg, png)
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            // Only allow image files
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Only image files are allowed (jpg, png, gif, webp).";
            } elseif ($size > 5 * 1024 * 1024) {
                // Limit file size to 5MB
                $error = "Image must be smaller than 5MB.";
            } else {
                // Create a unique filename so files don't overwrite each other
                $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;

                // Move the uploaded file to the uploads folder
                move_uploaded_file($tmp, "uploads/" . $new_filename);
                $photo = $new_filename;
            }
        }

        // Only insert if no error happened during upload
        if (empty($error)) {
            $sql = "INSERT INTO posts (user_id, category_id, title, content, photo)
                    VALUES ($user_id, $category_id, '$title', '$content', '$photo')";
            mysqli_query($conn, $sql);
            header("Location: index.php");
            exit();
        }
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <span class="site-name">📢 Community</span>
    <a href="index.php">Home</a>
    <a href="new_post.php">+ New Post</a>
    <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
</nav>

<div class="container">
    <div class="box">
        <h1>Write a New Post</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- enctype is required for file uploads -->
        <form method="POST" enctype="multipart/form-data">

            <label>Title</label>
            <input type="text" name="title" placeholder="Give your post a title" required>

            <label>Category</label>
            <select name="category_id" required>
                <option value="">-- Choose a category --</option>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                <?php endwhile; ?>
            </select>

            <label>Content</label>
            <textarea name="content" placeholder="Write your post here..." required></textarea>

            <label>Photo <span class="small">(optional — jpg, png, gif, max 5MB)</span></label>
            <input type="file" name="photo" accept="image/*">

            <br>
            <button type="submit">Publish Post</button>
            <a href="index.php" class="btn btn-cancel">Cancel</a>

        </form>
    </div>
</div>

</body>
</html>
