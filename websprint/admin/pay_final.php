<?php
require_once 'config.php';
$id = (int)$_GET['order_id'];

$order = $pdo->prepare("SELECT * FROM orders WHERE id=:i");
$order->execute([':i'=>$id]);
$o = $order->fetch();
if(!$o || !$o['final_amount']) exit;
?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
new Razorpay({
  key: "<?=RZP_KEY_ID?>",
  amount: "<?=$o['final_amount']*100?>",
  name: "WebSprint",
  description: "Final Payment",
  callback_url: "<?=BASE_URL?>/success_final.php?order_id=<?=$id?>"
}).open();
</script>
