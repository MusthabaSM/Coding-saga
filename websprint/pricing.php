<?php
// pricing.php — Web Sprint pricing page (Razorpay, advance-only, flexible final price)
// Added: optional phone/WhatsApp input per package; business is submitted via hidden input

$businessMap = [
  'gym'        => 'Gym & Fitness Center',
  'salon'      => 'Beauty Salon',
  'retail'     => 'Retail Clothing Store',
  'department' => 'Department Store / Supermarket',
  'restaurant' => 'Restaurant / Cafe',
  'hotel'      => 'Hotel & Lodge',
  'event'      => 'Event Planner',
  'coaching'   => 'Coaching Centre',
  'fashion'    => 'Fashion Store / Boutique',
  'repair'     => 'Repair & Maintenance Services',
  'studio'     => 'Photography / Studio',
  'travel'     => 'Travel & Transport Agency',
];

// From portfolio (?type=gym etc.)
$type = $_GET['type'] ?? '';
$type = strtolower(trim($type));
$selectedBusiness = $businessMap[$type] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pricing — Web Sprint</title>
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
.notice{background:#eef2ff;border-left:4px solid var(--primary);padding:12px 14px;border-radius:8px;margin-bottom:18px;font-size:14px}
.pricing{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px}
.card{background:#fff;padding:18px;border-radius:14px;box-shadow:0 6px 20px rgba(0,0,0,0.06);text-align:center;display:flex;flex-direction:column}
.card h3{margin-bottom:6px}
.price{font-size:18px;font-weight:600;color:var(--primary);margin:6px 0}
.card p{font-size:14px;color:var(--muted)}
form{margin-top:8px;text-align:left}
label{font-size:13px;color:#4b5563;display:block;margin-top:6px}
input{width:100%;padding:10px;margin-top:4px;border-radius:6px;border:1px solid #ddd;font-size:13px}
select{width:100%;padding:10px;margin-top:4px;border-radius:6px;border:1px solid #ddd;font-size:13px}
button{width:100%;padding:10px;margin-top:10px;border-radius:8px;border:0;background:#111;color:#fff;cursor:pointer;font-size:14px}
footer{text-align:center;padding:20px 0;margin-top:30px;color:var(--muted)}
.adv{font-weight:600;color:#111;margin-bottom:4px}
.small-note{font-size:12px;color:#6b7280;margin-top:4px}
.other-business{margin-top:4px}
</style>
</head>
<body>

<header>
  <div class="wrap">
    <strong>Web Sprint</strong>
    <nav>
      <a href="index.php">Home</a>
      <a href="portfolio.php">Portfolio</a>
      <a href="pricing.php">Pricing</a>
      <a href="contact.php">Contact</a>
    </nav>
  </div>
</header>

<main class="wrap">
<h1>Website Pricing</h1>

<?php if($selectedBusiness): ?>
  <div class="notice">
    Selected business from portfolio:
    <strong><?=htmlspecialchars($selectedBusiness)?></strong>
  </div>
<?php else: ?>
  <div class="notice">
    Choose your package and business type below. Prices <strong>start from</strong> the amounts shown.
    Final cost depends on your requirements. You only pay a small advance online to get started.
  </div>
<?php endif; ?>

<div class="pricing">

  <!-- BASIC -->
  <div class="card">
    <h3>Basic</h3>
    <p>Single-page website for simple presence</p>
    <div class="price">Starts from ₹1,999</div>
    <p class="adv">Advance to start: ₹200</p>
    <p class="small-note">Final price depends on features, sections, and design complexity.</p>

    <form method="post" action="create_order.php" class="pkg-form">
      <input type="hidden" name="package" value="Basic">
      <input type="hidden" name="business" class="business-hidden" value="<?=htmlspecialchars($selectedBusiness)?>">

      <?php if(!$selectedBusiness): ?>
        <label>Business type</label>
        <select name="business_select" class="business-select" required>
          <option value="">Select business</option>
          <?php foreach ($businessMap as $label): ?>
            <option value="<?=htmlspecialchars($label)?>"><?=htmlspecialchars($label)?></option>
          <?php endforeach; ?>
          <option value="OTHER">Other</option>
        </select>

        <div class="other-business" style="display:none;">
          <label>Please specify your business</label>
          <input type="text" name="business_other" class="business-other-input" placeholder="Eg: Clinic, Consultancy, Tattoo Studio">
        </div>
      <?php endif; ?>

      <label>Your name</label>
      <input name="name" placeholder="Your name" required>

      <label>Email</label>
      <input name="email" type="email" placeholder="Email" required>

      <label>Phone / WhatsApp (optional)</label>
      <input name="phone" type="text" placeholder="Optional — phone or WhatsApp (e.g. +919876543210)">

      <button type="submit">Pay advance & start project</button>
    </form>
  </div>

  <!-- BUSINESS -->
  <div class="card">
    <h3>Business</h3>
    <p>5-page website for growing businesses</p>
    <div class="price">Starts from ₹5,999</div>
    <p class="adv">Advance to start: ₹500</p>
    <p class="small-note">Best for service businesses, salons, gyms, institutes, etc.</p>

    <form method="post" action="create_order.php" class="pkg-form">
      <input type="hidden" name="package" value="Business">
      <input type="hidden" name="business" class="business-hidden" value="<?=htmlspecialchars($selectedBusiness)?>">

      <?php if(!$selectedBusiness): ?>
        <label>Business type</label>
        <select name="business_select" class="business-select" required>
          <option value="">Select business</option>
          <?php foreach ($businessMap as $label): ?>
            <option value="<?=htmlspecialchars($label)?>"><?=htmlspecialchars($label)?></option>
          <?php endforeach; ?>
          <option value="OTHER">Other</option>
        </select>

        <div class="other-business" style="display:none;">
          <label>Please specify your business</label>
          <input type="text" name="business_other" class="business-other-input" placeholder="Eg: Clinic, Consultancy, Tattoo Studio">
        </div>
      <?php endif; ?>

      <label>Your name</label>
      <input name="name" placeholder="Your name" required>

      <label>Email</label>
      <input name="email" type="email" placeholder="Email" required>

      <label>Phone / WhatsApp (optional)</label>
      <input name="phone" type="text" placeholder="Optional — phone or WhatsApp (e.g. +919876543210)">

      <button type="submit">Pay advance & start project</button>
    </form>
  </div>

  <!-- PREMIUM -->
  <div class="card">
    <h3>Premium</h3>
    <p>Advanced website with more pages & features</p>
    <div class="price">Starts from ₹11,999</div>
    <p class="adv">Advance to start: ₹800</p>
    <p class="small-note">Ideal for high-end brands, hotels, multi-service businesses.</p>

    <form method="post" action="create_order.php" class="pkg-form">
      <input type="hidden" name="package" value="Premium">
      <input type="hidden" name="business" class="business-hidden" value="<?=htmlspecialchars($selectedBusiness)?>">

      <?php if(!$selectedBusiness): ?>
        <label>Business type</label>
        <select name="business_select" class="business-select" required>
          <option value="">Select business</option>
          <?php foreach ($businessMap as $label): ?>
            <option value="<?=htmlspecialchars($label)?>"><?=htmlspecialchars($label)?></option>
          <?php endforeach; ?>
          <option value="OTHER">Other</option>
        </select>

        <div class="other-business" style="display:none;">
          <label>Please specify your business</label>
          <input type="text" name="business_other" class="business-other-input" placeholder="Eg: Clinic, Consultancy, Tattoo Studio">
        </div>
      <?php endif; ?>

      <label>Your name</label>
      <input name="name" placeholder="Your name" required>

      <label>Email</label>
      <input name="email" type="email" placeholder="Email" required>

      <label>Phone / WhatsApp (optional)</label>
      <input name="phone" type="text" placeholder="Optional — phone or WhatsApp (e.g. +919876543210)">

      <button type="submit">Pay advance & start project</button>
    </form>
  </div>

</div>

<p style="margin-top:24px">
  <a href="portfolio.php">← Back to portfolio</a>
</p>
</main>

<footer>
  <p>© <?=date('Y')?> Web Sprint — Freelance Web Solutions</p>
</footer>

<script>
/*
 JS responsibilities:
 - If page has a preselected business (from portfolio), hidden input 'business' is already set.
 - If user chooses a business from the select, or enters 'Other', we populate hidden business input before submit.
*/
document.querySelectorAll('.pkg-form').forEach(function(form){
  var select = form.querySelector('.business-select');
  var otherBox = form.querySelector('.other-business');
  var otherInput = form.querySelector('.business-other-input');
  var hidden = form.querySelector('.business-hidden');

  // Show/hide other box when selection changes
  if (select) {
    select.addEventListener('change', function(){
      if (this.value === 'OTHER') {
        otherBox.style.display = 'block';
        if (otherInput) otherInput.required = true;
      } else {
        otherBox.style.display = 'none';
        if (otherInput) { otherInput.required = false; otherInput.value = ''; }
      }
    });
  }

  // On submit, set the hidden business value:
  form.addEventListener('submit', function(e){
    // If business already prefilled (from portfolio), keep it
    if (hidden && hidden.value && hidden.value.trim() !== '') {
      // nothing to do
    } else if (select) {
      var val = select.value || '';
      if (val === 'OTHER') {
        var custom = (otherInput && otherInput.value) ? otherInput.value.trim() : '';
        if (!custom) {
          alert('Please specify your business in the textbox.');
          if (otherInput) otherInput.focus();
          e.preventDefault();
          return false;
        }
        hidden.value = custom;
      } else {
        hidden.value = val;
      }
    } else {
      // no select present and hidden empty — do nothing
    }

    // phone is optional; no validation here (server can normalize)
    return true;
  });
});
</script>

</body>
</html>
