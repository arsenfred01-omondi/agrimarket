<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_login();
$st = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$st->execute([$user['id']]);
$orders = $st->fetchAll();

$pageTitle = 'My Orders';
require __DIR__ . '/header.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-3xl font-extrabold text-brand-900 mb-8">My Orders</h1>
  <?php if (!$orders): ?>
    <div class="card py-16 text-center"><p class="text-gray-500">You haven't placed any orders yet.</p><a href="catalogue.php" class="btn mt-4">Start Shopping</a></div>
  <?php else: ?>
    <div class="card overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead><tr class="text-xs uppercase text-gray-400 border-b border-gray-100"><th class="p-4">Order</th><th class="p-4">Date</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
        <tbody class="divide-y divide-gray-100">
        <?php foreach ($orders as $o): ?>
          <tr><td class="p-4 font-semibold"><?= e($o['order_number']) ?></td>
              <td class="p-4 text-gray-500"><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
              <td class="p-4 font-bold text-brand-900"><?= money($o['total']) ?></td>
              <td class="p-4"><?= order_badge($o['status']) ?></td>
              <td class="p-4 text-right"><a href="receipt.php?id=<?= (int)$o['id'] ?>" class="text-brand-800 font-semibold hover:underline">View receipt</a></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
