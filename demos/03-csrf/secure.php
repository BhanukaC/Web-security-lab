<?php
// secure.php - CSRF protected transfer: POST only + per-session token
session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
session_start();

$conn = mysqli_connect('db', 'root', 'root', 'seclab');
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$sender = 'student1'; // simulated logged-in user
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_token)) {
        die('Invalid CSRF token');
    }

    $to = $_POST['to'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);

    $stmt = mysqli_prepare($conn, "INSERT INTO transfers (sender, receiver, amount) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssd', $sender, $to, $amount);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $message = "Transfer complete: $amount to " . htmlspecialchars($to, ENT_QUOTES, 'UTF-8');
}
$token = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Secure Bank</title></head>
<body>
  <h2>Bank Transfer (CSRF Protected)</h2>
  <p><?= $message ?></p>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= $token ?>">
    To: <input name="to"><br>
    Amount: <input name="amount"><br>
    <input type="submit" value="Transfer">
  </form>
</body>
</html>
