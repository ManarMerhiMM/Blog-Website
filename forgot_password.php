<?php
session_start();
include "connect.php";

// Initialize message
$message = "";

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Error: Invalid email format.";
    } else {
        // Check if user with this email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            $user_id = $user['id'];

            // Generate a secure token (even if collision happens which is extremely unlikely it is handled)
            do {
                $token = bin2hex(random_bytes(16));
                $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $token);
                $success = $stmt->execute();
            } while (!$success && $conn->errno === 1062); // Handle rare token collision

            $reset_link = "http://localhost/reset_password.php?token={$token}";
            $subject = "Password Reset Request";
            $body = "Click the link below to reset your password:\n\n{$reset_link}\n\nNote: This link will expire in 1 hour.";
            $headers = "From: no-reply@myBlog.com";

            if (mail($email, $subject, $body, $headers)) {
                $message = "Check your email for the reset link.";
            } else {
                $message = "Failed to send email. Please try again later.";
            }
        } else {
            $message = "Error: No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="icon" href="imgs/forgotPasswordIcon.ico" type="image/x-icon">
    <link rel="stylesheet" href="CSS/forgot_password.css">
</head>

<body>
    <form method="POST" action="forgot_password.php" id="forgotForm">
        <p id="errors" style="<?php echo $message ? 'display:block' : 'display:none'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>

        <div class="formContainers">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" id="email" placeholder="example@domain.com">
        </div>

        <div class="btnControls">
            <button type="submit">Send Reset Link</button>
            <button type="button"><a href="login.php">Back to Login</a></button>
        </div>
    </form>

    <script src="JS/forgot_password.js"></script>
</body>

</html>