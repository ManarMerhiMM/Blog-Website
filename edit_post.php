<?php
session_start();
include "connect.php";
$_SESSION["prev_page"] = "edit_post.php";

// Redirect if not authenticated
if (!(isset($_SESSION["successful"]) && $_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

// Get and validate postID
$postID = $_REQUEST['postID'] ?? null;
if (!$postID) {
    header("Location: dashboard.php");
    exit;
}

// Check ownership and fetch current image path
$authStmt = $conn->prepare("SELECT author_id, image_path FROM posts WHERE id = ?");
$authStmt->bind_param("i", $postID);
$authStmt->execute();
$authResult = $authStmt->get_result()->fetch_assoc();
$authStmt->close();
if (!$authResult || $authResult["author_id"] != $_SESSION["id"]) {
    header("Location: dashboard.php");
    exit;
}
$currentImage = $authResult['image_path'];

$error = '';

// Fetch existing title/content/category
$stmt = $conn->prepare("SELECT title, content, category FROM posts WHERE id = ? AND author_id = ?");
$stmt->bind_param("ii", $postID, $_SESSION['id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title        = htmlspecialchars(trim($_POST["title"]));
    $content      = htmlspecialchars(trim($_POST["content"]));
    $category     = htmlspecialchars(trim($_POST["category"]));
    $newImagePath = $currentImage;

    if($title == "" || $content == ""){
        $error = "Error: Empty field(s)!";
    }
    // 1) Handle image removal
    if (!empty($_POST['remove_image']) && $currentImage) {
        $fullPath = __DIR__ . '/' . $currentImage;
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
        $newImagePath = null;
    }

    // 2) Handle new upload if not removing
    if (empty($_POST['remove_image']) && !empty($_FILES['image']['name'])) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload error code: ' . $_FILES['image']['error'];
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
            $maxSize      = 6 * 1024 * 1024; // 6MB
            $tmpPath  = $_FILES['image']['tmp_name'];
            $origName = basename($_FILES['image']['name']);
            $mimeType = mime_content_type($tmpPath);
            $fileSize = $_FILES['image']['size'];

            if (!in_array($mimeType, $allowedTypes)) {
                $error = 'Error: Invalid file type. Please upload JPEG, PNG, GIF, WebP, or BMP.';
            } elseif ($fileSize > $maxSize) {
                $error = 'Error: File is too large (max 6 MB).';
            } else {
                $uploadDir     = __DIR__ . '/uploads/';
                $uploadWebPath = 'uploads/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    $error = 'Error: Cannot create upload directory.';
                } else {
                    $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $origName);
                    $newName  = uniqid() . "_$safeName";
                    $destPath = $uploadDir . $newName;
                    $webPath  = $uploadWebPath . $newName;

                    if (move_uploaded_file($tmpPath, $destPath)) {
                        // delete old file if exists
                        if ($currentImage && file_exists(__DIR__ . '/' . $currentImage)) {
                            @unlink(__DIR__ . '/' . $currentImage);
                        }
                        $newImagePath = $webPath;
                    } else {
                        $error = 'Failed to move uploaded file.';
                    }
                }
            }
        }
    }

    // Update if no errors
    if (empty($error)) {
        $upd = $conn->prepare(
            "UPDATE posts 
             SET title = ?, content = ?, category = ?, image_path = ?
             WHERE id = ? AND author_id = ?"
        );
        $upd->bind_param(
            "ssssii",
            $title,
            $content,
            $category,
            $newImagePath,
            $postID,
            $_SESSION["id"]
        );
        $upd->execute();
        $upd->close();
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Post</title>
    <link rel="icon" href="imgs/updatePostIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/edit_post.css">
</head>

<body>
    <form action="edit_post.php" method="post" enctype="multipart/form-data" id="updateForm">
        <h2 id="formTitle">Edit Post</h2>
        <p id="errors"><?php echo htmlspecialchars($error); ?></p>

        <input type="hidden" name="postID" value="<?php echo (int)$postID; ?>">

        <label for="title">Title:</label>
        <input type="text" name="title" id="title"
            value="<?php echo htmlspecialchars($post['title']); ?>" required>

        <label for="content">Body:</label>
        <textarea name="content" id="content" required><?php
                                                        echo htmlspecialchars($post['content']);
                                                        ?></textarea>

        <div id="categoryContainer">
            <label for="postCategory">Category:</label>
            <select name="category" id="postCategory">
                <?php
                $catResult = $conn->query("SELECT name FROM categories ORDER BY name ASC");
                while ($cat = $catResult->fetch_assoc()) {
                    $n   = htmlspecialchars($cat['name']);
                    $sel = ($post['category'] === $cat['name']) ? ' selected' : '';
                    echo "<option value=\"$n\"$sel>$n</option>";
                }
                ?>
            </select>
        </div>

        <?php
        // Display current image
        if ($currentImage) {
            echo '<div id="currentImage">';
            echo '<p>Current image:</p>';
            echo '<img class="currentImage" src="' . htmlspecialchars($currentImage) . '" alt="Current post image">';
            echo '</div>';
            // Removal checkbox
            echo '<div id="removeImageOption">';
            echo '<label><input type="checkbox" name="remove_image" value="1"> Remove current image</label>';
            echo '</div>';
        }
        ?>

        <div id="imageUpload">
            <label for="image">Replace Image (optional, max 6 MB):</label>
            <input type="file" name="image" id="image"
                accept="image/jpeg,image/png,image/gif,image/webp,image/bmp">
        </div>

        <div id="actions">
            <a href="dashboard.php"><button type="button">Back</button></a>
            <button type="submit" name="update">Confirm Changes</button>
        </div>
    </form>
    <script src="JS/edit_post.js"></script>
</body>

</html>