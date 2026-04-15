<?php
// download_receipt.php
require_once __DIR__ . '/config.php';

// Required: ?order_id=15
$orderId = intval($_GET['order_id'] ?? 0);
if ($orderId <= 0) {
    http_response_code(400);
    die('Invalid order id.');
}

// Sanitize filename and path
$invoiceFilename = "invoice_{$orderId}.pdf";
$invoicePath = rtrim(INVOICE_DIR, '/\\') . DIRECTORY_SEPARATOR . $invoiceFilename;

// Ensure file exists
if (!file_exists($invoicePath)) {
    http_response_code(404);
    die('Invoice not found.');
}

// Send headers and stream file
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($invoiceFilename) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($invoicePath));
flush();
readfile($invoicePath);
exit;
