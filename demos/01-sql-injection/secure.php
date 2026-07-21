<?php
// secure.php - prepared statements + hashed password verification
session_start();

$conn = mysqli_connect('db', 'root', 'root', 'seclab');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT password_hash FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hash);
    $found = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($found && password_verify($password, $hash)) {
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        echo "<p style='color:green'>Login successful! Welcome " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "</p>";
    } else {
        echo "<p style='color:red'>Invalid login</p>";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Secure Login</title></head>
<body>
  <h2>Secure Login (Prepared Statements + password_verify)</h2>
  <form method="post">
    <input name="username" placeholder="username"><br>
    <input name="password" placeholder="password" type="password"><br>
    <input type="submit" value="Login">
  </form>
</body>
</html>
