<?php
// view_template.php - shows a demo template for a given business type

// Allowed template types and their file names
$templates = [
    'gym'               => 'gym.php',
    'salon'             => 'salon.php',
    'retail'            => 'retail.php',
    'department'        => 'department_store.php',
    'restaurant'        => 'restaurant_cafe.php',
    'hotel'             => 'hotel.php',
    'event'             => 'event_planner.php',
    'coaching'          => 'coaching_centre.php',
    'fashion'           => 'fashion_store.php',
    'repair'            => 'repair_services.php',
    'studio'            => 'studio.php',
    'travel'            => 'travel_agency.php',
];

$type = $_GET['type'] ?? '';
$type = strtolower(trim($type));

if (!isset($templates[$type])) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Template not found</title></head>
    <body>
      <h1>Template not found</h1>
      <p>This business demo is not available yet.</p>
      <p><a href="portfolio.php">Back to portfolio</a></p>
    </body></html>
    <?php
    exit;
}

$file = __DIR__ .'/templates/'. $templates[$type];

if (!file_exists($file)) {
    http_response_code(404);
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Template missing</title></head>
    <body>
      <h1>Template file missing</h1>
      <p>The demo file for this business is not created yet.</p>
      <p><a href="portfolio.php">Back to portfolio</a></p>
    </body></html>
    <?php
    exit;
}

// Include the template file
include $file;
