<?php
session_start(); // Start session at the beginning of the script
include "connect.php";
$_SESSION["prev_page"] = "index.php";

// Redirect to session.php if the user is not successfully authenticated
if (!(isset($_SESSION["successful"]) && $_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

$stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id ORDER BY posts.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["search"])) {
        $searchContent = "%" . trim($_GET["prompt"]) . "%";
        $stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.author_id = users.id WHERE posts.title LIKE ? OR users.username LIKE ? ORDER BY posts.created_at DESC");
        $stmt->bind_param("ss", $searchContent, $searchContent);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="icon" href="imgs/homepageIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/styles.css">
</head>

<body>
    <header>
        <nav id="sidebar">
            <img id="logo" src="imgs/bloggerLogo.jpg" alt="Website Logo">

            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="dashboard.php"><?php echo $_SESSION["username"]; ?></a></li>
                <li><a href="logout.php" id="signoutBtn">Signout</a></li>
            </ul>
        </nav>

        <button id="burger">&rarr;</button>
    </header>
    <main>
        <form action="index.php" method="get" id="searchForm">
            <input name="prompt" type="text" placeholder="Search by post title/author...">
            <button id="searchBtn" type="submit" name="search">Go</button>
        </form>
        <?php
        if (isset($result)) {
            while ($row = $result->fetch_assoc()) {
                echo "<article>";
                echo "<div class='mainDiv'>";

                // SubDiv1 - User Info
                echo "<div class='subDiv1'>";
                echo "<img class='postImgs' src='imgs/userForPost.png' alt='user image for post'>";
                echo "<span class='authors'>" . htmlspecialchars($row["username"]) . "</span>";
                echo "</div>";

                // SubDiv2 - Title and Date
                echo "<div class='subDiv2'>";
                echo "<h2 class='titles'>" . htmlspecialchars($row["title"]) . "</h2>";
                echo "<span class='dates'>" . htmlspecialchars(date("j/n/Y", strtotime($row["created_at"]))) . "</span>";
                echo "</div>";

                // Post Body
                echo "<p class='postBody'>" . nl2br(htmlspecialchars($row["content"])) . "</p>";

                echo "</div>";
                echo "<form action='view_post.php' method='get' style='display: none;'><input type='hidden' name='postID' value='{$row['id']}'></form>";
                echo "</article>";
            }
        }

        ?>

    </main>
    <script src="JS/main.js"></script>
</body>

</html>