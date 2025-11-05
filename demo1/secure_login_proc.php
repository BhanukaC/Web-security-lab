<?php
// secure_login_proc.php
// Procedural mysqli - secure using prepared statements

$host = '127.0.0.1';
$user_db = 'root';
$pass_db = '';   // change if you use a password
$dbname = 'labdb';

$conn = mysqli_connect($host, $user_db, $pass_db, $dbname);
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Secure: use prepared statements
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND password = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
    mysqli_stmt_execute($stmt);

    // store result and check number of rows
    mysqli_stmt_store_result($stmt);
    $num = mysqli_stmt_num_rows($stmt);

    if ($num > 0) {
        echo "<p style='color:green'>Login successful! Welcome " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "</p>";
    } else {
        echo "<p style='color:red'>Invalid login</p>";
    }

    mysqli_stmt_close($stmt);
}
?>

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Secure Login (procedural)</title>
</head>

<body>
    <h2>Secure Login (Prepared Statements)</h2>
    <form method="post">
        <input name="username" placeholder="username"><br>
        <input name="password" placeholder="password" type="password"><br>
        <input type="submit" value="Login">
    </form>
</body>

</html>