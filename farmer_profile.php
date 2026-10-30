<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$st = db()->prepare('SELECT * FROM farmers WHERE id = ? AND is_verified = 1');
$st->execute([(int)($_GET['id'] ?? 0)]);
$farmer = $st->fetch();
if (!$farmer) {
    http_response_code(404);
    exit('Farmer not found.');
}
$products  = search_products(['farmer_id' => $farmer['id']]);
$pageTitle = $farmer['farm_name'];
require __DIR__ . '/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="card p-8 flex flex-col sm:flex-row items-center gap-6">
    <div class="w-24 h-24 bg-brand-100 rounded-3xl flex items-center justify-center text-5xl"><?= e($farmer['avatar']) ?></div>
    <div>
      <h1 class="text-3xl font-extrabold text-brand-900"><?= e($farmer['farm_name']) ?>
        <i class="fa-solid fa-circle-check text-brand-500 text-lg" title="Verified farm"></i></h1>
      <p class="text-sm text-gray-500 mt-1"><i class="fa-solid fa-location-dot text-brand-700 mr-1"></i><?= e($farmer['location']) ?> · <?= e($farmer['speciality']) ?></p>
      <p class="mt-3 text-sm text-gray-600 max-w-2xl"><?= e($farmer['bio'] ?: 'A proud AgriMarket partner farm growing chemical-free produce.') ?></p>
    </div>
  </div>
  <h2 class="text-xl font-bold text-gray-900 mt-12 mb-6">Fresh from <?= e($farmer['farm_name']) ?></h2>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($products as $p) echo product_card($p); ?>
    <?php if (!$products): ?><p class="text-gray-500">No products listed right now.</p><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
