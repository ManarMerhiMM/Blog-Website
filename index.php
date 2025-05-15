<?php
session_start();
include "connect.php";

// Read and sanitize inputs 
$postsPerPage = isset($_GET['postsPerPage']) ? max(1, (int)$_GET['postsPerPage']) : 5;
$currentPage  = isset($_GET['page'])         ? max(1, (int)$_GET['page'])         : 1;
$searchTerm   = '';
$searchSql    = '';
$params       = [];
$paramTypes   = '';

if (isset($_GET['search']) && isset($_GET['prompt'])) {
    $searchTerm = '%' . trim(htmlspecialchars($_GET['prompt'])) . '%';
    $searchSql  = "WHERE posts.title LIKE ? OR users.username LIKE ?";
    $paramTypes = 'ss';
    $params     = [$searchTerm, $searchTerm];
}

// Count total posts 
$countSql = "
    SELECT COUNT(*) AS total
    FROM posts
    JOIN users ON posts.author_id = users.id
    $searchSql
";
$countStmt = $conn->prepare($countSql);
if ($searchSql) {
    $refs = [];
    foreach ($params as $i => $val) {
        $refs[$i] = &$params[$i];
    }
    array_unshift($refs, $paramTypes);
    call_user_func_array([$countStmt, 'bind_param'], $refs);
}
$countStmt->execute();
$countStmt->bind_result($totalPosts);
$countStmt->fetch();
$countStmt->close();

$totalPages = (int)ceil($totalPosts / $postsPerPage);
$offset     = ($currentPage - 1) * $postsPerPage;

// Fetch posts for current page 
$dataSql = "
    SELECT posts.*, users.username
    FROM posts
    JOIN users ON posts.author_id = users.id
    $searchSql
    ORDER BY posts.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($dataSql);
if ($searchSql) {
    $types      = $paramTypes . 'ii';
    $bindValues = array_merge($params, [$postsPerPage, $offset]);
    $refs       = [];
    foreach ($bindValues as $i => $val) {
        $refs[$i] = &$bindValues[$i];
    }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
} else {
    $stmt->bind_param('ii', $postsPerPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="CSS/styles.css">
    <link rel="icon" href="imgs/homepageIcon.ico" type="image/x-icon">
</head>

<body>
    <header>
        <nav id="sidebar">
            <img id="logo" src="imgs/bloggerLogo.jpg" alt="Website Logo">
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <?php if (!empty($_SESSION['successful'])): ?>
                    <li><a href="dashboard.php"><?= htmlspecialchars($_SESSION['username']) ?></a></li>
                    <li><a href="logout.php">Signout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button id="burger">&rarr;</button>
    </header>

    <main>
        <div id="formHolder">
            <form method="get" id="searchForm">
                <input name="prompt" type="text"
                    placeholder="Search by post title/author..."
                    value="<?= htmlspecialchars($_GET['prompt'] ?? '') ?>">
                <input type="hidden" name="search" value="1">
                <input type="hidden" name="postsPerPage" value="<?= $postsPerPage ?>">
                <input type="hidden" name="page" value="1">
                <button type="submit">Search</button>
            </form>

            <form method="get" id="controls">
                <input name="postsPerPage" type="number" min="1"
                    placeholder="Posts per page..."
                    id="postsPerPage"
                    value="<?= $postsPerPage ?>">
                <?php if (isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="1">
                    <input type="hidden" name="prompt" value="<?= htmlspecialchars($_GET['prompt']) ?>">
                <?php endif; ?>
                <input type="hidden" name="page" value="1">
                <button type="submit">Apply</button>
            </form>
        </div>

        <div id="postContainer">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <article>
                        <div class="mainDiv">
                            <div class="subDiv1">
                                <img src="imgs/userForPost.png" class="postImgs" alt="User">
                                <span class="authors"><?= htmlspecialchars($row['username']) ?></span>
                            </div>
                            <div class="subDiv2">
                                <h2 class="titles"><?= htmlspecialchars($row['title']) ?></h2>
                                <span class="dates">
                                    <?= htmlspecialchars(date('j/n/Y', strtotime($row['created_at']))) ?>
                                </span>
                            </div>
                            <p class="postBody"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                            <form action="view_post.php" method="get" style="display:none;">
                                <input type="hidden" name="postID" value="<?= htmlspecialchars($row['id']) ?>">
                            </form>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <h2 id="emptySearch">No posts found :/</h2>
            <?php endif; ?>
        </div>

        <div id="paginationControls">
            <?php
            // Build one base array for every link
            $baseQuery = [];
            if (!empty($_GET['search']) && isset($_GET['prompt'])) {
                $baseQuery['search']       = '1';
                $baseQuery['prompt']       = htmlspecialchars($_GET['prompt']);
            }
            $baseQuery['postsPerPage'] = $postsPerPage;

            // Previous
            if ($currentPage > 1) {
                $tmp = $baseQuery;
                $tmp['page'] = $currentPage - 1;
                echo '<a href="index.php?' . http_build_query($tmp) . '" class="paginationBtns">'
                    . '&larr; Previous</a>';
            }

            // Pages 1..N
            for ($p = 1; $p <= $totalPages; $p++) {
                $tmp = $baseQuery;
                $tmp['page'] = $p;
                $active = $p === $currentPage ? ' activePage' : '';
                echo '<a href="index.php?' . http_build_query($tmp)
                    . '" class="paginationBtns' . $active . '">'
                    . $p . '</a>';
            }

            // Next
            if ($currentPage < $totalPages) {
                $tmp = $baseQuery;
                $tmp['page'] = $currentPage + 1;
                echo '<a href="index.php?' . http_build_query($tmp) . '" class="paginationBtns">'
                    . 'Next &rarr;</a>';
            }
            ?>
        </div>
    </main>

    <script src="JS/main.js"></script>
</body>

</html>