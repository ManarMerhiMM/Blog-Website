<?php
session_start();
include "connect.php";
$_SESSION["prev_page"] = "dashboard.php";

if (!(isset($_SESSION["successful"]) && $_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

$userStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userStmt->bind_param("s", $_SESSION["username"]);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

$user_id = $user['id'];
$stmt = $conn->prepare("SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "{$_SESSION["username"]}"; ?>'s Profile</title>
    <link rel="icon" href="imgs/userIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/dashboard.css">
</head>

<body>
    <header>
        <nav id="sidebar">
            <img id="logo" src="imgs/bloggerLogo.jpg" alt="Website Logo">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php" class="active"><?php echo $_SESSION["username"]; ?></a></li>
                <li><a href="logout.php" id="signoutBtn">Signout</a></li>
            </ul>
        </nav>
        <button id="burger">&rarr;</button>
    </header>

    <main>
        <section class="welcome-container">
            <h2 id="welcomeMessage"><?php echo "Welcome, {$_SESSION["username"]}!" ?></h2>

        </section>

        <section id="mainContainer">
            <h2 id="activity">Your Activity</h2>
            <a href="create_post.php" class="addPostBtn">Add Post</a>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<article>";
                    echo "<div class='mainDiv'>";
                    echo "<div class='subDiv1'>";
                    echo "<img class='postImgs' src='imgs/userForPost.png' alt='user image for post'>";
                    echo '<a class="authors" href="profile.php?userID=' . $row["author_id"] . '">' . htmlspecialchars($_SESSION["username"]) . '</a>';
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
                    echo "<form action='view_post.php' method='get'>";
                    echo "<input type='hidden' name='postID' value='{$row['id']}'>";
                    echo "<button class='editBtn' name='edit' type='button'>Edit</button>";
                    echo "<button class='deleteBtn' name='delete' type='button'>Delete</button>";
                    echo "</form>";
                    echo "</article>";
                }
            } else {
                echo "<p class='noPosts'>You haven't posted anything yet :/</p>";
            }
            ?>
        </section>
    </main>

    <script src="JS/dashboard.js"></script>
</body>

</html>