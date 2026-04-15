<?php
require_once __DIR__ . '/config.php';

$orderId = intval($_GET['order_id'] ?? 0);
if (!$orderId) die("Invalid order id.");

// Load order
$stmt = $pdo->prepare("SELECT o.*, r.requirements AS request_requirements FROM orders o LEFT JOIN requests r ON r.id = o.request_id WHERE o.id = :id");
$stmt->execute([':id'=>$orderId]);
$order = $stmt->fetch();
if (!$order) die("Order not found.");

// If razorpay_order_id exists and status pending, we can reuse or create a new razorpay order. For simplicity, always create a new Razorpay order and update razorpay_order_id.
function createRazorpayOrderForPay($orderId, $amount, $currency, $name, $email) {
    $url  = "https://api.razorpay.com/v1/orders";
    $payload = [
        'amount'   => $amount * 100,
        'currency' => $currency,
        'receipt'  => 'WS_REQ_' . $orderId,
        'notes'    => [
            'local_order_id' => $orderId,
            'customer_name'  => $name,
            'customer_email' => $email
        ]
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_USERPWD        => RZP_KEY_ID . ':' . RZP_KEY_SECRET,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        $err = curl_error($ch);
        curl_close($ch);
        die("cURL error: " . htmlspecialchars($err));
    }
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http !== 200 && $http !==201) {
        die("Razorpay API error (HTTP {$http}): " . htmlspecialchars($res));
    }
    $data = json_decode($res, true);
    return $data['id'] ?? null;
}

// Create Razorpay order id
$rzpOrderId = createRazorpayOrderForPay($orderId, $order['amount'], $order['currency'], $order['name'], $order['email']);
if (!$rzpOrderId) die("Failed to create Razorpay order.");

// Save razorpay_order_id
$stmt = $pdo->prepare("UPDATE orders SET razorpay_order_id = :rid WHERE id = :id");
$stmt->execute([':rid'=>$rzpOrderId, ':id'=>$orderId]);

// Render payment page which opens Razorpay checkout
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Pay Advance — Web Sprint</title><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="font-family:Arial,sans-serif;padding:18px;background:#f7f7fb">
<div style="max-width:720px;margin:auto;background:#fff;padding:16px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.06)">
  <h2>Pay Advance</h2>
  <p><strong>Order #<?=htmlspecialchars($orderId)?></strong></p>
  <p><strong>Customer:</strong> <?=htmlspecialchars($order['name'])?> (<?=htmlspecialchars($order['email'])?>)</p>
  <p><strong>Business:</strong> <?=htmlspecialchars($order['business'])?></p>
  <p><strong>Package:</strong> <?=htmlspecialchars($order['package'])?></p>
  <p><strong>Advance to pay:</strong> ₹<?=htmlspecialchars($order['amount'])?></p>
  <?php if (!empty($order['request_requirements'])): ?>
    <p><strong>Requirements:</strong><br><?=nl2br(htmlspecialchars($order['request_requirements']))?></p>
  <?php endif; ?>

  <button id="rzp-button" style="padding:10px 14px;background:#6C5CE7;color:#fff;border:0;border-radius:8px;cursor:pointer">Pay with Razorpay</button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
  "key": "<?=RZP_KEY_ID?>",
  "amount": "<?=intval($order['amount'] * 100)?>",
  "currency": "<?=htmlspecialchars($order['currency'])?>",
  "name": "Web Sprint",
  "description": "Advance for order #<?=intval($orderId)?>",
  "order_id": "<?=htmlspecialchars($rzpOrderId)?>",
  "prefill": {"name":"<?=htmlspecialchars($order['name'])?>","email":"<?=htmlspecialchars($order['email'])?>"},
  "callback_url": "<?=BASE_URL?>/success.php?gateway=razorpay&local_order_id=<?=intval($orderId)?>",
  "notes": {"order_id": <?=intval($orderId)?>},
  "theme": {"color":"#6C5CE7"}
};
var rzp1 = new Razorpay(options);
document.getElementById('rzp-button').onclick = function(e){
  rzp1.open();
  e.preventDefault();
}
</script>
</body>
</html>
