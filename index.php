<?php
session_start();
include "connect.php";

// --- 1) Read and sanitize inputs ---
$postsPerPage = isset($_GET['postsPerPage']) ? max(1, (int)$_GET['postsPerPage']) : 5;
$currentPage  = isset($_GET['page'])         ? max(1, (int)$_GET['page'])         : 1;

// Prepare search filters
$searchTerm = '';
$category   = '';
$params     = [];
$paramTypes = '';
$searchSqlParts = [];

if (isset($_GET['search'])) {
    // Text search
    if (!empty($_GET['prompt'])) {
        $searchTerm = '%' . trim(htmlspecialchars($_GET['prompt'])) . '%';
        $searchSqlParts[] = "(posts.title LIKE ? OR users.username LIKE ? OR posts.content LIKE ? )";
        $paramTypes .= 'sss';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    // Category filter
    if (!empty($_GET['category'])) {
        $category = trim(htmlspecialchars($_GET['category']));
        $searchSqlParts[] = "posts.category = ?";
        $paramTypes .= 's';
        $params[] = $category;
    }
}

// Combine WHERE clause
$searchSql = '';
if (count($searchSqlParts) > 0) {
    $searchSql = 'WHERE ' . implode(' AND ', $searchSqlParts);
}

// --- 2) Count total posts ---
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

// --- 3) Fetch posts for current page ---
$dataSql = "
    SELECT posts.*, users.username
    FROM posts
    JOIN users ON posts.author_id = users.id
    $searchSql
    ORDER BY posts.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($dataSql);

// Bind parameters including pagination
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
                    <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"]) { ?>
                        <li><a href="admin_dashboard.php">Admin Panel</a></li>
                    <?php } ?>
                    <li><a href="logout.php" id="signoutBtn">Signout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button id="burger">&rarr;</button>
    </header>

    <main>
        <div id="formHolder">
            <!-- Search & Filter Form -->
            <form method="get" id="searchForm">
                <input name="prompt" type="text" placeholder="Search..."
                    value="<?= htmlspecialchars($_GET['prompt'] ?? '') ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                    $catResult = $conn->query("SELECT name FROM categories ORDER BY name ASC");
                    while ($cat = $catResult->fetch_assoc()) {
                        $sel = (isset($_GET['category']) && $_GET['category'] === $cat['name']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($cat['name']) . '" ' . $sel . '>'
                            . htmlspecialchars($cat['name']) . '</option>';
                    }
                    ?>
                </select>
                <input type="hidden" name="search" value="1">
                <input type="hidden" name="postsPerPage" value="<?= $postsPerPage ?>">
                <input type="hidden" name="page" value="1">
                <button type="submit">Search</button>
            </form>

            <!-- Posts-per-Page Form -->
            <form method="get" id="controls">
                <input name="postsPerPage" type="number" min="1" placeholder="Posts per page..."
                    id="postsPerPage" value="<?= $postsPerPage ?>">
                <?php if (isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="1">
                    <input type="hidden" name="prompt" value="<?= htmlspecialchars($_GET['prompt'] ?? '') ?>">
                    <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category'] ?? '') ?>">
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
                                <a class="authors" href="profile.php?userID=<?php echo "{$row["author_id"]}";; ?>"><?= htmlspecialchars($row['username']) ?></a>
                                <span class="categories <?php echo "{$row["category"]}"; ?>"><?= $row["category"] ?></span>
                            </div>
                            <div class="subDiv2">
                                <h2 class="titles"><?= htmlspecialchars($row['title']) ?></h2>
                                <span class="dates"><?= htmlspecialchars(date('j/n/Y', strtotime($row['created_at']))) ?></span>
                            </div>
                            <p class="postBody"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                            <?php if (isset($row["image_path"])) { ?>
                                <img class="images" src="<?php echo $row["image_path"]; ?>" alt="<?php echo "{$row["title"]} image" ?>">
                            <?php } ?>
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
            $baseQuery = [];
            if (!empty($_GET['search'])) {
                $baseQuery['search']      = '1';
                if (isset($_GET['prompt']))   $baseQuery['prompt']   = htmlspecialchars($_GET['prompt']);
                if (isset($_GET['category'])) $baseQuery['category'] = htmlspecialchars($_GET['category']);
            }
            $baseQuery['postsPerPage'] = $postsPerPage;

            // Previous
            if ($currentPage > 1) {
                $tmp = $baseQuery;
                $tmp['page'] = $currentPage - 1;
                echo '<a href="index.php?' . http_build_query($tmp) . '" class="paginationBtns">&larr; Previous</a>';
            }
            // Page links
            for ($p = 1; $p <= $totalPages; $p++) {
                $tmp = $baseQuery;
                $tmp['page'] = $p;
                $active = $p === $currentPage ? ' activePage' : '';
                echo '<a href="index.php?' . http_build_query($tmp) . '" class="paginationBtns' . $active . '">' . $p . '</a>';
            }
            // Next
            if ($currentPage < $totalPages) {
                $tmp = $baseQuery;
                $tmp['page'] = $currentPage + 1;
                echo '<a href="index.php?' . http_build_query($tmp) . '" class="paginationBtns">Next &rarr;</a>';
            }
            ?>
        </div>
    </main>
    <script src="JS/main.js"></script>
</body>

</html>