<?php
session_start();
include "connect.php";
$_SESSION["prev_page"] = "create_post.php";

// Redirect if not authenticated
if (!(isset($_SESSION["successful"]) && $_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category  = htmlspecialchars(trim($_POST["category"]));
    $title     = htmlspecialchars(trim($_POST["title"]));
    $content   = htmlspecialchars(trim($_POST["content"]));
    $imagePath = null;

    if($title == "" || $content == "" || $category == ""){
        $error = "Error: Empty field(s)!";
    }
    // IMAGE UPLOAD BLOCK
    if (!empty($_FILES['image']['name'])) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error: Upload error code: ' . $_FILES['image']['error'];
        } else {
            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/bmp'
            ];
            $maxSize = 6 * 1024 * 1024; // 6MB

            $tmpPath  = $_FILES['image']['tmp_name'];
            $origName = basename($_FILES['image']['name']);
            $mimeType = mime_content_type($tmpPath);
            $fileSize = $_FILES['image']['size'];

            if (!in_array($mimeType, $allowedTypes)) {
                $error = 'Error: Invalid file type. Please upload JPEG, PNG, GIF, WebP, or BMP.';
            } elseif ($fileSize > $maxSize) {
                $error = 'Error: File is too large. Maximum size is 6 MB.';
            } else {
                $uploadDir     = __DIR__ . '/uploads/';
                $uploadWebPath = 'uploads/';

                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        $error = 'Error: Unable to create upload directory.';
                    }
                }

                if (empty($error)) {
                    $newName  = uniqid() . "_" . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $origName);
                    $destPath = $uploadDir . $newName;
                    $webPath  = $uploadWebPath . $newName;

                    if (move_uploaded_file($tmpPath, $destPath)) {
                        $imagePath = $webPath;
                    } else {
                        $error = 'Error: Failed to move uploaded file.';
                    }
                }
            }
        }
    }

    // If no errors, insert into database
    if (empty($error)) {
        $stmt = $conn->prepare("
            INSERT INTO posts 
                (author_id, title, content, category, image_path)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issss",
            $_SESSION["id"],
            $title,
            $content,
            $category,
            $imagePath
        );
        $stmt->execute();
        $stmt->close();

        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create a Post</title>
    <link rel="icon" href="imgs/createPostIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/create_post.css">
</head>

<body>
    <form action="create_post.php" method="post" enctype="multipart/form-data" id="createForm">
        <h2 id="formTitle">Add Post</h2>
        <p id="errors"><?php echo htmlspecialchars($error); ?></p>

        <input type="text" name="title" id="title" placeholder="Title...">
        <textarea name="content" id="content" placeholder="Body..."></textarea>

        <div id="categoryContainer">
            <label for="postCategory">Category:</label>
            <select name="category" id="postCategory">
                <?php
                $catResult = $conn->query("SELECT name FROM categories ORDER BY name ASC");
                while ($cat = $catResult->fetch_assoc()) {
                    $n = htmlspecialchars($cat['name']);
                    echo "<option value=\"{$n}\">{$n}</option>";
                }
                ?>
            </select>
        </div>

        <div id="imageUpload">
            <label for="image">Upload Image (max 6 MB):</label>
            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp">
        </div>

        <div id="actions">
            <a href="dashboard.php"><button type="button">Back</button></a>
            <button type="submit" name="create">Post</button>
        </div>
    </form>

    <script src="JS/create_post.js"></script>
</body>

</html>