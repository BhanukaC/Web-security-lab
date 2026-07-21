<?php
// vulnerable.php - SQL injection + plaintext password comparison
// Do not copy these patterns outside this lab.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// PHP 8.1+ made mysqli throw on query errors by default (MYSQLI_REPORT_ERROR |
// MYSQLI_REPORT_STRICT). A malformed injection attempt would otherwise crash
// with an uncaught mysqli_sql_exception and a raw stack trace instead of
// falling through to the existing "Invalid login" handling below, which
// already expects mysqli_query() to return false on error.
mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect('db', 'root', 'root', 'seclab');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // VULNERABLE: user input concatenated directly into the query,
    // and the password compared as plaintext.
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $res = mysqli_query($conn, $query);

    if ($res && mysqli_num_rows($res) > 0) {
        echo "<p style='color:green'>Login successful! Welcome " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "</p>";
    } else {
        echo "<p style='color:red'>Invalid login</p>";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Vulnerable Login</title></head>
<body>
  <h2>Vulnerable Login (SQL Injection)</h2>
  <form method="post">
    <input name="username" placeholder="username"><br>
    <input name="password" placeholder="password" type="password"><br>
    <input type="submit" value="Login">
  </form>
</body>
</html>
