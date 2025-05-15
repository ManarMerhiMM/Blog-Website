<?php
session_start();
include "connect.php";

$_SESSION["prev_page"] = "edit_post.php";
if (!(isset($_SESSION["successful"]) && $_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

$postID = $_REQUEST['postID'] ?? null;
if (!$postID) {
    header("Location: dashboard.php");
    exit;
}


$authStmt = $conn->prepare("SELECT author_id FROM posts WHERE id = ?");
$authStmt->bind_param("i", $postID);
$authStmt->execute();
$authResult = $authStmt->get_result();

$authResult = $authResult->fetch_assoc();
if ($authResult["author_id"] != $_SESSION["id"]) {
    header("Location: dashboard.php");
    exit;
}

// Fetch existing post data
$stmt = $conn->prepare("SELECT title, content, category FROM posts WHERE id = ? AND author_id = ?");
$stmt->bind_param("ii", $postID, $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    // Not found or not owned by user
    header("Location: dashboard.php");
    exit;
}
$post = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = htmlspecialchars(trim($_POST["title"]));
    $content = htmlspecialchars(trim($_POST["content"]));
    $category = htmlspecialchars(trim($_POST["category"]));

    $upd = $conn->prepare(
        "UPDATE posts SET title = ?, content = ?, category = ? WHERE id = ? AND author_id = ?"
    );
    $upd->bind_param("sssii", $title, $content, $category, $postID, $_SESSION["id"]);
    $upd->execute();
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="icon" href="imgs/updatePostIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/edit_post.css">
</head>

<body>
    <form action="edit_post.php" method="post" id="updateForm">
        <h2 id="formTitle">Edit Post</h2>
        <p id="errors"></p>
        <input type="hidden" name="postID" value="<?= $postID ?>">
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($post['title']) ?>" placeholder="Title...">
        <textarea name="content" id="content" placeholder="Body..."><?= htmlspecialchars($post['content']) ?></textarea>
        <div id="categoryContainer">
            <label for="postCategory">Category:</label>
            <select name="category">
                <?php
                $catResult = $conn->query("SELECT name FROM categories ORDER BY name ASC");
                while ($cat = $catResult->fetch_assoc()) {
                    $sel = ($post["category"] === $cat["name"]) ? 'selected' : '';
                    echo '<option value="' . htmlspecialchars($cat['name']) . '" ' . $sel . '>'
                        . htmlspecialchars($cat['name']) . '</option>';
                }
                ?>
            </select>
        </div>
        <a href="dashboard.php"><button type="button">Back</button></a>
        <button type="submit" name="update">Confirm Changes</button>
    </form>
    <script src="JS/edit_post.js"></script>
</body>

</html>