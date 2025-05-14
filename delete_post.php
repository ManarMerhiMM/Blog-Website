<?php
session_start();
include "connect.php";

//Authentication check
$_SESSION["prev_page"] = "delete_post.php";
if (empty($_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

//Read postID from GET or POST
$postID = $_REQUEST['postID'] ?? null;
$user_id = $_SESSION['id'] ?? null;

if (!$postID || !$user_id) {
    header("Location: dashboard.php");
    exit;
}

//Handle the deletion on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['confirm_delete'])) {
        $del = $conn->prepare("DELETE FROM posts WHERE id = ? AND author_id = ?");
        $del->bind_param("ii", $postID, $user_id);
        $del->execute();
    }
    header("Location: dashboard.php");
    exit;
}

//fetch the title for confirmation
$stmt = $conn->prepare("SELECT title FROM posts WHERE id = ? AND author_id = ?");
$stmt->bind_param("ii", $postID, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header("Location: dashboard.php");
    exit;
}
$post = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Delete Post</title>
    <link rel="stylesheet" href="CSS/delete_post.css">
    <link rel="icon" href="imgs/deletePostIcon.ico" type="image/x-icon">
</head>

<body>
    <form action="delete_post.php" method="post" id="deleteForm">
        <input type="hidden" name="postID" value="<?= htmlspecialchars($postID) ?>">
        <h2>Delete Post</h2>
        <p>Are you sure you want to delete the post titled:</p>
        <blockquote><?= htmlspecialchars($post['title']) ?></blockquote>
        <div class="btnGroup">
            <button type="submit" name="confirm_delete">Yes, Delete</button>
            <a href="dashboard.php"><button type="button">Cancel</button></a>
        </div>
    </form>
    <script src="JS/delete_post.js"></script>
</body>

</html>