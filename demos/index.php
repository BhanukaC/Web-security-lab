<?php
$demos = [
    '01-sql-injection/vulnerable.php',
    '01-sql-injection/secure.php',
    '02-xss/vulnerable.php',
    '02-xss/secure.php',
    '03-csrf/vulnerable.php',
    '03-csrf/secure.php',
    '03-csrf/attacker.html',
    '05-secrets/leaky-config.php',
];
?>
<!DOCTYPE html>
<html>
<head><title>Web Security Lab</title></head>
<body>
<ul>
<?php foreach ($demos as $demo): ?>
    <li><a href="<?= htmlspecialchars($demo) ?>"><?= htmlspecialchars($demo) ?></a></li>
<?php endforeach; ?>
</ul>
</body>
</html>
