<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$pageTitle = 'About Us';
require __DIR__ . '/header.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 space-y-10">
  <div class="text-center">
    <span class="text-xs font-bold uppercase tracking-wider text-brand-700">Our Story</span>
    <h1 class="text-4xl font-extrabold text-brand-900 mt-2">Good food starts with good farms.</h1>
    <p class="text-gray-600 mt-4"><?= APP_NAME ?> connects verified local farmers directly with households, restaurants and shops, so growers earn fairly and families eat fresh, chemical-free food.</p>
  </div>
  <div class="grid md:grid-cols-3 gap-6">
    <?php foreach ([['fa-handshake','Fair to farmers','No middle-men: farmers set their own prices and keep the margin.'],['fa-seedling','Truly organic','Every partner farm is reviewed and verified before listing.'],['fa-truck-fast','Harvest to door','Orders are harvested and delivered in under 24 hours.']] as [$i,$t,$d]): ?>
      <div class="card p-6 text-center"><i class="fa-solid <?= $i ?> text-3xl text-brand-700 mb-3"></i><h3 class="font-bold text-gray-900"><?= $t ?></h3><p class="text-sm text-gray-600 mt-1"><?= $d ?></p></div>
    <?php endforeach; ?>
  </div>
  <div class="text-center"><a href="register.php" class="btn">Join as a buyer or farmer</a></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
