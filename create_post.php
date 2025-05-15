<?php
session_start();
include "connect.php";
$_SESSION["prev_page"] = "create_post.php";

// Redirect to session.php if the user is not successfully authenticated
if (!(isset($_SESSION["successful"]) && $_SESSION["successful"])) {
    header("Location: session.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = htmlspecialchars(trim($_POST["title"]));
    $content = htmlspecialchars(trim($_POST["content"]));
    $stmt = $conn->prepare("INSERT INTO posts (author_id, title, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $_SESSION["id"], $title, $content);
    $stmt->execute();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a post</title>
    <link rel="icon" href="imgs/createPostIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/create_post.css">
</head>

<body>
    <form action="create_post.php" method="post" id="createForm">
        <h2 id="formTitle">Add Post</h2>
        <p id="errors"></p>
        <input type="text" name="title" id="title" placeholder="Title...">
        <textarea name="content" id="content" placeholder="Body..."></textarea>
        <a href="dashboard.php"><button type="button">Back</button></a>
        <button type="submit" name="create">Post</button>
    </form>

    <script src="JS/create_post.js"></script>
</body>

</html>