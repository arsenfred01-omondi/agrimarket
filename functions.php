<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

/* ------------------------------------------------------------ Core */

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float|int|string $amount): string
{
    return 'KES ' . number_format((float)$amount, 0);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/** Only allow same-site relative page names (e.g. "catalogue.php?q=kale"). */
function safe_back(?string $path, string $fallback = 'index.php'): string
{
    return ($path && preg_match('/^[a-z_]+\.php(\?[\w=&%.+\-]*)?$/i', $path)) ? $path : $fallback;
}

/* ------------------------------------------------------------ Flash & CSRF */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Session expired. Please go back, refresh the page and try again.');
    }
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/* ------------------------------------------------------------ Auth */

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $st = db()->prepare('SELECT id, name, email, phone, role FROM users WHERE id = ?');
            $st->execute([$_SESSION['user_id']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
    return $user;
}

function require_role(string ...$roles): array
{
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
    return $user;
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}

function farmer_for_user(int $userId): ?array
{
    $st = db()->prepare('SELECT * FROM farmers WHERE user_id = ?');
    $st->execute([$userId]);
    return $st->fetch() ?: null;
}

/* ------------------------------------------------------------ Catalogue */

function get_categories(): array
{
    return db()->query(
        'SELECT c.*, COUNT(p.id) AS total
           FROM categories c
      LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
       GROUP BY c.id ORDER BY c.name'
    )->fetchAll();
}

function search_products(array $f, ?int $limit = null): array
{
    $sql = 'SELECT p.*, c.name AS category, c.slug AS category_slug, fr.farm_name
              FROM products p
              JOIN categories c ON c.id = p.category_id
              JOIN farmers fr   ON fr.id = p.farmer_id
             WHERE p.is_active = 1';
    $args = [];

    if (!empty($f['q'])) {
        $sql .= ' AND (p.name LIKE ? OR fr.farm_name LIKE ?)';
        $like = '%' . $f['q'] . '%';
        array_push($args, $like, $like);
    }
    if (!empty($f['category'])) {
        $sql .= ' AND c.slug = ?';
        $args[] = $f['category'];
    }
    if (isset($f['max_price']) && $f['max_price'] !== '') {
        $sql .= ' AND p.price <= ?';
        $args[] = (float)$f['max_price'];
    }
    if (!empty($f['organic'])) {
        $sql .= ' AND p.is_organic = 1';
    }
    if (!empty($f['farmer_id'])) {
        $sql .= ' AND p.farmer_id = ?';
        $args[] = (int)$f['farmer_id'];
    }

    $orders = ['low-high' => 'p.price ASC', 'high-low' => 'p.price DESC', 'rating' => 'p.rating DESC'];
    $sql .= ' ORDER BY ' . ($orders[$f['sort'] ?? ''] ?? 'p.id ASC');
    if ($limit) {
        $sql .= ' LIMIT ' . (int)$limit;
    }

    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

function product_card(array $p): string
{
    $back  = basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    $badge = $p['is_organic'] ? '<span class="absolute top-3 left-3 bg-brand-900 text-brand-100 text-[10px] font-bold px-2 py-0.5 rounded-full">Organic</span>' : '';
    $out   = $p['stock'] < 1;

    ob_start(); ?>
    <article class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg transition flex flex-col">
      <a href="product.php?id=<?= (int)$p['id'] ?>" class="relative p-6 bg-brand-50 flex items-center justify-center text-6xl">
        <?= e($p['emoji']) ?><?= $badge ?>
      </a>
      <div class="p-5 flex-grow flex flex-col justify-between">
        <div>
          <div class="flex justify-between text-xs text-gray-400">
            <span><?= e($p['category']) ?></span>
            <span class="text-amber-500 font-bold">★ <?= number_format((float)$p['rating'], 1) ?></span>
          </div>
          <h3 class="font-bold text-gray-900 mt-1">
            <a href="product.php?id=<?= (int)$p['id'] ?>" class="hover:text-brand-800"><?= e($p['name']) ?></a>
          </h3>
          <p class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-store mr-1"></i><?= e($p['farm_name']) ?></p>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
          <div>
            <span class="font-extrabold text-brand-900 text-lg"><?= money($p['price']) ?></span>
            <span class="text-xs text-gray-400">/<?= e($p['unit']) ?></span>
          </div>
          <?php if ($out): ?>
            <span class="text-xs font-semibold text-red-500">Out of stock</span>
          <?php else: ?>
            <form method="post" action="cart.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
              <input type="hidden" name="back" value="<?= e($back) ?>">
              <button class="p-2.5 bg-brand-900 hover:bg-brand-800 text-white rounded-xl shadow transition" aria-label="Add to cart">
                <i class="fa-solid fa-cart-plus"></i>
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </article>
    <?php
    return (string)ob_get_clean();
}

/* ------------------------------------------------------------ Cart */

function cart_add(int $productId, int $qty = 1): void
{
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + max(1, $qty);
}

function cart_set(int $productId, int $qty): void
{
    if ($qty < 1) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $qty;
    }
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}

function cart_clear(): void
{
    unset($_SESSION['cart'], $_SESSION['promo']);
}

/** Cart lines joined with live product data (price, stock). */
function cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }
    $ids = array_keys($cart);
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $st  = db()->prepare(
        "SELECT p.*, fr.farm_name FROM products p
           JOIN farmers fr ON fr.id = p.farmer_id
          WHERE p.id IN ($in) AND p.is_active = 1"
    );
    $st->execute($ids);

    $items = [];
    foreach ($st->fetchAll() as $p) {
        $qty = min((int)$cart[$p['id']], (int)$p['stock']);
        if ($qty < 1) {
            continue;
        }
        $items[] = ['product' => $p, 'qty' => $qty, 'subtotal' => (float)$p['price'] * $qty];
    }
    return $items;
}

function find_promo(string $code): ?array
{
    $st = db()->prepare('SELECT * FROM promo_codes WHERE code = ? AND is_active = 1');
    $st->execute([strtoupper(trim($code))]);
    return $st->fetch() ?: null;
}

function cart_totals(array $items): array
{
    $subtotal = array_sum(array_column($items, 'subtotal'));
    $shipping = $subtotal > 0 ? DELIVERY_FEE : 0;
    $promo    = !empty($_SESSION['promo']) ? find_promo($_SESSION['promo']) : null;
    $discount = $promo ? round($subtotal * ((int)$promo['percent_off'] / 100)) : 0;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'discount' => $discount,
        'promo'    => $promo['code'] ?? null,
        'total'    => max(0, $subtotal + $shipping - $discount),
    ];
}

/* ------------------------------------------------------------ Misc */

function order_badge(string $status): string
{
    $colors = [
        'pending'   => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'delivered' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    return '<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold ' . ($colors[$status] ?? 'bg-gray-100 text-gray-700') . '">' . e(ucfirst($status)) . '</span>';
}

function valid_phone(string $phone): bool
{
    return (bool)preg_match('/^\+?[0-9 ]{9,15}$/', $phone);
}
