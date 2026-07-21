<?php
// vulnerable.php - CSRF: state-changing action accepts GET, no token
session_start();

$conn = mysqli_connect('db', 'root', 'root', 'seclab');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

$sender = 'student1'; // simulated logged-in user

if (isset($_GET['to'])) {
    $to = $_GET['to'];
    $amount = floatval($_GET['amount'] ?? 0);

    $stmt = mysqli_prepare($conn, "INSERT INTO transfers (sender, receiver, amount) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssd', $sender, $to, $amount);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<p>Transfer complete: $amount to " . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . "</p>";
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Vulnerable Bank</title></head>
<body>
  <h2>Bank Transfer (Vulnerable to CSRF)</h2>
  <form method="get">
    To: <input name="to"><br>
    Amount: <input name="amount"><br>
    <input type="submit" value="Transfer">
  </form>
</body>
</html>
