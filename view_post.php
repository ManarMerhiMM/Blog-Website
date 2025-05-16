<?php
session_start();
include "connect.php";

$postID = isset($_REQUEST["postID"]) ? intval($_REQUEST["postID"]) : 0;
if (!$postID) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["comment"])) {
        $content = $_POST["content"];
        $commentStmt = $conn->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
        $commentStmt->bind_param("iis", $_SESSION["id"], $postID, $content);
        $commentStmt->execute();
    }
    if (isset($_POST["deleteComment"])) {
        $commentID = isset($_POST["commentID"]) ? $_POST["commentID"] : '';
        $deleteStmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $deleteStmt->bind_param("ii", $commentID, $_SESSION["id"]);
        $deleteStmt->execute();
    }
}
// Fetch post, author info, and author_id
$stmt = $conn->prepare(
    "SELECT p.id AS post_id, p.category, p.title, p.content, p.image_path, p.created_at AS post_created, p.author_id,
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
                <a class="authors" href="profile.php?userID=<?php echo "{$post["author_id"]}";; ?>">By <?= htmlspecialchars($post['username']) ?></a>
                <span class="userSince" data-user-created="<?= $post['user_created'] ?>">
                    Joined: <?= date("j F, Y", strtotime($post['user_created'])) ?>
                </span>
                <span class="postCount"><?= $postCount ?> posts</span>
                <span class="postDate">Published: <?= date("j F, Y", strtotime($post['post_created'])) ?></span>
                <span class="categories <?= $post["category"] ?>"><?= $post["category"] ?></span>
            </div>
            <div class="postContent"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
            <?php if (isset($post["image_path"])) { ?>
                <img class="images" src="<?php echo $post["image_path"]; ?>" alt="<?php echo "{$post["title"]} image" ?>">
            <?php } ?>
            <div class="commentSection">
                <h2>Comments</h2>
                <?php
                $post_id = $post["post_id"];
                $commentsStmt = $conn->prepare("
                                    SELECT comments.*, comments.id AS comment_id, users.username 
                                    FROM comments 
                                    JOIN users ON users.id = comments.user_id 
                                    WHERE comments.post_id = ? 
                                    ORDER BY comments.created_at ASC
                                ");
                $commentsStmt->bind_param("i", $post_id);

                $commentsStmt->execute();
                $commentsResult = $commentsStmt->get_result();
                if ($commentsResult->num_rows < 1) {
                    echo "<h2>No Comments yet :/</h2>";
                    if (isset($_SESSION["successful"]) && $_SESSION["successful"]) {
                        echo '<form action="view_post.php" method="post" id="commentForm">';
                        echo '<input type="text" placeholder="Comment..." name="content">';
                        echo '<input type="hidden" name="postID" value="' . $postID . '">';
                        echo '<button type="submit" name="comment">Comment</button>';
                        echo '</form>';
                    }
                } else {
                    if (isset($_SESSION["successful"]) && $_SESSION["successful"]) {
                        echo '<form action="view_post.php" method="post" id="commentForm">';
                        echo '<input type="text" placeholder="Comment..." name="content">';
                        echo '<input type="hidden" name="postID" value="' . $postID . '">';
                        echo '<button type="submit" name="comment">Comment</button>';
                        echo '</form>';
                    }
                    while ($comment = $commentsResult->fetch_assoc()) { ?>
                        <div class="comment">
                            <div class="commentDiv1">
                                <a href="<?php echo "profile.php?userID={$comment["user_id"]}" ?>" class="authors"><?php echo $comment["username"] ?></a>
                                <span class="dates"><?= htmlspecialchars(date('j/n/Y', strtotime($comment['created_at']))) ?></span>
                            </div>
                            <p class="commentBody"><?php echo $comment["content"] ?></p>
                            <?php if (isset($_SESSION["successful"]) && $_SESSION["successful"] && $comment["user_id"] == $_SESSION["id"]) { ?>
                                <form action="view_post.php" method="post" class="deleteCommentForms">
                                    <input type="hidden" name="commentID" value="<?= htmlspecialchars($comment['comment_id']) ?>">
                                    <input type="hidden" name="postID" value="<?= htmlspecialchars($post['post_id']) ?>">
                                    <button type="submit" name="deleteComment">Delete</button>
                                </form>
                            <?php } ?>
                        </div>

                <?php
                    }
                }
                ?>
                <a href="index.php" class="backBtn">← Back to Home</a>
        </article>
    </main>
    <script src="JS/view_post.js"></script>
</body>

</html>