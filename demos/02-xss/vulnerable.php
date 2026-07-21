<?php
// vulnerable.php - stored XSS via unescaped comment output
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
<head><meta charset="utf-8"><title>Vulnerable Comments</title></head>
<body>
  <h2>Vulnerable Comment Box (XSS)</h2>
  <form method="post">
    <input name="comment" placeholder="Your comment"><br>
    <input type="submit" value="Post">
  </form>
  <h3>Comments</h3>
  <div>
    <?php while ($row = mysqli_fetch_assoc($result)) {
        // VULNERABLE: raw HTML echoed straight into the page
        echo $row['body'] . '<hr>';
    } ?>
  </div>
</body>
</html>
