<?php
session_start();
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

// Fetch requests
$stmt = $pdo->query("SELECT * FROM requests ORDER BY created_at DESC");
$requests = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Incoming Requests — Admin — Web Sprint</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,sans-serif;background:#f3f4f6;padding:0;margin:0}
header{background:#111827;color:#fff;padding:12px 18px;display:flex;justify-content:space-between;align-items:center}
.container{max-width:1100px;margin:18px auto;padding:0 16px}
.table{background:#fff;padding:12px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.06);overflow:auto}
table{width:100%;border-collapse:collapse}
th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
a.btn{display:inline-block;padding:6px 10px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none}
</style>
</head>
<body>
<header>
  <div><strong>Web Sprint Admin</strong></div>
  <div><a href="logout.php" style="color:#fff">Logout</a></div>
</header>

<div class="container">
  <h2>Incoming Requests</h2>
  <div class="table">
    <table>
      <thead><tr><th>ID</th><th>Date</th><th>Name</th><th>Email</th><th>Phone</th><th>Package</th><th>Business</th><th>Action</th></tr></thead>
      <tbody>
        <?php if (!$requests): ?>
          <tr><td colspan="8">No requests yet.</td></tr>
        <?php else: foreach($requests as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['id'])?></td>
            <td><?=htmlspecialchars($r['created_at'])?></td>
            <td><?=htmlspecialchars($r['name'])?></td>
            <td><?=htmlspecialchars($r['email'])?></td>
            <td><?=htmlspecialchars($r['phone'])?></td>
            <td><?=htmlspecialchars($r['package'])?></td>
            <td><?=htmlspecialchars($r['business'])?></td>
            <td>
              <a class="btn" href="request_view.php?id=<?=intval($r['id'])?>">View</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
