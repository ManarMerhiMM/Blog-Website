<?php
session_start();
include "connect.php";

// Admin check
if (!(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"])) {
    header("Location: index.php");
    exit;
}

//Handle Actions 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Toggle user deactivation
    if (isset($_POST['deactivate_user'], $_POST['user_id'])) {
        $uid = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET deactivated = 1 WHERE id = ? AND is_admin = 0");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['activate_user'], $_POST['user_id'])) {
        $uid = (int)$_POST['user_id'];
        $stmt = $conn->prepare("UPDATE users SET deactivated = 0 WHERE id = ? AND is_admin = 0");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
    }

    // Delete a post
    if (isset($_POST['delete_post'], $_POST['post_id'])) {
        $pid = (int)$_POST['post_id'];
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $stmt->close();
    }

    // Refresh to avoid resubmission
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch Data 
// Non-admin users
$usersStmt = $conn->prepare("SELECT id, username, deactivated FROM users WHERE is_admin = 0 ORDER BY username ASC");
$usersStmt->execute();
$users = $usersStmt->get_result();
$usersStmt->close();

// All posts with authors
$postsStmt = $conn->prepare("
    SELECT p.*, u.username
    FROM posts p
    JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC
");
$postsStmt->execute();
$posts = $postsStmt->get_result();
$postsStmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="icon" href="imgs/adminPanelIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/admin_dashboard.css">
</head>

<body>

    <!-- Sidebar Navbar -->
    <header>
        <nav id="sidebar">
            <img id="logo" src="imgs/bloggerLogo.jpg" alt="Website Logo">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php"><?php echo htmlspecialchars($_SESSION["username"]); ?></a></li>
                <li><a href="admin_dashboard.php" class="active">Admin Panel</a></li>
                <li><a href="logout.php" id="signoutBtn">Signout</a></li>
            </ul>
        </nav>
        <button id="burger">&rarr;</button>
    </header>
    <main>
        <section class="welcome-container">
            <h2 id="welcomeMessage">Admin Panel</h2>
        </section>

        <!-- Users Management -->
        <section id="mainContainer">
            <h2 class="plainText">Users</h2>
            <?php if ($users->num_rows > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="plainText">Username</th>
                            <th class="plainText">Status</th>
                            <th class="plainText">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo $u['deactivated'] ? 'Deactivated' : 'Active'; ?></td>
                                <td>
                                    <form method="post" style="display:inline" class="actionForms">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                        <?php if ($u['deactivated']): ?>
                                            <button class="activateBtns" type="submit" name="activate_user">Activate</button>
                                        <?php else: ?>
                                            <button class="deactivateBtns" type="submit" name="deactivate_user">Deactivate</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </section>

        <section id="mainContainer">
            <h2 class="plainText">All Posts</h2>
            <?php if ($posts->num_rows > 0): ?>
                <?php while ($row = $posts->fetch_assoc()): ?>
                    <article>
                        <div class="mainDiv">
                            <div class="subDiv1">
                                <img class="postImgs" src="imgs/userForPost.png" alt="user image for post">
                                <a class="authors" href="profile.php?userID=<?php echo $row['author_id']; ?>">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </a>
                                <span class="categories <?php echo htmlspecialchars($row['category']); ?>">
                                    <?php echo htmlspecialchars($row['category']); ?>
                                </span>
                            </div>
                            <div class="subDiv2">
                                <h2 class="titles"><?php echo htmlspecialchars($row['title']); ?></h2>
                                <span class="dates">
                                    <?php echo htmlspecialchars(date("j/n/Y", strtotime($row['created_at']))); ?>
                                </span>
                            </div>
                            <p class="postBody"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                            <?php if (!empty($row['image_path'])): ?>
                                <img class="images" src="<?php echo htmlspecialchars($row['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($row['title']); ?> image">
                            <?php endif; ?>
                        </div>
                        <form action="view_post.php" method="get" style="display:none;">
                            <input type="hidden" name="postID" value="<?= htmlspecialchars($row['id']) ?>">
                        </form>
                        <form method="post" style="margin-top: .5rem;" class="postDeletionForms">
                            <input type="hidden" name="post_id" value="<?php echo (int)$row['id']; ?>">
                            <button class="deleteBtn" name="delete_post" type="submit">Delete</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="noPosts">There are no posts yet.</p>
            <?php endif; ?>
        </section>


    </main>
    <script src="JS/admin_dashboard.js"></script>
</body>

</html>