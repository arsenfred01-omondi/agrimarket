<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$st = db()->prepare(
    'SELECT p.*, c.name AS category, c.slug AS category_slug, fr.farm_name, fr.id AS fid, fr.location
       FROM products p
       JOIN categories c ON c.id = p.category_id
       JOIN farmers fr   ON fr.id = p.farmer_id
      WHERE p.id = ? AND p.is_active = 1'
);
$st->execute([(int)($_GET['id'] ?? 0)]);
$p = $st->fetch();
if (!$p) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require __DIR__ . '/header.php';
    echo '<div class="max-w-xl mx-auto py-24 text-center"><h1 class="text-2xl font-bold">Product not found</h1><a class="btn mt-6" href="catalogue.php">Back to catalogue</a></div>';
    require __DIR__ . '/footer.php';
    exit;
}

$related = array_slice(array_filter(
    search_products(['category' => $p['category_slug']]),
    static fn($r) => $r['id'] !== $p['id']
), 0, 3);

$pageTitle = $p['name'];
require __DIR__ . '/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <nav class="flex text-xs text-gray-500 mb-6 space-x-2">
    <a href="index.php" class="hover:text-brand-900">Home</a><span>/</span>
    <a href="catalogue.php?category=<?= e($p['category_slug']) ?>" class="hover:text-brand-900"><?= e($p['category']) ?></a><span>/</span>
    <span class="font-semibold text-gray-800"><?= e($p['name']) ?></span>
  </nav>

  <div class="card p-6 md:p-10 grid md:grid-cols-2 gap-10">
    <div class="relative w-full h-80 sm:h-96 rounded-2xl bg-brand-50 flex items-center justify-center text-8xl border border-brand-100">
      <?= e($p['emoji']) ?>
      <?php if ($p['is_organic']): ?><span class="absolute top-4 left-4 bg-brand-900 text-brand-100 text-xs font-bold px-3 py-1 rounded-full">Organic Certified</span><?php endif; ?>
    </div>

    <div class="flex flex-col justify-between">
      <div>
        <div class="flex justify-between items-start">
          <div>
            <span class="text-xs uppercase tracking-wider font-bold text-brand-700"><?= e($p['category']) ?></span>
            <h1 class="text-3xl font-extrabold text-gray-900 mt-1"><?= e($p['name']) ?></h1>
            <p class="text-sm text-gray-500 mt-1"><i class="fa-solid fa-store text-xs mr-1"></i>Harvested by
              <a class="text-brand-800 font-semibold hover:underline" href="farmer_profile.php?id=<?= (int)$p['fid'] ?>"><?= e($p['farm_name']) ?></a> · <?= e($p['location']) ?></p>
          </div>
          <div class="text-sm font-bold text-amber-500">★ <?= number_format((float)$p['rating'], 1) ?></div>
        </div>

        <div class="mt-6 p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-brand-900"><?= money($p['price']) ?></span>
          <span class="text-sm text-gray-500">/ <?= e($p['unit']) ?></span>
        </div>

        <p class="mt-6 text-sm text-gray-600 leading-relaxed"><?= e($p['description']) ?></p>
        <p class="mt-3 text-xs font-semibold <?= $p['stock'] > 0 ? 'text-emerald-700' : 'text-red-600' ?>">
          <?= $p['stock'] > 0 ? (int)$p['stock'] . ' in stock' : 'Currently out of stock' ?></p>
      </div>

      <?php if ($p['stock'] > 0): ?>
      <form method="post" action="cart.php" class="mt-8 pt-6 border-t border-gray-100 space-y-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
        <input type="hidden" name="back" value="product.php?id=<?= (int)$p['id'] ?>">
        <div class="flex items-center space-x-4">
          <span class="text-sm font-semibold text-gray-700">Quantity:</span>
          <div class="flex items-center border border-gray-200 rounded-xl bg-gray-50 p-1">
            <button type="button" data-qty-step="-1" class="w-9 h-9 rounded-lg bg-white shadow-sm font-bold hover:bg-gray-100">-</button>
            <input id="qty-input" name="qty" type="number" min="1" max="<?= (int)$p['stock'] ?>" value="1" class="w-14 text-center text-sm font-bold bg-transparent focus:outline-none">
            <button type="button" data-qty-step="1" class="w-9 h-9 rounded-lg bg-white shadow-sm font-bold hover:bg-gray-100">+</button>
          </div>
        </div>
        <button class="btn w-full py-3.5"><i class="fa-solid fa-basket-shopping"></i> Add to Cart</button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($related): ?>
  <div class="mt-16">
    <h3 class="text-xl font-bold text-gray-900 mb-6">More from <?= e($p['category']) ?></h3>
    <div class="grid sm:grid-cols-3 gap-6"><?php foreach ($related as $r) echo product_card($r); ?></div>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
