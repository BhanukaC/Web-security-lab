<?php
// vulnerable_comment_proc.php
session_start(); // keep comments in session for demo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['comments'])) {
        $_SESSION['comments'] = array();
    }
    // VULNERABLE: storing and later outputting raw user input
    $_SESSION['comments'][] = $_POST['comment'] ?? '';
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Vulnerable Comments</title>
</head>

<body>
    <h2>Vulnerable Comment Box (XSS demo)</h2>
    <form method="post">
        <input name="comment" placeholder="Your comment"><br>
        <input type="submit" value="Post">
    </form>

    <h3>Comments</h3>
    <div>
        <?php
        if (!empty($_SESSION['comments'])) {
            foreach ($_SESSION['comments'] as $c) {
                // VULNERABLE: echoing raw HTML leads to XSS
                echo $c . '<hr>';
            }
        }
        ?>
    </div>
</body>

</html>