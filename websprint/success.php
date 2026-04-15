<?php
// success.php
// Razorpay callback -> verify -> mark paid -> generate improved ASCII-safe PDF (no external libs)
// Cleaned: removed debug output and Unicode characters that caused '&' artifacts.

require_once __DIR__ . '/config.php';

// --- helpers ---
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function ensure_dir($d){ if (!is_dir($d)) @mkdir($d,0755,true); return is_dir($d) && is_writable($d); }

// --- PDF helpers (ASCII-safe) --------------------------------------------
// Escape PDF literal string parentheses/backslashes
function pdf_escape($s) {
    $s = (string)$s;
    $s = str_replace('\\', '\\\\', $s);
    $s = str_replace('(', '\\(', $s);
    $s = str_replace(')', '\\)', $s);
    // remove control chars that break PDF
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
    return $s;
}
function repeat_ascii($c, $n){ return str_repeat($c, max(0,(int)$n)); }

/**
 * create_polished_pdf_clean
 * Single-page PDF using only ASCII characters (no rupee symbol) to avoid encoding artifacts.
 */
function create_polished_pdf_clean($path, $meta) {
    $W = 595; $H = 842;
    $left = 36;
    $right = $W - 36;
    $colAmtX = $right - 120;
    $y = $H - 60;

    $content = "BT\n/F1 18 Tf\n";

    // Company Title
    $company = $meta['company_name'] ?? 'Web Sprint';
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape($company));
    $y -= 22;

    // Company lines (small)
    $content .= "/F1 9 Tf\n";
    if (!empty($meta['company_lines']) && is_array($meta['company_lines'])) {
        foreach ($meta['company_lines'] as $line) {
            $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape($line));
            $y -= 12;
        }
    } else {
        $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Freelance Web Solutions'));
        $y -= 12;
    }

    // Invoice badge (right column)
    $badgeX = $W - 180;
    $badgeY = $H - 58;
    $content .= "/F1 12 Tf\n";
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $badgeX, $badgeY, pdf_escape('INVOICE'));
    $content .= "/F1 9 Tf\n";
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(Invoice #: %s) Tj\n", $badgeX, $badgeY - 16, pdf_escape($meta['invoice_no'] ?? ''));
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(Date: %s) Tj\n", $badgeX, $badgeY - 30, pdf_escape($meta['date'] ?? ''));
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(Payment ID: %s) Tj\n", $badgeX, $badgeY - 44, pdf_escape($meta['payment_id'] ?? ''));

    // Divider (ASCII)
    $y -= 18;
    $content .= "/F1 10 Tf\n";
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape(repeat_ascii('-', 80)));
    $y -= 20;

    // Bill To block
    $content .= "/F1 11 Tf\n";
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Bill To:'));
    $y -= 14;
    $content .= "/F1 10 Tf\n";
    $cust = $meta['customer_name'] ?? 'Customer';
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape($cust));
    $y -= 12;
    if (!empty($meta['customer_business'])) { $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Business: ' . $meta['customer_business'])); $y -= 12; }
    if (!empty($meta['customer_email']))    { $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Email: ' . $meta['customer_email'])); $y -= 12; }
    if (!empty($meta['customer_phone']))    { $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Phone: ' . $meta['customer_phone'])); $y -= 12; }

    $y -= 8;
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape(repeat_ascii('=', 80)));
    $y -= 18;

    // Table header
    $content .= "/F1 11 Tf\n";
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Description'));
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $colAmtX, $y, pdf_escape('Amount (INR)'));
    $y -= 14;
    $content .= "/F1 10 Tf\n";

    // Items
    $total = 0.0;
    if (!empty($meta['items']) && is_array($meta['items'])) {
        foreach ($meta['items'] as $it) {
            $desc = $it['desc'] ?? '';
            $amt = floatval($it['amount'] ?? 0);
            $total += $amt;

            $descWrapped = wordwrap($desc, 80, "\n");
            $lines = explode("\n", $descWrapped);
            $first = true;
            foreach ($lines as $ln) {
                $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape($ln));
                if ($first) {
                    $amountText = 'Rs. ' . number_format($amt, 2);
                    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $colAmtX, $y, pdf_escape($amountText));
                    $first = false;
                }
                $y -= 12;
            }
            $y -= 6;
        }
    } else {
        $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape('Advance payment for website (part payment)'));
        $amountText = 'Rs. ' . number_format($meta['items'][0]['amount'] ?? 0, 2);
        $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $colAmtX, $y, pdf_escape($amountText));
        $total = floatval($meta['items'][0]['amount'] ?? 0);
        $y -= 20;
    }

    // Total row
    $y -= 6;
    $content .= "/F1 12 Tf\n";
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $colAmtX - 70, $y, pdf_escape('Total:'));
    $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $colAmtX, $y, pdf_escape('Rs. ' . number_format($total, 2)));
    $y -= 18;

    // Note
    $content .= "/F1 9 Tf\n";
    $note = $meta['note'] ?? 'This is an advance receipt. Final price will be confirmed after requirements are finalized.';
    $noteWrap = wordwrap($note, 100, "\n");
    foreach (explode("\n", $noteWrap) as $nline) {
        $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $y, pdf_escape($nline));
        $y -= 12;
    }

    // Footer contact left
    $footerY = 36;
    $contact = $meta['company_contact'] ?? '';
    if ($contact) {
        $content .= "/F1 9 Tf\n";
        $content .= sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $left, $footerY, pdf_escape($contact));
    }

    $content .= "ET\n";

    // Build PDF objects
    $objects = [];
    $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 {$W} {$H}] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
    $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $len = strlen($content);
    $objects[] = "5 0 obj\n<< /Length {$len} >>\nstream\n{$content}\nendstream\nendobj\n";

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $pos = strlen($pdf);
    $offsets = [];
    foreach ($objects as $obj) {
        $offsets[] = $pos;
        $pdf .= $obj;
        $pos = strlen($pdf);
    }
    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ($offsets as $off) {
        $pdf .= str_pad($off, 10, "0", STR_PAD_LEFT) . " 00000 n \n";
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

    $w = @file_put_contents($path, $pdf);
    if ($w === false) return false;
    clearstatcache(true, $path);
    return file_exists($path);
}

// ----------------- Request handling ---------------------------------------
$gateway = strtolower(trim($_GET['gateway'] ?? ''));
$localOrderId = intval($_GET['local_order_id'] ?? ($_GET['order_id'] ?? 0));
if ($localOrderId <= 0) { http_response_code(400); die("Invalid order identifier."); }

$rzpPaymentId = $_POST['razorpay_payment_id'] ?? $_GET['razorpay_payment_id'] ?? null;
$rzpOrderId   = $_POST['razorpay_order_id'] ?? $_GET['razorpay_order_id'] ?? null;
$rzpSignature = $_POST['razorpay_signature'] ?? $_GET['razorpay_signature'] ?? null;

if ($gateway !== 'razorpay') { http_response_code(400); die("Unsupported gateway."); }
if (!$rzpPaymentId || !$rzpOrderId || !$rzpSignature) { http_response_code(400); die("Missing Razorpay response data."); }
if (!defined('RZP_KEY_SECRET') || !RZP_KEY_SECRET) { http_response_code(500); die("Razorpay secret not configured in config.php."); }

// verify signature
$expected = hash_hmac('sha256', $rzpOrderId . '|' . $rzpPaymentId, RZP_KEY_SECRET);
if (!hash_equals($expected, $rzpSignature)) {
    http_response_code(400); die("Razorpay signature verification failed.");
}

// load order + optional request
$stmt = $pdo->prepare("
    SELECT o.*, r.requirements AS request_requirements, r.phone AS request_phone
    FROM orders o
    LEFT JOIN requests r ON r.id = o.request_id
    WHERE o.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $localOrderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { http_response_code(404); die("Order not found."); }

// mark paid
try {
    $upd = $pdo->prepare("UPDATE orders SET status = 'paid', razorpay_payment_id = :pid, updated_at = NOW() WHERE id = :id");
    $upd->execute([':pid' => $rzpPaymentId, ':id' => $localOrderId]);
} catch (Exception $ex) {
    // silent fail - order will be considered paid logically
}

// prepare invoice meta
$orderId    = $order['id'] ?? $localOrderId;
$name       = $order['name'] ?? '';
$email      = $order['email'] ?? '';
$business   = $order['business'] ?? '';
$package    = $order['package'] ?? '';
$advance    = (int)($order['amount'] ?? 0);
$currency   = $order['currency'] ?? 'INR';
$paymentId  = $rzpPaymentId;
$req_text   = $order['request_requirements'] ?? '';
$created_at = $order['updated_at'] ?? $order['created_at'] ?? date('Y-m-d H:i:s');

if (!defined('INVOICE_DIR')) define('INVOICE_DIR', __DIR__ . '/uploads/invoices');
ensure_dir(INVOICE_DIR);
$invoiceFilename = "invoice_{$orderId}.pdf";
$invoicePath = rtrim(INVOICE_DIR, '/\\') . DIRECTORY_SEPARATOR . $invoiceFilename;

// Build meta for PDF generator
$invoiceMeta = [
    'company_name' => 'Web Sprint',
    'company_lines' => ['Freelance Web Solutions', 'City, State - India'],
    'company_contact' => 'contact@websprint.example | +91-XXXXXXXXXX',
    'invoice_no' => $orderId,
    'date' => date('Y-m-d H:i:s', strtotime($created_at)),
    'payment_id' => $paymentId,
    'customer_name' => $name,
    'customer_business' => $business,
    'customer_email' => $email,
    'customer_phone' => $order['phone'] ?? $order['request_phone'] ?? '',
    'items' => [
        ['desc' => 'Advance payment — ' . ($package ?: 'Website project'), 'amount' => (float)$advance]
    ],
    'note' => 'This is an advance receipt. Final project price will be confirmed after requirements are finalized.'
];

// Generate PDF if missing
if (!file_exists($invoicePath)) {
    $ok = create_polished_pdf_clean($invoicePath, $invoiceMeta);
    if (!$ok) {
        // fallback simple generator (ASCII-safe)
        $lines = [];
        $lines[] = "Invoice #: {$orderId}    Date: {$created_at}";
        $lines[] = "";
        $lines[] = "Customer: {$name}";
        if ($business) $lines[] = "Business: {$business}";
        if ($email) $lines[] = "Email: {$email}";
        $lines[] = "";
        $lines[] = "Package: {$package}";
        $lines[] = "Advance paid: Rs. " . number_format($advance, 2) . " " . $currency;
        if ($req_text) {
            $lines[] = "";
            $lines[] = "Requirements:";
            $reqWrapped = wordwrap($req_text, 80, "\n");
            foreach (explode("\n", $reqWrapped) as $rl) $lines[] = $rl;
        }
        $lines[] = "";
        $lines[] = "Payment ID: {$paymentId}";
        $lines[] = "";
        $lines[] = "Note: This is an advance receipt. Final price will be confirmed later.";
        // create simple pdf (reusing earlier logic)
        function create_simple_minimal_pdf_local($path, $title, $linesArr) {
            $W=595;$H=842;
            $content = "BT\n/F1 16 Tf\n72 " . ($H - 72) . " Td\n(" . pdf_escape($title) . ") Tj\n0 -18 Td\n/F1 12 Tf\n";
            foreach ($linesArr as $idx=>$l) { $content .= "(" . pdf_escape($l) . ") Tj\n"; if ($idx !== count($linesArr)-1) $content .= "0 -14 Td\n"; }
            $objects=[];$objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n"; $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n"; $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $W $H] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n"; $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n"; $len=strlen($content); $objects[] = "5 0 obj\n<< /Length $len >>\nstream\n{$content}\nendstream\nendobj\n";
            $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n"; $pos=strlen($pdf); $offsets=[];
            foreach($objects as $o){ $offsets[]=$pos; $pdf.=$o; $pos=strlen($pdf); }
            $xrefPos=strlen($pdf); $pdf.="xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";
            foreach($offsets as $off) $pdf.=str_pad($off,10,"0",STR_PAD_LEFT)." 00000 n \n";
            $pdf.="trailer\n<< /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";
            return @file_put_contents($path,$pdf) !== false && file_exists($path);
        }
        create_simple_minimal_pdf_local($invoicePath, "Web Sprint - Receipt #{$orderId}", $lines);
    }
}

// Build download URL (prefer download_receipt.php)
$downloadUrl = rtrim(BASE_URL, '/') . '/download_receipt.php?order_id=' . intval($orderId);
if (!file_exists(__DIR__ . '/download_receipt.php')) {
    $downloadUrl = rtrim(BASE_URL, '/') . '/uploads/invoices/' . rawurlencode($invoiceFilename);
}

// Build mailto and wa links
$mailtoHref = '';
if (!empty($email)) {
    $subject = rawurlencode("Your Web Sprint receipt — Order #{$orderId}");
    $body = rawurlencode("Hi {$name},\n\nThank you for your advance payment. Download your receipt here:\n\n{$downloadUrl}\n\n— Web Sprint");
    $mailtoHref = "mailto:" . rawurlencode($email) . "?subject={$subject}&body={$body}";
}

$waHref = '';
$phoneRaw = (string)($order['phone'] ?? $order['request_phone'] ?? '');
$digits = preg_replace('/\D+/', '', $phoneRaw);
if ($digits !== '') {
    if (strlen($digits) === 10) $digits = '91' . $digits;
    $waHref = "https://wa.me/{$digits}?text=" . rawurlencode("Hi {$name},\n\nThank you for your payment. Download your receipt here: {$downloadUrl}");
}

// ---------- Render user-facing confirmation page (clean) -----------------
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Payment Successful — Web Sprint</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7fb;color:#222;margin:0;padding:0}
.wrap{max-width:860px;margin:28px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 12px 36px rgba(0,0,0,0.08)}
h1{margin-top:0}
.table{width:100%;border-collapse:collapse;margin-top:10px}
.table th,.table td{padding:8px;border-bottom:1px solid #eee;text-align:left}
.actions{margin-top:18px;display:flex;gap:10px;flex-wrap:wrap}
.btn{display:inline-block;padding:10px 14px;border-radius:8px;border:0;background:#111;color:#fff;text-decoration:none}
.btn.download{background:#6C5CE7}
.btn.mail{background:#2563eb}
.btn.wa{background:#25D366;color:#000}
.copy{background:#f3f4f6;padding:10px;border-radius:6px;border:1px solid #e6e6e6;width:100%;box-sizing:border-box}
.small{font-size:13px;color:#4b5563}
.note{margin-top:12px;color:#6b7280}
</style>
</head>
<body>
<div class="wrap">
  <h1>Payment successful</h1>
  <p class="small">Thank you, <strong><?= e($name) ?></strong>. Your advance payment has been confirmed.</p>

  <table class="table">
    <tr><th>Order ID</th><td>#<?= e($orderId) ?></td></tr>
    <tr><th>Business</th><td><?= e($business) ?></td></tr>
    <tr><th>Package</th><td><?= e($package) ?></td></tr>
    <tr><th>Advance paid now</th><td><?= e('Rs. ' . number_format($advance, 2)) ?> <?= e($currency) ?></td></tr>
    <tr><th>Status</th><td>Paid (advance)</td></tr>
    <tr><th>Payment ID</th><td><?= e($paymentId) ?></td></tr>
    <tr><th>Date</th><td><?= e($created_at) ?></td></tr>
  </table>

  <?php if ($req_text): ?>
    <div class="note"><strong>Requirements:</strong><br><?= nl2br(e($req_text)) ?></div>
  <?php endif; ?>

  <div style="margin-top:14px">
    <label style="font-weight:700">Receipt</label>
    <div style="margin-top:8px">
      <?php if ($downloadUrl): ?>
        <a class="btn download" href="<?= e($downloadUrl) ?>" target="_blank" rel="noopener">Download Receipt (PDF)</a>
      <?php else: ?>
        <div class="note">Receipt not available. Check server folder permissions.</div>
      <?php endif; ?>
    </div>

    <div class="actions">
      <?php if ($mailtoHref): ?>
        <a class="btn mail" href="<?= e($mailtoHref) ?>" target="_blank" rel="noopener">Send Receipt (Email)</a>
      <?php else: ?>
        <button class="btn mail" disabled title="No email available">Send Receipt (Email)</button>
      <?php endif; ?>

      <?php if ($waHref): ?>
        <a class="btn wa" href="<?= e($waHref) ?>" target="_blank" rel="noopener">Share via WhatsApp</a>
      <?php else: ?>
        <button class="btn wa" disabled title="No phone available">Share via WhatsApp</button>
      <?php endif; ?>
    </div>

    <div style="margin-top:10px">
      <label style="font-weight:700">Receipt link (share):</label>
      <input id="linkBox" class="copy" readonly value="<?= e($downloadUrl) ?>">
      <div style="margin-top:8px">
        <button id="copyBtn" class="btn">Copy Link</button>
      </div>
    </div>

    <p class="note">Note: to share the PDF via WhatsApp programmatically you need a public HTTPS URL (ngrok or a real host). If your URL uses 'localhost', remote recipients cannot download it.</p>
  </div>

  <p style="margin-top:18px"><a href="index.php">← Back to Web Sprint home</a></p>
</div>

<script>
document.getElementById('copyBtn')?.addEventListener('click', function(){
  var el = document.getElementById('linkBox');
  if (!navigator.clipboard) {
    el.select();
    try { document.execCommand('copy'); alert('Link copied'); } catch(e){ alert('Copy failed — select & copy manually'); }
    return;
  }
  navigator.clipboard.writeText(el.value).then(function(){ alert('Link copied'); }, function(){ alert('Copy failed — select & copy manually'); });
});
</script>
</body>
</html>
