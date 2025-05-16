<?php
session_start();
include "connect.php";

$id = isset($_GET["userID"]) ? $_GET["userID"] : '';

if (empty($id)) {
    header("Location: index.php");
    exit;
}

$userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->bind_param("i", $id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();


$totalPostNum = $conn->prepare("SELECT COUNT(*) AS count FROM posts WHERE author_id = ?");
$totalPostNum->bind_param("i", $id);
$totalPostNum->execute();
$totalPostNum = $totalPostNum->get_result();
$totalPostNum = $totalPostNum->fetch_assoc();

$totalPostNum = $totalPostNum["count"];
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "{$user['username']}'s Profile"; ?></title>
    <link rel="icon" href="imgs/userIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/profile.css">
</head>

<body>
    <header>
        <nav id="sidebar">
            <img id="logo" src="imgs/bloggerLogo.jpg" alt="Website Logo">
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (!empty($_SESSION['successful'])): ?>
                    <li><a href="dashboard.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]) { ?>
                        <li><a href="admin_dashboard.php">Admin Panel</a></li>
                    <?php } ?>
                    <li><a href="logout.php">Signout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button id="burger">&rarr;</button>
    </header>

    <main>
        <section class="welcome-container">
            <h2 id="welcomeMessage"><?php echo "{$user["username"]}'s Profile" ?></h2>
        </section>

        <section id="mainContainer">
            <h2 id="activity"><?php echo "{$totalPostNum} Posts"; ?></h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<article>";
                    echo "<div class='mainDiv'>";
                    echo "<div class='subDiv1'>";
                    echo "<img class='postImgs' src='imgs/userForPost.png' alt='user image for post'>";
                    echo '<a class="authors" href="profile.php?userID=' . $row["author_id"] . '">' . htmlspecialchars($user["username"]) . '</a>';
                    echo "<span class='categories {$row["category"]}'>{$row["category"]}</span>";
                    echo "</div>";
                    echo "<div class='subDiv2'>";
                    echo "<h2 class='titles'>" . htmlspecialchars($row["title"]) . "</h2>";
                    echo "<span class='dates'>" . htmlspecialchars(date("j/n/Y", strtotime($row["created_at"]))) . "</span>";
                    echo "</div>";
                    echo "<p class='postBody'>" . nl2br(htmlspecialchars($row["content"])) . "</p>";
                    if (isset($row["image_path"])) {
                        echo '<img class="images" src="' . $row["image_path"] . '" alt="' . htmlspecialchars($row["title"]) . ' image">';
                    }
                    echo "</div>";
                    echo "<form action='view_post.php' method='get' style='display: hidden'>";
                    echo "<input type='hidden' name='postID' value='{$row['id']}'>";
                    echo "</form>";
                    echo "</article>";
                }
            } else {
                echo "<p class='noPosts'>You haven't posted anything yet :/</p>";
            }
            ?>
        </section>
    </main>

    <script src="JS/profile.js"></script>
</body>

</html>