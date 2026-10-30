<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (current_user()) redirect('index.php');
$errors = [];
$old = ['role' => 'buyer', 'name' => '', 'email' => '', 'phone' => '', 'location' => ''];

if (is_post()) {
    csrf_check();
    $old = [
        'role'     => ($_POST['role'] ?? '') === 'farmer' ? 'farmer' : 'buyer',
        'name'     => trim((string)($_POST['name'] ?? '')),
        'email'    => strtolower(trim((string)($_POST['email'] ?? ''))),
        'phone'    => trim((string)($_POST['phone'] ?? '')),
        'location' => trim((string)($_POST['location'] ?? '')),
    ];
    $password = (string)($_POST['password'] ?? '');

    if (mb_strlen($old['name']) < 2) $errors[] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!valid_phone($old['phone'])) $errors[] = 'Enter a valid phone number.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($old['role'] === 'farmer' && $old['location'] === '') $errors[] = 'Farmers must provide a farm location.';

    if (!$errors) {
        $chk = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $chk->execute([$old['email']]);
        if ($chk->fetch()) $errors[] = 'An account with that email already exists.';
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO users (name,email,phone,password_hash,role) VALUES (?,?,?,?,?)')
            ->execute([$old['name'], $old['email'], $old['phone'], password_hash($password, PASSWORD_DEFAULT), $old['role']]);
        $uid = (int)$pdo->lastInsertId();
        if ($old['role'] === 'farmer') {
            $pdo->prepare('INSERT INTO farmers (user_id,farm_name,location) VALUES (?,?,?)')->execute([$uid, $old['name'], $old['location']]);
        }
        $pdo->commit();

        login_user($uid);
        flash('success', $old['role'] === 'farmer' ? 'Account created! Your farm is pending verification by our team.' : 'Account created successfully!');
        redirect($old['role'] === 'farmer' ? 'farmer.php' : 'index.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/header.php';
?>
<div class="max-w-4xl mx-auto px-4 py-12 md:py-16">
  <div class="card overflow-hidden grid md:grid-cols-2 shadow-xl">
    <div class="bg-brand-900 p-8 md:p-12 text-white flex flex-col justify-between">
      <div>
        <div class="flex items-center space-x-2 text-brand-300 font-bold text-xl mb-8"><i class="fa-solid fa-leaf"></i><span><?= APP_NAME ?></span></div>
        <h2 class="text-3xl font-extrabold mb-4">Join <?= APP_NAME ?></h2>
        <p class="text-brand-100 text-sm leading-relaxed">Register as a fresh produce Buyer or join our network as a certified organic Farmer.</p>
      </div>
      <div class="p-4 bg-brand-800/60 rounded-xl border border-brand-700 text-xs text-emerald-100 mt-8">
        <p class="font-bold mb-1">Are you a local farmer?</p><p>Sell directly to thousands of buyers with transparent pricing &amp; logistics support.</p>
      </div>
    </div>
    <div class="p-8 md:p-12">
      <h3 class="text-2xl font-bold text-gray-900 mb-6">Create Account</h3>
      <?php foreach ($errors as $err): ?><p class="mb-2 px-4 py-2 rounded-xl bg-red-50 text-red-700 text-sm"><?= e($err) ?></p><?php endforeach; ?>
      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl text-xs font-semibold text-center">
          <label class="py-2.5 rounded-lg cursor-pointer has-[:checked]:bg-white has-[:checked]:shadow-sm has-[:checked]:text-brand-900 text-gray-600">
            <input type="radio" name="role" value="buyer" class="sr-only" <?= $old['role'] === 'buyer' ? 'checked' : '' ?>>Consumer Buyer</label>
          <label class="py-2.5 rounded-lg cursor-pointer has-[:checked]:bg-white has-[:checked]:shadow-sm has-[:checked]:text-brand-900 text-gray-600">
            <input type="radio" name="role" value="farmer" class="sr-only" <?= $old['role'] === 'farmer' ? 'checked' : '' ?>>Farm Producer</label>
        </div>
        <div><label class="label">Full Name / Farm Name</label><input class="input" name="name" required placeholder="Jane Doe" value="<?= e($old['name']) ?>"></div>
        <div id="farmer-fields" class="hidden"><label class="label">Farm Location</label><input class="input" name="location" placeholder="e.g. Kiambu" value="<?= e($old['location']) ?>"></div>
        <div><label class="label">Email Address</label><input class="input" type="email" name="email" required placeholder="jane@example.com" value="<?= e($old['email']) ?>"></div>
        <div><label class="label">Phone Number</label><input class="input" type="tel" name="phone" required placeholder="+254 700 000 000" value="<?= e($old['phone']) ?>"></div>
        <div><label class="label">Password</label><input class="input" type="password" name="password" required minlength="8" placeholder="Minimum 8 characters"></div>
        <label class="flex items-center text-xs text-gray-600"><input type="checkbox" required class="mr-2 accent-brand-800"> I agree to the Terms of Service &amp; Privacy Policy</label>
        <button class="btn w-full py-3">Create Account</button>
      </form>
      <p class="mt-4 text-center text-xs text-gray-500">Already registered? <a href="login.php" class="text-brand-800 font-bold hover:underline">Sign in</a></p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
