<?php
// request_project.php
// Public: "Start Your Project" form. Prefills package & business from GET and saves to requests table.

require_once __DIR__ . '/config.php';

// business options
$businessMap = [
  'gym'        => 'Gym & Fitness Center',
  'salon'      => 'Beauty Salon',
  'retail'     => 'Retail Clothing Store',
  'department' => 'Department Store / Supermarket',
  'restaurant' => 'Restaurant / Café',
  'hotel'      => 'Hotel & Lodge',
  'event'      => 'Event Planner',
  'coaching'   => 'Coaching Centre',
  'fashion'    => 'Fashion Store / Boutique',
  'repair'     => 'Repair & Maintenance Services',
  'studio'     => 'Photography / Studio',
  'travel'     => 'Travel & Transport Agency',
];

// Prefill values from GET (pricing -> request flow)
$pref_package = trim($_GET['package'] ?? '');
$pref_business = trim($_GET['business'] ?? '');

// Normalize if business matches a known label (in case ?type used)
if ($pref_business && in_array($pref_business, $businessMap, true)) {
    // keep as-is
}

// Form processing
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $package = trim($_POST['package'] ?? '');
    $business_select = trim($_POST['business_select'] ?? '');
    $business_other = trim($_POST['business_other'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $preferred_contact = trim($_POST['preferred_contact'] ?? '');

    // Resolve business: if business_select provided use it; else if business_other use it; else fallback to pref
    $business = '';
    if (!empty($business_select) && $business_select !== 'OTHER') {
        $business = $business_select;
    } elseif (!empty($business_other)) {
        $business = $business_other;
    } elseif (!empty($pref_business)) {
        $business = $pref_business;
    } else {
        $business = 'General';
    }

    // Basic validation
    if (!$name || !$email || !$package) {
        $error = 'Please complete required fields: Name, Email & Package.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO requests (name,email,phone,business,package,requirements) VALUES (:name,:email,:phone,:business,:package,:requirements)");
            $stmt->execute([
                ':name'=>$name,
                ':email'=>$email,
                ':phone'=>$phone,
                ':business'=>$business,
                ':package'=>$package,
                ':requirements'=>$requirements
            ]);
            $reqId = $pdo->lastInsertId();
            $success = "Request received. Your request ID is #{$reqId}. We will contact you shortly to discuss details.";
            // (optional) send admin notification here
            // Clear POST values to avoid resubmission display
            $pref_package = $pref_business = '';
        } catch (Exception $e) {
            $error = "Could not save request: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Helper: build options for select with preselected value
function buildBusinessOptions($businessMap, $selected = '') {
    $out = '<option value="">Select (optional)</option>';
    foreach ($businessMap as $label) {
        $sel = ($label === $selected) ? ' selected' : '';
        $out .= '<option value="'.htmlspecialchars($label).'"'.$sel.'>'.htmlspecialchars($label).'</option>';
    }
    $out .= '<option value="OTHER">Other (specify)</option>';
    return $out;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Start Your Project — Web Sprint</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7fb;color:#222;margin:0;padding:20px}
.container{max-width:820px;margin:18px auto;background:#fff;padding:18px;border-radius:10px;box-shadow:0 12px 30px rgba(0,0,0,0.06)}
h1{margin-top:0;font-size:22px}
.note{color:#6b7280;font-size:14px;margin-bottom:12px}
label{display:block;margin-top:10px;font-weight:600;font-size:13px}
input,select,textarea{width:100%;padding:10px;border-radius:8px;border:1px solid #e6e6e6;margin-top:6px;font-size:14px}
textarea{min-height:120px;resize:vertical}
button{margin-top:12px;padding:12px 14px;border-radius:8px;border:0;background:#111;color:#fff;font-weight:700;cursor:pointer}
.success{background:#ecfdf5;padding:10px;border-left:4px solid #10b981;margin-bottom:10px}
.error{background:#fff1f2;padding:10px;border-left:4px solid #ef4444;margin-bottom:10px}
.small{color:#6b7280;font-size:13px;margin-top:8px}
.inline{display:flex;gap:10px}
.inline > *{flex:1}
</style>
</head>
<body>
<div class="container">
  <h1>Start Your Project</h1>
  <p class="note">Tell us about your website. We'll contact you to discuss requirements and provide a final quote. After you agree, we'll send a secure payment link for the advance to confirm the order.</p>

  <?php if ($success): ?>
    <div class="success"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="error"><?=htmlspecialchars($error)?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <label>Your name *</label>
    <input name="name" value="<?=htmlspecialchars($_POST['name'] ?? '')?>" required>

    <label>Email *</label>
    <input name="email" type="email" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" required>

    <label>Phone</label>
    <input name="phone" value="<?=htmlspecialchars($_POST['phone'] ?? '')?>">

    <label>Which package are you interested in? *</label>
    <select name="package" required>
      <option value="">Select package</option>
      <option value="Basic" <?=($pref_package==='Basic' || ($_POST['package'] ?? '')==='Basic') ? 'selected' : ''?>>Basic — starts from ₹1,999</option>
      <option value="Business" <?=($pref_package==='Business' || ($_POST['package'] ?? '')==='Business') ? 'selected' : ''?>>Business — starts from ₹5,999</option>
      <option value="Premium" <?=($pref_package==='Premium' || ($_POST['package'] ?? '')==='Premium') ? 'selected' : ''?>>Premium — starts from ₹11,999</option>
    </select>

    <label>What kind of business is this? (optional)</label>
    <select id="business_select" name="business_select">
      <?= buildBusinessOptions($businessMap, $_POST['business_select'] ?? $pref_business) ?>
    </select>

    <div id="other_wrap" style="display:none;margin-top:6px;">
      <label>If Other, please specify</label>
      <input name="business_other" id="business_other" value="<?=htmlspecialchars($_POST['business_other'] ?? '')?>" placeholder="Eg: Hardware store, Bakery, Consultancy">
    </div>

    <label>Brief requirements / what do you need on the website?</label>
    <textarea name="requirements" placeholder="Pages, features, integrations, example websites..."><?=htmlspecialchars($_POST['requirements'] ?? '')?></textarea>

    <label>Preferred contact method / time (optional)</label>
    <input name="preferred_contact" placeholder="Eg: WhatsApp - 9am-6pm / Email anytime" value="<?=htmlspecialchars($_POST['preferred_contact'] ?? '')?>">

    <p class="small">After we review your request we will contact you to confirm scope & price. Then we will send a secure advance payment link to confirm the order.</p>

    <button type="submit">Submit Request</button>
  </form>
</div>

<script>
// show other input when Other selected
(function(){
  var sel = document.getElementById('business_select');
  var wrap = document.getElementById('other_wrap');
  var other = document.getElementById('business_other');

  function toggle() {
    if (sel.value === 'OTHER') {
      wrap.style.display = 'block';
      other.required = true;
    } else {
      wrap.style.display = 'none';
      other.required = false;
      other.value = '';
    }
  }
  sel.addEventListener('change', toggle);
  // initial state (if prefilled via GET param)
  if (sel.value === 'OTHER' || sel.value === '') {
    // if pref_business was passed as a string and not in select options,
    // leave Other hidden — user can type in textbox
  }
  toggle();
})();
</script>
</body>
</html>
