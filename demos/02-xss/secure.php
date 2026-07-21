<?php
// secure.php - output encoding + hardening headers
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
header("Content-Security-Policy: default-src 'self'");

$conn = mysqli_connect('db', 'root', 'root', 'seclab');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (body) VALUES (?)");
    mysqli_stmt_bind_param($stmt, 's', $comment);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$result = mysqli_query($conn, "SELECT body FROM comments ORDER BY id DESC");
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Secure Comments</title></head>
<body>
  <h2>Secure Comment Box (Output Encoding)</h2>
  <form method="post">
    <input name="comment" placeholder="Your comment"><br>
    <input type="submit" value="Post">
  </form>
  <h3>Comments</h3>
  <div>
    <?php while ($row = mysqli_fetch_assoc($result)) {
        // SAFE: encoded for the HTML body context
        echo htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8') . '<hr>';
    } ?>
  </div>
</body>
</html>
