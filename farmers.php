<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$farmers = db()->query(
    'SELECT fr.*, COUNT(p.id) AS items FROM farmers fr
  LEFT JOIN products p ON p.farmer_id = fr.id AND p.is_active = 1
      WHERE fr.is_verified = 1 GROUP BY fr.id ORDER BY fr.farm_name'
)->fetchAll();

$pageTitle = 'Our Farmers';
require __DIR__ . '/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-3xl font-extrabold text-brand-900">Meet Our Local Partner Farmers</h1>
  <p class="text-sm text-gray-500 mt-1 mb-8">Supporting sustainable agricultural communities across the region.</p>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($farmers as $f): ?>
      <a href="farmer_profile.php?id=<?= (int)$f['id'] ?>" class="card p-6 flex items-center space-x-4 hover:shadow-md transition">
        <div class="w-14 h-14 bg-brand-100 rounded-2xl flex items-center justify-center text-3xl"><?= e($f['avatar']) ?></div>
        <div>
          <h2 class="font-bold text-gray-900"><?= e($f['farm_name']) ?></h2>
          <p class="text-xs text-gray-500"><i class="fa-solid fa-location-dot text-brand-700 mr-1"></i><?= e($f['location']) ?></p>
          <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700"><?= (int)$f['items'] ?> products</span>
        </div>
      </a>
    <?php endforeach; ?>
    <?php if (!$farmers): ?><p class="text-gray-500">No verified farmers yet.</p><?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
