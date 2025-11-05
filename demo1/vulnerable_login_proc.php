<?php
// vulnerable_login_proc.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$host = '127.0.0.1';
$user_db = 'root';
$pass_db = '';    // change if you use a password
$dbname = 'labdb';

$conn = mysqli_connect($host, $user_db, $pass_db, $dbname);
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // VULNERABLE: concatenating input directly into SQL
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";

    //echo $query;

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

<head>
    <meta charset="utf-8">
    <title>Vulnerable Login (procedural)</title>
</head>

<body>
    <h2>Vulnerable Login (SQL Injection demo)</h2>
    <form method="post">
        <input name="username" placeholder="username"><br>
        <input name="password" placeholder="password" type="password"><br>
        <input type="submit" value="Login">
    </form>
</body>

</html>