<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email) {
        $line = date('c') . " | $name | $email | " . str_replace(["\n","\r"], ' ', $message) . PHP_EOL;
        file_put_contents(__DIR__ . '/contacts.txt', $line, FILE_APPEND);
        $saved = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Contact — MilesBuddy</title>
<style>
:root{--primary:#6C5CE7;--dark:#222;--muted:#6b7280;--bg:#f7f7fb;--card:#fff}
*{margin:0;padding:0;box-sizing:border-box;font-family:Inter,Arial,sans-serif}
body{background:var(--bg);color:var(--dark)}
a{text-decoration:none;color:var(--primary)}
.wrap{max-width:1100px;margin:auto;padding:25px}
header{background:#fff;border-bottom:1px solid #eee}
header .wrap{display:flex;justify-content:space-between;align-items:center}
header nav a{margin-left:16px;color:#777;font-weight:600}
h1{margin:18px 0}
input,textarea{width:100%;padding:10px;margin-top:8px;border:1px solid #ddd;border-radius:6px}
button{padding:10px 16px;border:0;background:#111;color:#fff;border-radius:6px;margin-top:12px;cursor:pointer}
.notice{background:#e6ffed;border-left:4px solid #2ecc71;padding:10px;margin-bottom:10px}
footer{text-align:center;padding:18px 0;margin-top:30px;color:var(--muted)}
</style>
</head>
<body>

<header>
  <div class="wrap">
    <div class="logo"><strong>MilesBuddy</strong></div>
    <nav>
      <a href="index.php">Home</a>
      <a href="portfolio.php">Portfolio</a>
      <a href="pricing.php">Pricing</a>
      <a href="contact.php">Contact</a>
    </nav>
  </div>
</header>

<main class="wrap">
  <h1>Contact</h1>

  <?php if (!empty($saved)): ?>
    <div class="notice">Thanks! We received your message.</div>
  <?php endif; ?>

  <form method="post">
    <input name="name" placeholder="Your Name" required>
    <input name="email" type="email" placeholder="Email" required>
    <textarea name="message" rows="4" placeholder="How can we help?"></textarea>
    <button>Send Message</button>
  </form>

  <p style="margin-top:20px;"><a href="index.php">← Back to Home</a></p>
</main>

<footer>
  <p>© <?=date('Y')?> MilesBuddy</p>
</footer>
</body>
</html>
