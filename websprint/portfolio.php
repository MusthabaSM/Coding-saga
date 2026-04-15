<?php
// portfolio.php — MilesBuddy Business Portfolio with "Place order" flow
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Portfolio — WebSprint</title>

<style>
:root{--primary:#6C5CE7;--dark:#222;--muted:#6b7280;--bg:#f7f7fb;--card:#fff}
*{margin:0;padding:0;box-sizing:border-box;font-family:Inter,Arial,sans-serif}
body{background:var(--bg);color:var(--dark)}
a{text-decoration:none;color:var(--primary)}
.wrap{max-width:1100px;margin:auto;padding:25px}

/* HEADER */
header{background:#fff;border-bottom:1px solid #eee}
header .wrap{display:flex;justify-content:space-between;align-items:center}
header nav a{margin-left:16px;color:#777;font-weight:600}

/* PAGE */
h1{margin:18px 0}
.subtitle{color:var(--muted);margin-bottom:22px}

/* GRID */
.grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:20px;
}

/* CARD */
.card{
  background:#fff;
  border-radius:14px;
  box-shadow:0 6px 20px rgba(0,0,0,0.06);
  overflow:hidden;
  display:flex;
  flex-direction:column;
}
.card img{
  width:100%;
  height:180px;
  object-fit:cover;
  background:#eee;
}
.card .content{
  padding:14px;
  display:flex;
  flex-direction:column;
  height:100%;
}
.card h3{
  font-size:16px;
  margin-bottom:6px;
}
.card p{
  font-size:13px;
  color:var(--muted);
  margin-bottom:10px;
}
.card button{
  align-self:flex-start;
  padding:8px 14px;
  border-radius:6px;
  border:0;
  background:var(--primary);
  color:#fff;
  font-size:13px;
  cursor:pointer;
}

/* FOOTER */
footer{
  text-align:center;
  padding:20px 0;
  margin-top:30px;
  color:var(--muted);
}

@media(max-width:900px){
  .card img{height:160px}
}
</style>
</head>
<body>

<header>
  <div class="wrap">
    <strong>WebSprint</strong>
    <nav>
      <a href="index.php">Home</a>
      <a href="portfolio.php">Portfolio</a>
      <a href="pricing.php">Pricing</a>
      <a href="contact.php">Contact</a>
    </nav>
  </div>
</header>

<main class="wrap">
<h1>Business Portfolio</h1>
<p class="subtitle">
We design websites from scratch based on your business requirements.
Choose a business type below and place an order to see suitable packages.
</p>

<div class="grid">
  <?php
  // title, description, image filename, slug
  $items = [
    ["Gym & Fitness Center","Membership plans, trainers, transformations, offers.","gym.jpg","gym"],
    ["Beauty Salon","Services, pricing, appointment enquiry.","salon.jpg","salon"],
    ["Retail Clothing Store","Collections, offers, WhatsApp ordering.","retail.jpg","retail"],
    ["Department Store / Supermarket","Categories, promotions, location map.","department.jpg","department"],
    ["Restaurant / Cafe","Menu, table booking, Google reviews.","restaurant.jpg","restaurant"],
    ["Hotel & Lodge","Room gallery, pricing, booking enquiry.","hotel.jpg","hotel"],
    ["Event Planners","Wedding & corporate event portfolios.","event.jpg","event"],
    ["Coaching Centre","Courses, batches, admission enquiry.","coaching.jpg","coaching"],
    ["Fashion Store / Boutique","Lookbook, collections, Instagram links.","fashion.jpg","fashion"],
    ["Repair & Maintenance Services","AC, appliance, on-call repair services.","repair.jpg","repair"],
    ["Photography / Studio","Gallery, packages, booking requests.","studio.jpg","studio"],
    ["Travel & Transport Agency","Tour packages, cab & bus booking.","travel.jpg","travel"],
  ];

  foreach ($items as $item):
    [$title,$desc,$img,$slug] = $item;
  ?>
    <div class="card">
      <!-- You will upload these images later to assets/portfolio/ -->
      <img src="assets/portfolio/<?=htmlspecialchars($img)?>" alt="<?=htmlspecialchars($title)?> demo">
      <div class="content">
        <h3><?=htmlspecialchars($title)?></h3>
        <p><?=htmlspecialchars($desc)?></p>
        <button type="button"
                class="order-btn"
                data-type="<?=htmlspecialchars($slug)?>"
                data-title="<?=htmlspecialchars($title)?>">
          Place order →
        </button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<p style="margin-top:24px">
  Or go directly to <a href="pricing.php">pricing & packages →</a>
</p>

</main>

<footer>
  <p>© <?=date('Y')?> WebSprint — Freelance Web Solutions</p>
</footer>

<script>
// When user clicks "Place order", ask for confirmation then go to pricing.php
document.addEventListener('DOMContentLoaded', function(){
  var buttons = document.querySelectorAll('.order-btn');
  buttons.forEach(function(btn){
    btn.addEventListener('click', function(){
      var title = this.dataset.title || 'this business website';
      var type  = this.dataset.type || '';
      var ok = confirm('Place order for ' + title + ' website?');
      if(ok){
        // Redirect to pricing page; type can be used there if needed
        window.location.href = 'pricing.php?type=' + encodeURIComponent(type);
      }
    });
  });
});
</script>

</body>
</html>
