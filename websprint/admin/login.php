<?php
session_start();
require_once __DIR__ . '/../config.php';

// If already logged in, go to orders page
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: orders.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login — Web Sprint</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:0}
.wrap{max-width:400px;margin:80px auto;background:#fff;border-radius:12px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,0.08)}
h1{margin-top:0;margin-bottom:12px;font-size:22px}
label{display:block;margin-top:12px;font-size:14px}
input{width:100%;padding:9px;margin-top:4px;border-radius:6px;border:1px solid #d1d5db}
button{margin-top:16px;width:100%;padding:10px;border:0;border-radius:8px;background:#4f46e5;color:#fff;font-weight:600;cursor:pointer}
.error{margin-top:10px;color:#b91c1c;font-size:13px}
small{color:#6b7280;font-size:12px}
</style>
</head>
<body>

<div class="wrap">
  <h1>Web Sprint Admin</h1>
  <small>Sign in to view orders.</small>

  <?php if ($error): ?>
    <div class="error"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>

  <form method="post">
    <label>Username
      <input type="text" name="username" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
  </form>
</div>

</body>
</html>
