<?php
// create_order.php — save order (including optional phone) then create Razorpay order (INR advance only)
// Place this file in your project root. Requires config.php which must define:
// - $pdo (PDO), RZP_KEY_ID, RZP_KEY_SECRET, BASE_URL
// NOTE: Ensure your `orders` table has a `phone` VARCHAR column (ALTER TABLE orders ADD COLUMN phone VARCHAR(32) DEFAULT NULL;)

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pricing.php');
    exit;
}

// --- Helper sanitizers ---
function clean($v){ return trim((string)$v); }
function valid_email($e){ return filter_var($e, FILTER_VALIDATE_EMAIL) !== false; }

// --- Read inputs ---
$name    = clean($_POST['name'] ?? '');
$email   = clean($_POST['email'] ?? '');
$package = clean($_POST['package'] ?? '');

// Resolve business:
// Priority: hidden 'business' (from portfolio) -> business_select / business_other -> fallback 'General'
if (!empty($_POST['business'])) {
    $business = clean($_POST['business']);
} else {
    $selected = clean($_POST['business_select'] ?? '');
    if ($selected === 'OTHER') {
        $custom = clean($_POST['business_other'] ?? '');
        if ($custom === '') {
            die('Please specify your business type.');
        }
        $business = $custom;
    } else {
        $business = $selected ?: 'General';
    }
}

// optional phone (may include + or spaces). store raw; you can normalize before using with wa.me
$phone = clean($_POST['phone'] ?? '');
if ($phone === '') $phone = null;

// basic validation
if ($name === '' || $email === '' || $package === '') {
    die('Missing required fields (name, email, package).');
}
if (!valid_email($email)) {
    die('Invalid email address.');
}

// Map package -> starting price (informational) + advance amount
$packageLower = strtolower($package);
switch ($packageLower) {
    case 'basic':
        $startingFrom   = 1999;
        $advanceAmount  = 200;
        break;
    case 'business':
        $startingFrom   = 5999;
        $advanceAmount  = 500;
        break;
    case 'premium':
        $startingFrom   = 11999;
        $advanceAmount  = 800;
        break;
    default:
        die('Unknown package selected.');
}

$amount   = (int)$advanceAmount; // advance to charge (INR)
$currency = 'INR';
$gateway  = 'razorpay';

try {
    // Insert order record (status pending). We store phone (nullable).
    $stmt = $pdo->prepare("
        INSERT INTO orders (name, email, phone, business, package, amount, currency, gateway, status, created_at)
        VALUES (:name, :email, :phone, :business, :package, :amount, :currency, :gateway, 'pending', NOW())
    ");
    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':phone'    => $phone,
        ':business' => $business ?: 'General',
        ':package'  => $package,
        ':amount'   => $amount,
        ':currency' => $currency,
        ':gateway'  => $gateway,
    ]);
    $orderId = $pdo->lastInsertId();
} catch (Exception $e) {
    // avoid leaking internal info on production; show message useful for debugging locally
    die("Database error while creating order: " . htmlspecialchars($e->getMessage()));
}

// --- Create Razorpay order via API (cURL) ---
function createRazorpayOrder($localOrderId, $amount, $currency, $name, $email, $phone, $business) {
    if (!defined('RZP_KEY_ID') || !defined('RZP_KEY_SECRET')) return null;
    $url = "https://api.razorpay.com/v1/orders";

    $payload = [
        'amount'   => intval($amount * 100), // paise
        'currency' => $currency,
        'receipt'  => 'WS_' . $localOrderId,
        'notes'    => [
            'local_order_id'  => (string)$localOrderId,
            'customer_name'   => $name,
            'customer_email'  => $email,
            'customer_phone'  => $phone ?? '',
            'business'        => $business ?? '',
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_USERPWD        => RZP_KEY_ID . ':' . RZP_KEY_SECRET,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $res  = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($res === false || ($http !== 200 && $http !== 201)) {
        // log or return error string in debug use; return null for failure
        error_log("Razorpay order create failed HTTP={$http} ERR={$err} RES={$res}");
        return null;
    }

    $data = json_decode($res, true);
    return $data['id'] ?? null;
}

$rzpOrderId = createRazorpayOrder($orderId, $amount, $currency, $name, $email, $phone, $business);
if (!$rzpOrderId) {
    // Optionally mark the order as failed or delete; here we keep the DB row for manual recovery
    die("Failed to create Razorpay order — check API keys and network (see server logs).");
}

// Save razorpay_order_id in local DB
try {
    $stmt = $pdo->prepare("UPDATE orders SET razorpay_order_id = :rid WHERE id = :id");
    $stmt->execute([':rid' => $rzpOrderId, ':id' => $orderId]);
} catch (Exception $e) {
    error_log("Failed to save razorpay_order_id: " . $e->getMessage());
}

// --- Render Razorpay checkout page ---
// Prefill contact only if phone present and looks reasonable (digits and +)
$prefillContact = '';
if (!empty($phone)) {
    // normalize simple: remove spaces; keep + if present
    $p = preg_replace('/\s+/', '', $phone);
    $prefillContact = $p;
}

function renderRazorpayCheckoutPage($rzpOrderId, $amount, $currency, $name, $email, $localOrderId, $prefillContact = '') {
    ?>
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Pay advance — Web Sprint</title>
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;background:#f7f7fb} .card{max-width:720px;margin:20px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.06)}</style>
    </head>
    <body>
      <div class="card">
        <h2>Pay advance to confirm your project</h2>
        <p><strong>Advance amount:</strong> ₹<?=htmlspecialchars($amount)?> &nbsp; • &nbsp; <strong>Package:</strong> <?=htmlspecialchars($localOrderId)?></p>
        <p>Final project price will be discussed and finalized based on your requirements. The advance will be adjusted in your final invoice.</p>

        <button id="rzp-button" style="padding:12px 18px;border-radius:6px;border:0;background:#6C5CE7;color:#fff;font-weight:700;cursor:pointer">Pay with Razorpay</button>

        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
          var options = {
              "key": "<?= htmlspecialchars(RZP_KEY_ID) ?>",
              "amount": "<?= intval($amount*100) ?>",
              "currency": "<?= htmlspecialchars($currency) ?>",
              "name": "Web Sprint",
              "description": "Website order #<?= intval($localOrderId) ?> (advance)",
              "order_id": "<?= htmlspecialchars($rzpOrderId) ?>",
              "prefill": {
                  "name": "<?= htmlspecialchars($name) ?>",
                  "email": "<?= htmlspecialchars($email) ?>",
                  <?= $prefillContact ? '"contact": "'.htmlspecialchars($prefillContact).'",' : '' ?>
              },
              "notes": {
                  "local_order_id": "<?= intval($localOrderId) ?>",
                  "business": "<?= htmlspecialchars($_POST['business'] ?? $_POST['business_select'] ?? ($_POST['business_other'] ?? '')) ?>"
              },
              "callback_url": "<?= rtrim(BASE_URL, '/') ?>/success.php?gateway=razorpay&local_order_id=<?= intval($localOrderId) ?>",
              "theme": {"color": "#6C5CE7"}
          };
          var rzp1 = new Razorpay(options);
          document.getElementById('rzp-button').onclick = function(e){
              rzp1.open();
              e.preventDefault();
          }
        </script>
      </div>
    </body>
    </html>
    <?php
    exit;
}

renderRazorpayCheckoutPage($rzpOrderId, $amount, $currency, $name, $email, $orderId, $prefillContact);
