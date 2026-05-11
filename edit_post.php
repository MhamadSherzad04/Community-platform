<?php
include 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id      = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the post from the database
$result = mysqli_query($conn, "SELECT * FROM posts WHERE id = $id");
$post   = mysqli_fetch_assoc($result);

// Make sure the post exists and belongs to the logged-in user
if (!$post || $post['user_id'] != $user_id) {
    echo "You are not allowed to edit this post.";
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $content     = mysqli_real_escape_string($conn, $_POST['content']);
    $category_id = $_POST['category_id'];
    $photo       = $post['photo']; // keep old photo by default

    if (empty($title) || empty($content) || empty($category_id)) {
        $error = "Please fill in all fields.";
    } else {

        // If user wants to remove the current photo
        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
            // Delete the file from the server
            if (!empty($photo) && file_exists("uploads/" . $photo)) {
                unlink("uploads/" . $photo);
            }
            $photo = "";
        }

        // If a new photo was uploaded
        if (!empty($_FILES['photo']['name'])) {

            $file     = $_FILES['photo'];
            $filename = $file['name'];
            $tmp      = $file['tmp_name'];
            $size     = $file['size'];
            $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Only image files are allowed (jpg, png, gif, webp).";
            } elseif ($size > 5 * 1024 * 1024) {
                $error = "Image must be smaller than 5MB.";
            } else {
                // Delete old photo file if there was one
                if (!empty($photo) && file_exists("uploads/" . $photo)) {
                    unlink("uploads/" . $photo);
                }
                // Save new photo
                $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
                move_uploaded_file($tmp, "uploads/" . $new_filename);
                $photo = $new_filename;
            }
        }

        if (empty($error)) {
            $sql = "UPDATE posts
                    SET title = '$title', content = '$content',
                        category_id = $category_id, photo = '$photo'
                    WHERE id = $id AND user_id = $user_id";
            mysqli_query($conn, $sql);
            header("Location: view_post.php?id=$id");
            exit();
        }
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <span class="site-name">📢 Community</span>
    <a href="index.php">Home</a>
    <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
</nav>

<div class="container">
    <div class="box">
        <h1>Edit Post</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <label>Title</label>
            <input type="text" name="title" value="<?php echo $post['title']; ?>" required>

            <label>Category</label>
            <select name="category_id" required>
                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php if ($cat['id'] == $post['category_id']) echo 'selected'; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Content</label>
            <textarea name="content" required><?php echo $post['content']; ?></textarea>

            <!-- Show current photo if there is one -->
            <?php if (!empty($post['photo'])): ?>
                <label>Current Photo</label>
                <img src="uploads/<?php echo $post['photo']; ?>" class="current-photo-preview" alt="Current photo">
                <div style="margin-top: 8px;">
                    <input type="checkbox" name="remove_photo" value="1" id="remove_photo">
                    <label for="remove_photo" style="display:inline; font-weight:normal; color:#e74c3c;">
                        Remove this photo
                    </label>
                </div>
            <?php endif; ?>

            <label>
                <?php echo !empty($post['photo']) ? 'Replace Photo' : 'Add Photo'; ?>
                <span class="small">(optional — jpg, png, gif, max 5MB)</span>
            </label>
            <input type="file" name="photo" accept="image/*">

            <br>
            <button type="submit">Save Changes</button>
            <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-cancel">Cancel</a>

        </form>
    </div>
</div>

</body>
</html>
