<?php
session_start();
require_once __DIR__ . '/../config.php';

// Check admin login
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// --- Summary queries ---

// Total orders
$totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Paid orders
$paidOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'")->fetchColumn();

// Pending orders
$pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Total revenue (paid only)
$totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status = 'paid'")->fetchColumn();

// All orders (latest first)
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard — Web Sprint</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,sans-serif;background:#f3f4f6;margin:0;padding:0;color:#111827}
header{background:#111827;color:#e5e7eb;padding:14px 20px;display:flex;justify-content:space-between;align-items:center}
header a{color:#e5e7eb;text-decoration:none;font-size:13px;margin-left:12px}
main{max-width:1100px;margin:20px auto;padding:0 16px}
h1{margin:10px 0 6px 0;font-size:24px}
.subtitle{color:#6b7280;font-size:13px;margin-bottom:14px}

/* summary cards */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:20px}
.card{background:#fff;border-radius:12px;padding:14px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.06)}
.card-title{font-size:13px;color:#6b7280;margin-bottom:6px}
.card-value{font-size:22px;font-weight:700}
.card-note{font-size:11px;color:#9ca3af;margin-top:4px}

/* table */
.table-wrap{background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.06);overflow:auto;padding:12px;margin-top:10px}
table{width:100%;border-collapse:collapse;font-size:13px;min-width:900px}
th,td{padding:8px 6px;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap}
th{background:#f9fafb;font-weight:600;font-size:12px;color:#4b5563}
td.small{text-align:center}
td.email{max-width:200px;white-space:normal;word-break:break-all}
.status-badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px}
.status-paid{background:#dcfce7;color:#166534}
.status-pending{background:#fef9c3;color:#854d0e}
.status-failed{background:#fee2e2;color:#991b1b}
.status-cancelled{background:#e5e7eb;color:#4b5563}
.badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;background:#e0f2fe;color:#075985}
@media(max-width:600px){
  header{flex-direction:column;align-items:flex-start;gap:8px}
}
</style>
</head>
<body>

<header>
  <div>
    <strong>Web Sprint Admin</strong>
  </div>
  <div>
    <a href="../index.php">View site</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="requests.php">Requests</a>
    <a href="set_final_price.php">Set Final Price</a>
    <a href="logout.php">Logout</a>


  </div>
</header>

<main>
  <h1>Dashboard</h1>
  <div class="subtitle">
    Overview of website orders and revenue.
  </div>

  <section class="cards">
    <div class="card">
      <div class="card-title">Total Orders</div>
      <div class="card-value"><?=number_format($totalOrders)?></div>
      <div class="card-note">All time</div>
    </div>

    <div class="card">
      <div class="card-title">Paid Orders</div>
      <div class="card-value"><?=number_format($paidOrders)?></div>
      <div class="card-note">Orders marked as paid</div>
    </div>

    <div class="card">
      <div class="card-title">Pending Orders</div>
      <div class="card-value"><?=number_format($pendingOrders)?></div>
      <div class="card-note">Awaiting payment</div>
    </div>

    <div class="card">
      <div class="card-title">Total Revenue (Paid)</div>
      <div class="card-value">₹<?=number_format($totalRevenue)?></div>
      <div class="card-note">Based on paid orders only</div>
    </div>
  </section>

  <h2 style="font-size:18px;margin-top:10px;">All Orders</h2>
  <div class="subtitle">All orders placed, latest first.</div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Created</th>
          <th>Updated</th>
          <th>Name</th>
          <th>Email</th>
          <th>Business</th>
          <th>Package</th>
          <th>Amount</th>
          <th>Currency</th>
          <th>Gateway</th>
          <th>Status</th>
          <th>Razorpay Order ID</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$orders): ?>
          <tr>
            <td colspan="12">No orders yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td class="small"><?=htmlspecialchars($o['id'])?></td>
              <td><?=htmlspecialchars($o['created_at'])?></td>
              <td><?=htmlspecialchars($o['updated_at'])?></td>
              <td><?=htmlspecialchars($o['name'])?></td>
              <td class="email"><?=htmlspecialchars($o['email'])?></td>
              <td><?=htmlspecialchars($o['business'])?></td>
              <td><span class="badge"><?=htmlspecialchars($o['package'])?></span></td>
              <td class="small"><?=htmlspecialchars($o['amount'])?></td>
              <td class="small"><?=htmlspecialchars($o['currency'])?></td>
              <td class="small"><?=htmlspecialchars(strtoupper($o['gateway']))?></td>
              <td class="small">
                <?php
                  $status = $o['status'];
                  $class = 'status-badge ';
                  if ($status === 'paid') $class .= 'status-paid';
                  elseif ($status === 'pending') $class .= 'status-pending';
                  elseif ($status === 'failed') $class .= 'status-failed';
                  else $class .= 'status-cancelled';
                ?>
                <span class="<?=$class?>"><?=htmlspecialchars(ucfirst($status))?></span>
              </td>
              <td class="email"><?=htmlspecialchars($o['razorpay_order_id'])?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
