<?php
// bank_secure_proc.php
session_start();

$host = '127.0.0.1';
$user_db = 'root';
$pass_db = '';
$dbname = 'labdb';

$conn = mysqli_connect($host, $user_db, $pass_db, $dbname);
if (!$conn) {
    die('DB connection error: ' . mysqli_connect_error());
}

// ensure CSRF token exists in session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_SESSION['csrf_token'];
    echo '<!doctype html>
    <html><head><meta charset="utf-8"><title>Bank Secure</title></head><body>
    <h2>Bank Transfer (CSRF Protected)</h2>
    <form method="post">
      <input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">
      To: <input name="to"><br>
      Amount: <input name="amount"><br>
      <input type="submit" value="Transfer">
    </form>
    </body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_token)) {
        die('Invalid CSRF token');
    }

    // Simulate logged-in sender
    $sender = 'student1';
    $to = $_POST['to'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);

    $stmt = mysqli_prepare($conn, "INSERT INTO transfers (sender, receiver, amount) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, 'ssd', $sender, $to, $amount);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "Transfer complete: $amount to $to";
}
