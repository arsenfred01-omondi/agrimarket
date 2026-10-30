<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$pageTitle  = 'Farm Fresh Organic Produce';
$categories = get_categories();
$featured   = search_products(['sort' => 'rating'], 4);
$farmers    = db()->query('SELECT * FROM farmers WHERE is_verified = 1 ORDER BY id LIMIT 3')->fetchAll();
$stats      = [
    'farms'    => (int)db()->query('SELECT COUNT(*) FROM farmers WHERE is_verified = 1')->fetchColumn(),
    'products' => (int)db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn(),
];

require __DIR__ . '/header.php';
?>
<section class="bg-gradient-to-br from-brand-50 via-white to-brand-100/50 py-16 lg:py-24 border-b border-brand-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-12 items-center">
    <div class="space-y-6">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-100 text-brand-900">
        <i class="fa-solid fa-seedling mr-1.5"></i> Organic &amp; Locally Sourced
      </span>
      <h1 class="text-4xl sm:text-5xl font-extrabold text-brand-900 tracking-tight leading-tight">
        Good food starts with <span class="text-brand-700">good farms.</span>
      </h1>
      <p class="text-gray-600 text-lg">Connect directly with verified local farmers. Enjoy farm-fresh, chemical-free produce delivered to your doorstep within hours.</p>
      <form action="catalogue.php" method="get" class="p-2 bg-white rounded-2xl shadow-xl border border-gray-100 flex flex-col sm:flex-row gap-2">
        <div class="relative flex-grow">
          <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-gray-400"></i>
          <input type="text" name="q" placeholder="What are you craving today? (e.g. Sukuma Wiki, Tomatoes)" class="w-full pl-11 pr-4 py-3 text-sm rounded-xl focus:outline-none">
        </div>
        <button class="btn">Explore <i class="fa-solid fa-arrow-right text-xs"></i></button>
      </form>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <?php foreach ([['🍎','Fresh Fruits','Naturally ripened on tree','emerald',''],['🥦','Farm Greens','Crisp harvest daily','emerald','mt-6'],['🍯','Pure Honey','Raw & unprocessed','amber','-mt-6'],['🥛','Dairy & Eggs','Pasture raised quality','blue','']] as [$em,$t,$d,$c,$m]): ?>
        <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 hover:shadow-lg transition <?= $m ?>">
          <div class="w-12 h-12 bg-<?= $c ?>-100 rounded-xl flex items-center justify-center text-2xl mb-4"><?= $em ?></div>
          <h3 class="font-bold text-gray-900"><?= $t ?></h3>
          <p class="text-xs text-gray-500 mt-1"><?= $d ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bg-brand-900 text-white py-8">
  <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
    <?php foreach ([[$stats['farms'].'+','Verified Local Farms'],[$stats['products'].'+','Fresh Organic Items'],['4.9 ★','Customer Rating'],['< 24 hrs','Harvest to Doorstep']] as [$n,$l]): ?>
      <div><p class="text-3xl font-extrabold text-brand-300"><?= e($n) ?></p><p class="text-xs uppercase tracking-wider text-emerald-200 mt-1"><?= e($l) ?></p></div>
    <?php endforeach; ?>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="flex justify-between items-end mb-8">
    <div><h2 class="text-2xl font-bold text-gray-900">Shop by Category</h2><p class="text-sm text-gray-500">Clean, pesticide-free farm options</p></div>
    <a href="catalogue.php" class="text-sm font-semibold text-brand-800 hover:text-brand-900">View all &rarr;</a>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
    <?php foreach ($categories as $c): ?>
      <a href="catalogue.php?category=<?= e($c['slug']) ?>" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm text-center hover:shadow-md hover:border-brand-300 transition">
        <div class="text-3xl mb-2"><?= e($c['emoji']) ?></div>
        <h4 class="font-bold text-gray-900 text-sm"><?= e($c['name']) ?></h4>
        <span class="text-xs text-gray-400"><?= (int)$c['total'] ?> items</span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-gray-100/70 py-16 border-y border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8"><span class="text-xs font-bold uppercase tracking-wider text-brand-700">Fresh Harvesting</span>
      <h2 class="text-2xl font-bold text-gray-900">Featured Fresh Produce</h2></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6"><?php foreach ($featured as $p) echo product_card($p); ?></div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
  <div class="text-center max-w-2xl mx-auto mb-14">
    <span class="text-xs font-bold uppercase tracking-wider text-brand-700">Simple Process</span>
    <h2 class="text-3xl font-bold text-gray-900 mt-1">How <?= APP_NAME ?> Works</h2>
    <p class="text-gray-500 mt-2 text-sm">We cut out middle-men so farmers are paid fairly and you receive the freshest produce possible.</p>
  </div>
  <div class="grid md:grid-cols-3 gap-8">
    <?php foreach ([['Direct from Farmers','Browse live harvests listed by regional farmers with transparent pricing.'],['Freshly Harvested','Orders trigger morning harvests, so nothing sits on warehouse shelves.'],['Eco-Friendly Delivery','Delivered in reusable organic tote bags right to your kitchen door.']] as $i => [$t,$d]): ?>
      <div class="card p-8 text-center">
        <div class="w-16 h-16 bg-brand-100 text-brand-900 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6"><?= $i + 1 ?></div>
        <h3 class="text-lg font-bold text-gray-900 mb-2"><?= $t ?></h3><p class="text-sm text-gray-600"><?= $d ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-brand-50 py-16 border-t border-brand-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-xl mx-auto mb-12"><h2 class="text-2xl font-bold text-gray-900">Meet Our Local Partner Farmers</h2>
      <p class="text-sm text-gray-600">Supporting sustainable agricultural communities</p></div>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
      <?php foreach ($farmers as $f): ?>
        <a href="farmer_profile.php?id=<?= (int)$f['id'] ?>" class="card p-6 flex items-center space-x-4 hover:shadow-md transition">
          <div class="w-14 h-14 bg-brand-100 rounded-2xl flex items-center justify-center text-3xl"><?= e($f['avatar']) ?></div>
          <div>
            <h4 class="font-bold text-gray-900"><?= e($f['farm_name']) ?></h4>
            <p class="text-xs text-gray-500"><i class="fa-solid fa-location-dot text-brand-700 mr-1"></i><?= e($f['location']) ?></p>
            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700"><?= e($f['speciality']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
