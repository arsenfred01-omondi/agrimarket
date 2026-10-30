<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (current_user()) redirect('index.php');
$error = '';

if (is_post()) {
    csrf_check();
    $st = db()->prepare('SELECT id, password_hash, role FROM users WHERE email = ?');
    $st->execute([strtolower(trim((string)($_POST['email'] ?? '')))]);
    $u = $st->fetch();

    if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
        login_user((int)$u['id']);
        flash('success', 'Welcome back!');
        redirect(match ($u['role']) { 'admin' => 'admin.php', 'farmer' => 'farmer.php', default => 'index.php' });
    }
    $error = 'Invalid email or password.';
}

$pageTitle = 'Log In';
require __DIR__ . '/header.php';
?>
<div class="max-w-4xl mx-auto px-4 py-12 md:py-20">
  <div class="card overflow-hidden grid md:grid-cols-2 shadow-xl">
    <div class="bg-brand-900 p-8 md:p-12 text-white flex flex-col justify-between">
      <div>
        <div class="flex items-center space-x-2 text-brand-300 font-bold text-xl mb-8"><i class="fa-solid fa-leaf"></i><span><?= APP_NAME ?></span></div>
        <h2 class="text-3xl font-extrabold mb-4">Welcome Back!</h2>
        <p class="text-brand-100 text-sm leading-relaxed">Log in to access your cart, track orders and manage your farm listings.</p>
      </div>
      <ul class="space-y-3 text-xs text-emerald-200 mt-8">
        <li><i class="fa-solid fa-check-circle text-brand-300 mr-2"></i>Direct farmer support guarantee</li>
        <li><i class="fa-solid fa-check-circle text-brand-300 mr-2"></i>Zero pesticide assurance</li>
      </ul>
    </div>
    <div class="p-8 md:p-12 flex flex-col justify-center">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">Account Login</h3>
      <?php if ($error): ?><p class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-700 text-sm"><?= e($error) ?></p><?php endif; ?>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <div><label class="label">Email Address</label><input class="input" type="email" name="email" required placeholder="name@domain.com" value="<?= e($_POST['email'] ?? '') ?>"></div>
        <div><label class="label">Password</label><input class="input" type="password" name="password" required placeholder="••••••••"></div>
        <button class="btn w-full py-3">Sign In</button>
      </form>
      <p class="mt-6 text-center text-xs text-gray-500">Don't have an account? <a href="register.php" class="text-brand-800 font-bold hover:underline">Register here</a></p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
