<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user   = require_role('farmer');
$farmer = farmer_for_user((int)$user['id']);
if (!$farmer) { http_response_code(403); exit('Farmer profile missing.'); }
$pdo = db();

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name  = trim((string)($_POST['name'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        if ($name === '' || $price <= 0 || $stock < 0) {
            flash('error', 'Enter a product name, a price above zero and a valid stock level.');
        } else {
            $pdo->prepare('INSERT INTO products (farmer_id,category_id,name,description,price,unit,stock,emoji,is_organic) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$farmer['id'], (int)$_POST['category_id'], $name, trim((string)$_POST['description']), $price,
                           trim((string)($_POST['unit'] ?: 'kg')), $stock, trim((string)($_POST['emoji'] ?: '🥬')), empty($_POST['is_organic']) ? 0 : 1]);
            flash('success', 'Product added.');
        }
    } elseif ($action === 'stock') {
        $pdo->prepare('UPDATE products SET stock = ?, price = ? WHERE id = ? AND farmer_id = ?')
            ->execute([max(0, (int)$_POST['stock']), max(1, (float)$_POST['price']), (int)$_POST['product_id'], $farmer['id']]);
        flash('success', 'Product updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare('UPDATE products SET is_active = 0 WHERE id = ? AND farmer_id = ?')->execute([(int)$_POST['product_id'], $farmer['id']]);
        flash('success', 'Product removed from the catalogue.');
    }
    redirect('farmer.php');
}

$st = $pdo->prepare('SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id WHERE p.farmer_id = ? AND p.is_active = 1 ORDER BY p.id DESC');
$st->execute([$farmer['id']]);
$products = $st->fetchAll();

$st = $pdo->prepare(
    'SELECT o.order_number, o.status, o.created_at, oi.product_name, oi.quantity, oi.unit_price
       FROM order_items oi JOIN orders o ON o.id = oi.order_id
      WHERE oi.farmer_id = ? ORDER BY o.id DESC LIMIT 15'
);
$st->execute([$farmer['id']]);
$sales = $st->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Farmer Dashboard';
require __DIR__ . '/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-3xl font-extrabold text-brand-900"><?= e($farmer['farm_name']) ?></h1>
      <p class="text-sm text-gray-500"><?= $farmer['is_verified'] ? 'Verified farm — your products are live.' : 'Awaiting verification — products stay hidden from buyers until an admin approves your farm.' ?></p>
    </div>
    <?php if ($farmer['is_verified']): ?><a class="btn" href="farmer_profile.php?id=<?= (int)$farmer['id'] ?>">View public profile</a><?php endif; ?>
  </div>

  <section class="card p-6">
    <h2 class="font-bold text-lg mb-4">Add a product</h2>
    <form method="post" class="grid md:grid-cols-4 gap-4">
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <div class="md:col-span-2"><label class="label">Name</label><input class="input" name="name" required></div>
      <div><label class="label">Category</label><select class="input" name="category_id"><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div><label class="label">Emoji</label><input class="input" name="emoji" maxlength="4" placeholder="🥬"></div>
      <div><label class="label">Price (KES)</label><input class="input" type="number" step="1" min="1" name="price" required></div>
      <div><label class="label">Unit</label><input class="input" name="unit" placeholder="kg, bunch, tray..."></div>
      <div><label class="label">Stock</label><input class="input" type="number" min="0" name="stock" required></div>
      <label class="flex items-end gap-2 text-sm pb-3"><input type="checkbox" name="is_organic" value="1" checked class="accent-brand-800"> Certified organic</label>
      <div class="md:col-span-4"><label class="label">Description</label><textarea class="input" name="description" rows="2"></textarea></div>
      <div class="md:col-span-4"><button class="btn">Add product</button></div>
    </form>
  </section>

  <section class="card p-6 overflow-x-auto">
    <h2 class="font-bold text-lg mb-4">My products (<?= count($products) ?>)</h2>
    <table class="w-full text-sm text-left">
      <thead><tr class="text-xs uppercase text-gray-400 border-b border-gray-100"><th class="pb-3">Product</th><th class="pb-3">Price</th><th class="pb-3">Stock</th><th></th></tr></thead>
      <tbody class="divide-y divide-gray-100">
      <?php foreach ($products as $p): ?>
        <tr>
          <td class="py-3"><?= e($p['emoji'] . ' ' . $p['name']) ?> <span class="text-xs text-gray-400">· <?= e($p['category']) ?></span></td>
          <td colspan="2">
            <form method="post" class="flex items-center gap-2">
              <?= csrf_field() ?><input type="hidden" name="action" value="stock"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="number" name="price" min="1" value="<?= (int)$p['price'] ?>" class="w-24 px-2 py-1 border border-gray-200 rounded-lg text-xs">
              <input type="number" name="stock" min="0" value="<?= (int)$p['stock'] ?>" class="w-20 px-2 py-1 border border-gray-200 rounded-lg text-xs">
              <button class="text-xs font-semibold text-brand-800 hover:underline">Save</button>
            </form>
          </td>
          <td class="text-right">
            <form method="post" data-confirm="Remove this product from the catalogue?">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <button class="text-gray-400 hover:text-red-500" aria-label="Delete"><i class="fa-solid fa-trash-can"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="card p-6 overflow-x-auto">
    <h2 class="font-bold text-lg mb-4">Recent sales</h2>
    <?php if (!$sales): ?><p class="text-sm text-gray-500">No orders yet.</p><?php else: ?>
    <table class="w-full text-sm text-left">
      <thead><tr class="text-xs uppercase text-gray-400 border-b border-gray-100"><th class="pb-3">Order</th><th class="pb-3">Item</th><th class="pb-3">Qty</th><th class="pb-3">Amount</th><th class="pb-3">Status</th></tr></thead>
      <tbody class="divide-y divide-gray-100">
      <?php foreach ($sales as $s): ?>
        <tr><td class="py-3 font-semibold"><?= e($s['order_number']) ?></td><td><?= e($s['product_name']) ?></td><td><?= (int)$s['quantity'] ?></td>
            <td><?= money($s['quantity'] * $s['unit_price']) ?></td><td><?= order_badge($s['status']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table><?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/footer.php'; ?>
