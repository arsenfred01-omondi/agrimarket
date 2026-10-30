<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_login();
$st = db()->prepare('SELECT * FROM orders WHERE id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$order = $st->fetch();
if (!$order || ($order['user_id'] !== $user['id'] && $user['role'] !== 'admin')) {
    http_response_code(404);
    exit('Receipt not found.');
}
$st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$st->execute([$order['id']]);
$lines = $st->fetchAll();
$methods = ['mpesa' => 'M-Pesa', 'card' => 'Card', 'cod' => 'Cash on Delivery'];

$pageTitle = 'Receipt ' . $order['order_number'];
require __DIR__ . '/header.php';
?>
<div class="max-w-2xl mx-auto px-4 py-10">
  <div class="card p-8 space-y-6">
    <div class="text-center">
      <div class="w-16 h-16 mx-auto rounded-full bg-brand-100 text-brand-800 flex items-center justify-center text-3xl mb-3"><i class="fa-solid fa-circle-check"></i></div>
      <h1 class="text-2xl font-extrabold text-brand-900">Thank you for your order!</h1>
      <p class="text-sm text-gray-500 mt-1">Order <strong><?= e($order['order_number']) ?></strong> · <?= e(date('d M Y, H:i', strtotime($order['created_at']))) ?></p>
      <div class="mt-3"><?= order_badge($order['status']) ?></div>
    </div>

    <table class="w-full text-sm">
      <thead><tr class="text-xs uppercase text-gray-400 border-b border-gray-100"><th class="text-left pb-2">Item</th><th class="pb-2">Qty</th><th class="text-right pb-2">Amount</th></tr></thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach ($lines as $l): ?>
          <tr><td class="py-2"><?= e($l['product_name']) ?> <span class="text-gray-400">@ <?= money($l['unit_price']) ?></span></td>
              <td class="text-center"><?= (int)$l['quantity'] ?></td>
              <td class="text-right font-semibold"><?= money($l['unit_price'] * $l['quantity']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="space-y-1 text-sm border-t border-gray-100 pt-4">
      <div class="flex justify-between"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
      <div class="flex justify-between"><span>Delivery</span><span><?= money($order['shipping']) ?></span></div>
      <?php if ((float)$order['discount'] > 0): ?><div class="flex justify-between text-emerald-700"><span>Discount (<?= e($order['promo_code']) ?>)</span><span>- <?= money($order['discount']) ?></span></div><?php endif; ?>
      <div class="flex justify-between text-lg font-extrabold text-brand-900 pt-2"><span>Total</span><span><?= money($order['total']) ?></span></div>
    </div>

    <div class="grid grid-cols-2 gap-4 text-xs text-gray-600 bg-gray-50 rounded-xl p-4">
      <div><p class="font-bold uppercase text-gray-400">Deliver to</p><p><?= e($order['address']) ?>, <?= e($order['city']) ?></p><p><?= e($order['phone']) ?></p></div>
      <div><p class="font-bold uppercase text-gray-400">Payment</p><p><?= e($methods[$order['payment_method']] ?? '') ?> (<?= e($order['payment_status']) ?>)</p></div>
    </div>

    <div class="flex gap-3 justify-center">
      <button onclick="window.print()" class="btn !bg-gray-700">Print</button>
      <a href="orders.php" class="btn">My Orders</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
