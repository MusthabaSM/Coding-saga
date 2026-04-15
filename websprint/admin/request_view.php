<?php
session_start();
require_once __DIR__ . '/../config.php';
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: requests.php'); exit; }

// Load request
$stmt = $pdo->prepare("SELECT * FROM requests WHERE id = :id");
$stmt->execute([':id'=>$id]);
$req = $stmt->fetch();
if (!$req) { header('Location: requests.php'); exit; }

// POST handling: create order + produce pay link
$created_link = '';
$manualMessage = '';
$waHref = '';
$mailtoHref = '';
$phoneNormalized = '';
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_link'])) {
    // Determine advance amount based on package
    $package = strtolower($req['package']);
    if ($package === 'basic') $advance = 200;
    elseif ($package === 'business') $advance = 500;
    elseif ($package === 'premium') $advance = 800;
    else $advance = 200;

    // Optional: include base_price_info
    $base_info = 'Starts from ';
    if ($package === 'basic') $base_info .= '₹1,999';
    elseif ($package === 'business') $base_info .= '₹5,999';
    elseif ($package === 'premium') $base_info .= '₹11,999';

    // Normalize phone for DB (store raw as given)
    $phoneRaw = (string)($req['phone'] ?? '');
    $phoneForDb = $phoneRaw !== '' ? $phoneRaw : null;

    // Create order in DB with status 'pending' and link to request
    try {
        $stmt2 = $pdo->prepare("INSERT INTO orders (request_id,name,email,phone,business,package,amount,currency,gateway,status,base_price_info,created_at) VALUES (:request_id,:name,:email,:phone,:business,:package,:amount,'INR','razorpay','pending',:base,NOW())");
        $stmt2->execute([
            ':request_id'=>$req['id'],
            ':name'=>$req['name'],
            ':email'=>$req['email'],
            ':phone'=>$phoneForDb,
            ':business'=>$req['business'] ?: 'General',
            ':package'=>$req['package'],
            ':amount'=>$advance,
            ':base'=>$base_info
        ]);
        $orderId = $pdo->lastInsertId();
    } catch (Exception $e) {
        $notice = 'Database error while creating order: ' . $e->getMessage();
        $orderId = 0;
    }

    if (!empty($orderId)) {
        // Public pay URL
        $payUrl = rtrim(BASE_URL, '/') . '/pay_advance.php?order_id=' . intval($orderId);
        $created_link = $payUrl;

        // Build a message to send to customer (editable)
        $manualMessage = "Hi {$req['name']},\n\n"
            . "We have prepared a secure payment link to pay the advance for your website request (Request #{$req['id']}).\n\n"
            . "Package: {$req['package']}\n"
            . "Advance to pay: ₹{$advance}\n\n"
            . "Please pay using the link below to confirm your order:\n"
            . "{$payUrl}\n\n"
            . "If you need help, reply here.\n\n— Web Sprint";

        // Prepare WhatsApp web link (wa.me)
        $phoneRaw = (string)($req['phone'] ?? '');
        $digits = preg_replace('/\D+/', '', $phoneRaw);
        if ($digits !== '') {
            // If 10 digits (likely India), prepend country code +91
            if (strlen($digits) === 10) $digits = '91' . $digits;
            // Save normalized digits for wa.me (no +)
            $phoneNormalized = $digits;
            $waText = rawurlencode($manualMessage);
            $waHref = "https://wa.me/{$phoneNormalized}?text={$waText}";
        } else {
            $waHref = '';
        }

        // Prepare mailto link (subject + body)
        $subject = rawurlencode("Payment link for your Web Sprint request #{$req['id']}");
        $body = rawurlencode($manualMessage);
        $emailTo = rawurlencode($req['email'] ?? '');
        if ($emailTo) $mailtoHref = "mailto:{$emailTo}?subject={$subject}&body={$body}";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Request #<?=htmlspecialchars($req['id'])?> — Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,sans-serif;background:#f3f4f6;padding:0;margin:0}
.header{background:#111827;color:#fff;padding:12px 18px;display:flex;justify-content:space-between}
.container{max-width:920px;margin:18px auto;padding:16px}
.card{background:#fff;padding:16px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.06)}
label{font-weight:700;margin-top:8px;display:block}
.value{margin-bottom:8px}
.btn{display:inline-block;padding:8px 12px;background:#0ea5a4;color:#fff;border-radius:6px;text-decoration:none;border:0;cursor:pointer}
.form-btn{padding:8px 12px;background:#2563eb;color:#fff;border:0;border-radius:6px;cursor:pointer}
.note{font-size:13px;color:#6b7280;margin-top:8px}
.result{margin-top:12px;padding:12px;border-radius:6px}
.success{background:#ecfdf5;border-left:4px solid #10b981}
.failure{background:#fff1f2;border-left:4px solid #ef4444}
.small{font-size:13px;color:#374151}
.copy-row{display:flex;gap:8px;align-items:center;margin-top:8px}
.copy-input{flex:1;padding:10px;border:1px solid #e5e7eb;border-radius:6px}
.icon{display:inline-block;padding:8px 12px;border-radius:6px;background:#111;color:#fff;text-decoration:none}
.icon.mail{background:#2563eb}
.icon.wa{background:#25D366}
</style>
</head>
<body>
<div class="header"><div><strong>Web Sprint Admin</strong></div><div><a href="requests.php" style="color:#fff">Back</a> <a href="logout.php" style="color:#fff;margin-left:12px">Logout</a></div></div>

<div class="container">
  <div class="card">
    <h2>Request #<?=htmlspecialchars($req['id'])?></h2>

    <label>Name</label><div class="value"><?=htmlspecialchars($req['name'])?></div>
    <label>Email</label><div class="value"><?=htmlspecialchars($req['email'])?></div>
    <label>Phone</label><div class="value"><?=htmlspecialchars($req['phone'])?></div>
    <label>Package</label><div class="value"><?=htmlspecialchars($req['package'])?></div>
    <label>Business</label><div class="value"><?=htmlspecialchars($req['business'])?></div>
    <label>Requirements</label><div class="value"><?=nl2br(htmlspecialchars($req['requirements']))?></div>

    <form method="post" style="margin-top:12px">
      <button name="create_link" class="form-btn" type="submit">Create Pay Link (advance)</button>
    </form>

    <?php if ($notice): ?>
      <div class="result failure"><?=htmlspecialchars($notice)?></div>
    <?php endif; ?>

    <?php if ($created_link): ?>
      <div class="result success">
        <strong>Payment link created:</strong>
        <div style="margin-top:8px">
          <input id="payLink" class="copy-input" readonly value="<?=htmlspecialchars($created_link)?>">
        </div>

        <div class="copy-row" style="margin-top:8px">
          <button id="copyBtn" class="btn">Copy link</button>
          <a id="openPay" class="icon" href="<?=htmlspecialchars($created_link)?>" target="_blank">Open link</a>

          <!-- Email button (mailto:) -->
          <?php if ($mailtoHref): ?>
            <a id="emailBtn" class="icon mail" href="<?=htmlspecialchars($mailtoHref)?>">Email</a>
          <?php else: ?>
            <span class="small" style="margin-left:6px">No email available.</span>
          <?php endif; ?>

          <!-- WhatsApp button (wa.me) -->
          <?php if ($waHref): ?>
            <a id="waOpen" class="icon wa" href="<?=htmlspecialchars($waHref)?>" target="_blank">WhatsApp</a>
          <?php else: ?>
            <span class="small" style="margin-left:6px">No valid phone to open WhatsApp link.</span>
          <?php endif; ?>
        </div>

        <div style="margin-top:12px">
          <label>Message to send to customer (editable)</label>
          <textarea id="msgBox" style="width:100%;min-height:120px;padding:10px;border-radius:6px;border:1px solid #e5e7eb"><?=htmlspecialchars($manualMessage)?></textarea>

          <div class="copy-row" style="margin-top:8px">
            <button id="copyMsg" class="btn">Copy message</button>

            <?php if ($phoneNormalized): ?>
              <a id="waOpenMsg" class="icon wa" href="<?= 'https://wa.me/' . $phoneNormalized . '?text=' . rawurlencode($manualMessage) ?>" target="_blank">Open WhatsApp (message)</a>
            <?php endif; ?>

            <?php if ($mailtoHref): ?>
              <a id="emailOpenMsg" class="icon mail" href="<?=htmlspecialchars($mailtoHref)?>">Open Email (message)</a>
            <?php endif; ?>

          </div>

          <div class="note" style="margin-top:10px">
            You can send the payment link via WhatsApp or email manually. The WhatsApp buttons use <code>wa.me</code> links and open WhatsApp Web (or mobile app) with a pre-filled message. Edit the message above if you wish.
          </div>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<script>
// Copy helpers
document.getElementById('copyBtn')?.addEventListener('click', function(){
  var link = document.getElementById('payLink');
  try {
    navigator.clipboard.writeText(link.value).then(function(){
      alert('Payment link copied to clipboard');
    }, function(){
      try { link.select(); document.execCommand('copy'); alert('Payment link copied'); } catch(e){ alert('Copy failed. Select and copy manually.'); }
    });
  } catch(e) {
    try { link.select(); document.execCommand('copy'); alert('Payment link copied'); } catch(e){ alert('Copy failed. Select and copy manually.'); }
  }
});

document.getElementById('copyMsg')?.addEventListener('click', function(){
  var msg = document.getElementById('msgBox').value;
  try {
    navigator.clipboard.writeText(msg).then(function(){
      alert('Message copied to clipboard');
    }, function(){
      alert('Unable to copy automatically — please select and copy the message manually.');
    });
  } catch(e){
    alert('Unable to copy automatically — please select and copy the message manually.');
  }
});
</script>
</body>
</html>
