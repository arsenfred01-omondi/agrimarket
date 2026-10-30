<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

require_role('admin');
$pdo = db();

if (is_post()) {
    csrf_check();
    switch ($_POST['action'] ?? '') {
        case 'order_status':
            if (in_array($_POST['status'], ['pending', 'confirmed', 'delivered', 'cancelled'], true)) {
                $paid = $_POST['status'] === 'delivered' ? ", payment_status = 'paid'" : '';
                $pdo->prepare("UPDATE orders SET status = ? $paid WHERE id = ?")->execute([$_POST['status'], (int)$_POST['id']]);
                flash('success', 'Order updated.');
            }
            break;
        case 'verify_farmer':
            $pdo->prepare('UPDATE farmers SET is_verified = 1 - is_verified WHERE id = ?')->execute([(int)$_POST['id']]);
            flash('success', 'Farmer verification toggled.');
            break;
        case 'toggle_product':
            $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([(int)$_POST['id']]);
            flash('success', 'Product visibility toggled.');
            break;
    }
    redirect('admin.php?tab=' . urlencode((string)($_POST['tab'] ?? 'orders')));
}

$tab  = in_array($_GET['tab'] ?? '', ['orders', 'farmers', 'products', 'users', 'messages'], true) ? $_GET['tab'] : 'orders';
$stat = $pdo->query(
    "SELECT (SELECT COUNT(*) FROM users) users, (SELECT COUNT(*) FROM orders) orders,
            (SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled') revenue,
            (SELECT COUNT(*) FROM farmers WHERE is_verified = 0) pending_farmers"
)->fetch();

$rows = match ($tab) {
    'orders'   => $pdo->query('SELECT o.*, u.name AS customer FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 100')->fetchAll(),
    'farmers'  => $pdo->query('SELECT fr.*, u.email FROM farmers fr JOIN users u ON u.id = fr.user_id ORDER BY fr.is_verified, fr.id DESC')->fetchAll(),
    'products' => $pdo->query('SELECT p.*, fr.farm_name FROM products p JOIN farmers fr ON fr.id = p.farmer_id ORDER BY p.id DESC LIMIT 200')->fetchAll(),
    'users'    => $pdo->query('SELECT id, name, email, phone, role, created_at FROM users ORDER BY id DESC LIMIT 200')->fetchAll(),
    'messages' => $pdo->query('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 100')->fetchAll(),
};

$pageTitle = 'Admin Panel';
require __DIR__ . '/header.php';

$toggle = fn(string $action, int $id, string $label) =>
    '<form method="post" class="inline">' . csrf_field() . '<input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="tab" value="' . e($GLOBALS['tab']) . '"><button class="text-xs font-semibold text-brand-800 hover:underline">' . $label . '</button></form>';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-3xl font-extrabold text-brand-900 mb-6">Admin Panel</h1>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <?php foreach ([['Users', $stat['users']], ['Orders', $stat['orders']], ['Revenue', money($stat['revenue'])], ['Farms to verify', $stat['pending_farmers']]] as [$l, $v]): ?>
      <div class="card p-5"><p class="text-xs uppercase text-gray-400 font-bold"><?= $l ?></p><p class="text-2xl font-extrabold text-brand-900 mt-1"><?= e((string)$v) ?></p></div>
    <?php endforeach; ?>
  </div>

  <nav class="flex gap-2 mb-6 text-sm font-semibold overflow-x-auto">
    <?php foreach (['orders', 'farmers', 'products', 'users', 'messages'] as $t): ?>
      <a href="admin.php?tab=<?= $t ?>" class="px-4 py-2 rounded-xl <?= $tab === $t ? 'bg-brand-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-brand-50' ?>"><?= ucfirst($t) ?></a>
    <?php endforeach; ?>
  </nav>

  <div class="card p-6 overflow-x-auto text-sm">
  <?php if (!$rows): ?><p class="text-gray-500">Nothing here yet.</p>
  <?php elseif ($tab === 'orders'): ?>
    <table class="w-full text-left"><thead class="text-xs uppercase text-gray-400"><tr><th class="pb-3">Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead><tbody class="divide-y divide-gray-100">
    <?php foreach ($rows as $o): ?><tr>
      <td class="py-3 font-semibold"><a class="hover:underline" href="receipt.php?id=<?= (int)$o['id'] ?>"><?= e($o['order_number']) ?></a></td>
      <td><?= e($o['customer']) ?></td><td><?= money($o['total']) ?></td><td><?= e(strtoupper($o['payment_method'])) ?> · <?= e($o['payment_status']) ?></td><td><?= order_badge($o['status']) ?></td>
      <td><form method="post" class="flex gap-2"><?= csrf_field() ?><input type="hidden" name="action" value="order_status"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="tab" value="orders">
        <select name="status" class="text-xs border border-gray-200 rounded-lg px-2 py-1"><?php foreach (['pending', 'confirmed', 'delivered', 'cancelled'] as $s): ?><option <?= $o['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
        <button class="text-xs font-semibold text-brand-800 hover:underline">Update</button></form></td></tr><?php endforeach; ?>
    </tbody></table>
  <?php elseif ($tab === 'farmers'): ?>
    <table class="w-full text-left"><thead class="text-xs uppercase text-gray-400"><tr><th class="pb-3">Farm</th><th>Location</th><th>Email</th><th>Status</th><th></th></tr></thead><tbody class="divide-y divide-gray-100">
    <?php foreach ($rows as $f): ?><tr><td class="py-3 font-semibold"><?= e($f['farm_name']) ?></td><td><?= e($f['location']) ?></td><td><?= e($f['email']) ?></td>
      <td><?= $f['is_verified'] ? '<span class="text-emerald-700 font-semibold">Verified</span>' : '<span class="text-amber-600 font-semibold">Pending</span>' ?></td>
      <td><?= $toggle('verify_farmer', (int)$f['id'], $f['is_verified'] ? 'Revoke' : 'Verify') ?></td></tr><?php endforeach; ?>
    </tbody></table>
  <?php elseif ($tab === 'products'): ?>
    <table class="w-full text-left"><thead class="text-xs uppercase text-gray-400"><tr><th class="pb-3">Product</th><th>Farm</th><th>Price</th><th>Stock</th><th>Visible</th><th></th></tr></thead><tbody class="divide-y divide-gray-100">
    <?php foreach ($rows as $p): ?><tr><td class="py-3"><?= e($p['emoji'] . ' ' . $p['name']) ?></td><td><?= e($p['farm_name']) ?></td><td><?= money($p['price']) ?></td><td><?= (int)$p['stock'] ?></td>
      <td><?= $p['is_active'] ? 'Yes' : 'No' ?></td><td><?= $toggle('toggle_product', (int)$p['id'], $p['is_active'] ? 'Hide' : 'Show') ?></td></tr><?php endforeach; ?>
    </tbody></table>
  <?php elseif ($tab === 'users'): ?>
    <table class="w-full text-left"><thead class="text-xs uppercase text-gray-400"><tr><th class="pb-3">Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th></tr></thead><tbody class="divide-y divide-gray-100">
    <?php foreach ($rows as $u): ?><tr><td class="py-3 font-semibold"><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td><td><?= e($u['role']) ?></td><td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table>
  <?php else: ?>
    <div class="divide-y divide-gray-100">
    <?php foreach ($rows as $m): ?><div class="py-4"><p class="font-semibold"><?= e($m['subject']) ?> <span class="text-xs text-gray-400 font-normal">· <?= e($m['name']) ?> &lt;<?= e($m['email']) ?>&gt; · <?= e(date('d M Y H:i', strtotime($m['created_at']))) ?></span></p>
      <p class="text-gray-600 mt-1 whitespace-pre-line"><?= e($m['message']) ?></p></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
