<?php
// secure_comment_proc.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['comments'])) {
        $_SESSION['comments'] = array();
    }
    // We store the raw input (or we could sanitize before storing) but
    // always escape on output for the correct context.
    $_SESSION['comments'][] = $_POST['comment'] ?? '';
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Secure Comments</title>
</head>

<body>
    <h2>Secure Comment Box (Output Encoding)</h2>
    <form method="post">
        <input name="comment" placeholder="Your comment"><br>
        <input type="submit" value="Post">
    </form>

    <h3>Comments</h3>
    <div>
        <?php
        if (!empty($_SESSION['comments'])) {
            foreach ($_SESSION['comments'] as $c) {
                // SAFE: encode output for HTML body context
                echo htmlspecialchars($c, ENT_QUOTES, 'UTF-8') . '<hr>';
            }
        }
        ?>
    </div>
</body>

</html>