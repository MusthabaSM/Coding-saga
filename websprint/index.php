<?php
if (file_exists(__DIR__ . '/config.php'))
  require_once __DIR__ . '/config.php';

function e($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function img_or_placeholder($relpath, $alt = '', $w = '100%', $h = 'auto')
{
  $full = __DIR__ . '/' . ltrim($relpath, '/');
  if (file_exists($full)) {
    return '<img src="' . e($relpath) . '" alt="' . e($alt) . '" style="width:' . $w . ';height:' . $h . ';object-fit:cover;border-radius:18px;">';
  }
  return '<div style="width:' . $w . ';height:' . $h . ';border-radius:18px;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#6C5CE7;font-weight:700">IMAGE</div>';
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>WebSprint — Websites for Local Businesses</title>

  <style>
    :root {
      --muted: #6b7280;
      --dark: #0f1724;
      --primary: #111827;
      --accent: #6C5CE7;
      --container: 1150px;
    }

    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      font-family: Inter, system-ui, Arial;
      background: url("uploads/bg5.jpg") no-repeat center center fixed;
      background-size: cover;
      color: var(--dark)
    }

    header {
      position: sticky;
      top: 0;
      background: rgba(255, 255, 255, .9);
      backdrop-filter: blur(6px);
      border-bottom: 1px solid #eee;
      z-index: 100
    }

    .hdr {
      max-width: var(--container);
      margin: auto;
      padding: 14px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center
    }

    .brand {
      font-size: 24px;
      font-weight: 900
    }

    .nav a {
      margin-left: 14px;
      font-weight: 600;
      color: var(--muted)
    }

    .nav a.cta {
      background: #111;
      color: #fff;
      padding: 8px 14px;
      border-radius: 12px
    }

    .wrap {
      max-width: var(--container);
      margin: auto;
      padding: 28px
    }

    .hero-grid {
      display: grid;
      grid-template-columns: 1fr 460px;
      gap: 32px;
      align-items: center
    }

    @media(max-width:900px) {
      .hero-grid {
        grid-template-columns: 1fr
      }
    }

    .title {
      font-size: 40px;
      font-weight: 900;
      line-height: 1.1;
      margin: 0
    }

    @media(max-width:600px) {
      .title {
        font-size: 38px
      }
    }

    .lead {
      color: var(--muted);
      font-size: 18px;
      margin-top: 10px;
      max-width: 620px
    }

    .cta-row {
      margin-top: 22px;
      display: flex;
      gap: 14px;
      flex-wrap: wrap
    }

    .btn {
      padding: 13px 22px;
      border-radius: 30px;
      font-weight: 700;
      text-decoration: none
    }

    .btn-primary {
      background: #111;
      color: #fff
    }

    .btn-ghost {
      border: 1px solid #ddd;
      color: #111;
      background: #fff
    }

    .features {
      display: flex;
      gap: 16px;
      margin-top: 30px
    }

    @media(max-width:900px) {
      .features {
        flex-direction: column
      }
    }

    .feature {
      background: #fff;
      padding: 20px;
      border-radius: 14px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, .06);
      text-align: center
    }

    .feature p {
      color: var(--muted);
      font-size: 14px;
      margin-top: 6px
    }

    .visual {
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0, 0, 0, .1)
    }

    .banner {
      margin-top: 40px;
      border-radius: 26px;
      overflow: hidden;
      display: flex;
      box-shadow: 0 24px 60px rgba(0, 0, 0, .08)
    }

    @media(max-width:900px) {
      .banner {
        flex-direction: column
      }
    }

    .banner-left {
      flex: 1;
      padding: 48px;
      background: #fff
    }

    .banner-left h2 {
      font-size: 44px;
      font-weight: 900;
      margin: 0 0 10px
    }

    .banner-left p {
      color: var(--muted);
      max-width: 540px
    }

    .banner-right {
      width: 460px;
      background: #f7f4ff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px
    }

    @media(max-width:900px) {
      .banner-right {
        width: 100%
      }
    }

    footer {
      margin-top: 60px;
      background: linear-gradient(180deg, #f8fbff, #fff)
    }

    .f-inner {
      max-width: var(--container);
      margin: auto;
      padding: 40px;
      display: flex;
      gap: 40px;
      justify-content: space-between;
      flex-wrap: wrap
    }

    .f-brand {
      font-size: 36px;
      font-weight: 900
    }

    .small {
      font-size: 14px;
      color: var(--muted)
    }
  </style>
</head>

<body>

  <header>
    <div class="hdr">
      <div class="brand">WebSprint</div>
      <nav class="nav">
        <a href="index.php">Home</a>
        <a href="portfolio.php">Portfolio</a>
        <a href="pricing.php">Pricing</a>
        <a href="request_project.php">Request</a>
        <a href="contact.php">Contact</a>
        <a class="cta" href="admin/dashboard.php">Admin</a>
      </nav>
    </div>
  </header>

  <main class="wrap">

    <section class="hero">
      <div class="hero-grid">

        <div>
          <h1 class="title">
            Websites that turn local visitors into paying customers
          </h1>

          <p class="lead">
            WebSprint builds fast, mobile-first websites for local businesses that want more calls,
            enquiries, and walk-ins — not just an online presence.
          </p>

          <div class="cta-row">
            <a class="btn btn-primary" href="portfolio.php">Choose Your Business</a>
            <a class="btn btn-ghost" href="pricing.php">Get Your Website</a>
          </div>

          <div class="features">
            <div class="feature">
              <strong>Transparent Process</strong>
              <p>Discuss requirements first. Pay a small advance only after clarity.</p>
            </div>
            <div class="feature">
              <strong>Built for Mobile Users</strong>
              <p>Your website looks perfect on phones, tablets, and desktops.</p>
            </div>
            <div class="feature">
              <strong>Pay After Approval</strong>
              <p>Final payment only after your website is completed and approved.</p>
            </div>
          </div>
        </div>

        <div class="visual">
          <?= img_or_placeholder('uploads/banner-right.jpg', 'Hero Image', '100%', '100%'); ?>
        </div>

      </div>
      <!-- HOW IT WORKS SECTION -->
      <section style="margin-top:70px">

        <h2 style="
    text-align:center;
    font-size:44px;
    font-weight:900;
    margin-bottom:14px;
  ">
          How WebSprint Works
        </h2>

        <p style="
    text-align:center;
    max-width:680px;
    margin:0 auto 40px;
    color:#000000;
    font-size:17px;
  ">
          A simple, transparent process designed for local businesses — no confusion,
          no upfront risk, no hidden charges.
        </p>

        <div style="
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
  ">

          <!-- STEP 1 -->
          <div style="
      background:#fff;
      padding:26px;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.06);
    ">
            <div style="font-size:36px;font-weight:900;color:#008080">01</div>
            <h3 style="margin:10px 0 6px">Request a project</h3>
            <p style="color:#6b7280;font-size:14px">
              Tell us about your business, goals, and what kind of website you need.
            </p>
          </div>

          <!-- STEP 2 -->
          <div style="
      background:#fff;
      padding:26px;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.06);
    ">
            <div style="font-size:36px;font-weight:900;color:#008080">02</div>
            <h3 style="margin:10px 0 6px">Discuss & finalize scope</h3>
            <p style="color:#6b7280;font-size:14px">
              We discuss features, pages, design, and finalize the pricing transparently.
            </p>
          </div>

          <!-- STEP 3 -->
          <div style="
      background:#fff;
      padding:26px;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.06);
    ">
            <div style="font-size:36px;font-weight:900;color:#008080">03</div>
            <h3 style="margin:10px 0 6px">Pay a small advance</h3>
            <p style="color:#6b7280;font-size:14px">
              Confirm your project by paying a small advance. This helps us begin work.
            </p>
          </div>

          <!-- STEP 4 -->
          <div style="
      background:#fff;
      padding:26px;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.06);
    ">
            <div style="font-size:36px;font-weight:900;color:#008080">04</div>
            <h3 style="margin:10px 0 6px">We build your website</h3>
            <p style="color:#6b7280;font-size:14px">
              Your website is designed, developed, and tested for mobile & desktop.
            </p>
          </div>

          <!-- STEP 5 -->
          <div style="
      background:#fff;
      padding:26px;
      border-radius:18px;
      box-shadow:0 12px 30px rgba(0,0,0,0.06);
    ">
            <div style="font-size:36px;font-weight:900;color:#008080">05</div>
            <h3 style="margin:10px 0 6px">Approve & pay final amount</h3>
            <p style="color:#6b7280;font-size:14px">
              Pay the remaining amount only after reviewing and approving the website.
            </p>
          </div>

        </div>

        <!-- TRUST LINE -->
        <p style="
    text-align:center;
    margin-top:30px;
    color:#000000;
    font-weight:600;
  ">
          ✔ No hidden charges &nbsp; • &nbsp; ✔ Transparent pricing &nbsp; • &nbsp; ✔ Pay after approval
        </p>

      </section>

      <div class="banner">
        <div class="banner-left">
          <h2>From local business to local brand</h2>
          <p>
            Tell us about your business and goals. We’ll suggest the right website,
            finalize pricing transparently, and build something that actually works
            for your customers.
          </p>
          <div style="margin-top:20px">
            <a class="btn btn-primary" href="request_project.php">Request a Website</a>
          </div>
        </div>

        <div class="banner-right">
          <?= img_or_placeholder('uploads/bg6.jpg', 'Banner Visual', '115%', '115%'); ?>
        </div>
      </div>

    </section>

  </main>

  <footer>
    <div class="f-inner">
      <div>
        <div class="f-brand">WebSprint</div>
        <div class="small">Professional websites built for local growth.</div>
      </div>

      <div>
        <strong>Contact</strong>
        <div class="small" style="margin-top:6px">
          smmusthaba20@gmail.com<br>
          +91 90877 05080
        </div>
      </div>

      <div>
        <strong>Location</strong>
        <div class="small" style="margin-top:6px">
          Salem, Tamil Nadu, India
        </div>
      </div>
    </div>
  </footer>

</body>

</html>