<?php
session_start();
include "connect.php";

$postID = isset($_GET['postID']) ? intval($_GET['postID']) : 0;
if (!$postID) {
    header("Location: index.php");
    exit;
}

// Fetch post, author info, and author_id
$stmt = $conn->prepare(
    "SELECT p.id, p.category, p.title, p.content, p.created_at AS post_created, p.author_id,
            u.username, u.created_at AS user_created
     FROM posts p
     JOIN users u ON p.author_id = u.id
     WHERE p.id = ?"
);
$stmt->bind_param("i", $postID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit;
}
$post = $result->fetch_assoc();

// Count how many posts this user has made
$countStmt = $conn->prepare(
    "SELECT COUNT(*) AS post_count FROM posts WHERE author_id = ?"
);
$countStmt->bind_param("i", $post['author_id']);
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$postCount = $countRow['post_count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="icon" href="imgs/viewPostIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/view_post.css">
</head>

<body>
    <main>
        <article>
            <h1 class="postTitle"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="postMeta">
                <a class="authors" href="profile.php?userID=<?php echo"{$post["author_id"]}";;?>">By <?= htmlspecialchars($post['username']) ?></a>
                <span class="userSince" data-user-created="<?= $post['user_created'] ?>">
                    Joined: <?= date("j F, Y", strtotime($post['user_created'])) ?>
                </span>
                <span class="postCount"><?= $postCount ?> posts</span>
                <span class="postDate">Published: <?= date("j F, Y", strtotime($post['post_created'])) ?></span>
                <span class="categories <?= $post["category"] ?>"><?= $post["category"] ?></span>
            </div>
            <div class="postContent"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
            <a href="index.php" class="backBtn">← Back to Home</a>
        </article>
    </main>
    <script src="JS/view_post.js"></script>
</body>

</html>