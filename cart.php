<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $pid    = (int)($_POST['product_id'] ?? 0);
    $back   = safe_back($_POST['back'] ?? null, 'cart.php');

    switch ($action) {
        case 'add':
            $st = db()->prepare('SELECT name, stock FROM products WHERE id = ? AND is_active = 1');
            $st->execute([$pid]);
            if ($p = $st->fetch()) {
                $want = ($_SESSION['cart'][$pid] ?? 0) + max(1, (int)($_POST['qty'] ?? 1));
                if ($want > (int)$p['stock']) {
                    flash('error', 'Only ' . (int)$p['stock'] . ' of ' . $p['name'] . ' available.');
                } else {
                    cart_add($pid, (int)($_POST['qty'] ?? 1));
                    flash('success', $p['name'] . ' added to cart.');
                }
            }
            redirect($back);
        case 'update':
            cart_set($pid, (int)($_POST['qty'] ?? 0));
            redirect('cart.php');
        case 'remove':
            cart_set($pid, 0);
            flash('success', 'Item removed from cart.');
            redirect('cart.php');
        case 'promo':
            $promo = find_promo((string)($_POST['code'] ?? ''));
            if ($promo) {
                $_SESSION['promo'] = $promo['code'];
                flash('success', $promo['percent_off'] . '% discount applied!');
            } else {
                unset($_SESSION['promo']);
                flash('error', 'Invalid promo code.');
            }
            redirect('cart.php');
    }
    redirect('cart.php');
}

$items  = cart_items();
$totals = cart_totals($items);
$pageTitle = 'Your Cart';
require __DIR__ . '/header.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-3xl font-extrabold text-brand-900 mb-8">Your Fresh Produce Cart</h1>

  <?php if (!$items): ?>
    <div class="card py-16 text-center">
      <i class="fa-solid fa-basket-shopping text-5xl text-gray-300 mb-3"></i>
      <p class="text-gray-500 font-medium">Your cart is currently empty.</p>
      <a href="catalogue.php" class="btn mt-4">Start Shopping</a>
    </div>
  <?php else: ?>
  <div class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 card p-6 overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead><tr class="border-b border-gray-100 text-xs font-bold uppercase text-gray-400">
          <th class="pb-4">Product</th><th class="pb-4">Price</th><th class="pb-4">Quantity</th><th class="pb-4 text-right">Subtotal</th><th></th></tr></thead>
        <tbody class="divide-y divide-gray-100">
        <?php foreach ($items as $i): $p = $i['product']; ?>
          <tr>
            <td class="py-4"><div class="flex items-center space-x-3">
              <span class="text-3xl bg-brand-50 p-2 rounded-xl"><?= e($p['emoji']) ?></span>
              <div><a href="product.php?id=<?= (int)$p['id'] ?>" class="font-bold text-gray-900 hover:text-brand-800"><?= e($p['name']) ?></a>
                <p class="text-xs text-gray-400"><?= e($p['farm_name']) ?></p></div></div></td>
            <td class="py-4 font-semibold"><?= money($p['price']) ?></td>
            <td class="py-4">
              <form method="post" class="flex items-center gap-2">
                <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <input type="number" name="qty" min="0" max="<?= (int)$p['stock'] ?>" value="<?= $i['qty'] ?>" class="w-16 px-2 py-1 border border-gray-200 rounded-lg text-center text-xs">
                <button class="text-xs font-semibold text-brand-800 hover:underline">Update</button>
              </form></td>
            <td class="py-4 text-right font-extrabold text-brand-900"><?= money($i['subtotal']) ?></td>
            <td class="py-4 text-right">
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                <button class="text-gray-400 hover:text-red-500" aria-label="Remove"><i class="fa-solid fa-trash-can"></i></button></form></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <aside class="card p-6 space-y-4 h-fit">
      <h3 class="font-bold text-gray-900 text-lg border-b border-gray-100 pb-3">Order Summary</h3>
      <div class="flex justify-between text-sm text-gray-600"><span>Items Subtotal</span><span class="font-semibold text-gray-800"><?= money($totals['subtotal']) ?></span></div>
      <div class="flex justify-between text-sm text-gray-600"><span>Eco Express Delivery</span><span class="font-semibold text-gray-800"><?= money($totals['shipping']) ?></span></div>
      <?php if ($totals['promo']): ?>
        <div class="flex justify-between text-sm text-emerald-700 font-semibold"><span>Promo (<?= e($totals['promo']) ?>)</span><span>- <?= money($totals['discount']) ?></span></div>
      <?php endif; ?>
      <form method="post" class="flex gap-2 pt-2">
        <?= csrf_field() ?><input type="hidden" name="action" value="promo">
        <input type="text" name="code" placeholder="Promo code (ORGANIC10)" class="input !py-2 !text-xs">
        <button class="px-4 py-2 bg-gray-800 text-white rounded-xl text-xs font-semibold hover:bg-gray-900">Apply</button>
      </form>
      <div class="pt-4 border-t border-gray-100 flex justify-between items-baseline">
        <span class="font-bold text-gray-900">Total Amount</span>
        <span class="text-2xl font-extrabold text-brand-900"><?= money($totals['total']) ?></span>
      </div>
      <a href="checkout.php" class="btn w-full py-3.5">Proceed to Checkout <i class="fa-solid fa-arrow-right text-xs"></i></a>
    </aside>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/footer.php'; ?>
