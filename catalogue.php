<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$pageTitle = 'Produce Catalogue';
$filters = [
    'q'         => trim((string)($_GET['q'] ?? '')),
    'category'  => (string)($_GET['category'] ?? ''),
    'max_price' => (string)($_GET['max_price'] ?? '600'),
    'organic'   => !empty($_GET['organic']),
    'sort'      => (string)($_GET['sort'] ?? ''),
];
$products   = search_products($filters);
$categories = get_categories();

require __DIR__ . '/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div class="pb-6 border-b border-gray-200">
    <h1 class="text-3xl font-extrabold text-brand-900">Produce Catalogue</h1>
    <p class="text-sm text-gray-500 mt-1">Browse fresh, clean produce straight from organic local growers.</p>
  </div>

  <form id="catalogue-filters" method="get" class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-8">
    <aside class="space-y-6 card p-6 h-fit">
      <div class="flex items-center justify-between pb-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">Filters</h3>
        <a href="catalogue.php" class="text-xs text-brand-800 font-semibold hover:underline">Reset all</a>
      </div>
      <div>
        <label class="label">Search</label>
        <input type="text" name="q" value="<?= e($filters['q']) ?>" placeholder="Filter catalogue..." class="input">
      </div>
      <div>
        <h4 class="label mb-3">Categories</h4>
        <div class="space-y-2 text-sm text-gray-600">
          <label class="flex items-center"><input type="radio" name="category" value="" <?= $filters['category'] === '' ? 'checked' : '' ?> class="accent-brand-800 mr-2"> All</label>
          <?php foreach ($categories as $c): ?>
            <label class="flex items-center"><input type="radio" name="category" value="<?= e($c['slug']) ?>" <?= $filters['category'] === $c['slug'] ? 'checked' : '' ?> class="accent-brand-800 mr-2"> <?= e($c['name']) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="pt-4 border-t border-gray-100">
        <h4 class="label mb-3">Max Price (KES)</h4>
        <input type="range" id="price-range" name="max_price" min="50" max="600" step="25" value="<?= (int)$filters['max_price'] ?>" class="w-full accent-brand-800">
        <div class="flex justify-between text-xs font-semibold text-gray-600 mt-2"><span>KES 50</span><span id="price-range-value">KES <?= (int)$filters['max_price'] ?></span></div>
      </div>
      <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
        <span class="text-sm font-semibold text-gray-700">100% Organic Only</span>
        <input type="checkbox" name="organic" value="1" <?= $filters['organic'] ? 'checked' : '' ?> class="w-4 h-4 accent-brand-800">
      </div>
      <noscript><button class="btn w-full">Apply filters</button></noscript>
    </aside>

    <div class="lg:col-span-3">
      <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500"><?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?> found</p>
        <select name="sort" class="px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl">
          <?php foreach (['' => 'Sort: Featured', 'low-high' => 'Price: Low to High', 'high-low' => 'Price: High to Low', 'rating' => 'Highest Rated'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= $filters['sort'] === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (!$products): ?>
        <div class="text-center py-16 card">
          <i class="fa-solid fa-leaf text-4xl text-gray-300 mb-3"></i>
          <h3 class="text-lg font-bold text-gray-800">No produce found</h3>
          <p class="text-sm text-gray-500 mt-1">Try tweaking your search term or filter criteria.</p>
        </div>
      <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"><?php foreach ($products as $p) echo product_card($p); ?></div>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/footer.php'; ?>
