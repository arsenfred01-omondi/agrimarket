<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$user = require_login();
$errors = [];

if (is_post()) {
    csrf_check();
    $name  = trim((string)($_POST['name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $new   = (string)($_POST['new_password'] ?? '');

    if (mb_strlen($name) < 2) $errors[] = 'Please enter your name.';
    if (!valid_phone($phone)) $errors[] = 'Enter a valid phone number.';

    if ($new !== '') {
        $st = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $st->execute([$user['id']]);
        if (!password_verify((string)($_POST['current_password'] ?? ''), (string)$st->fetchColumn())) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
    }

    if (!$errors) {
        db()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?')->execute([$name, $phone, $user['id']]);
        if ($new !== '') {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        }
        flash('success', 'Profile updated.');
        redirect('profile.php');
    }
}

$pageTitle = 'My Profile';
require __DIR__ . '/header.php';
?>
<div class="max-w-xl mx-auto px-4 py-12">
  <div class="card p-8">
    <h1 class="text-2xl font-extrabold text-brand-900 mb-1">My Profile</h1>
    <p class="text-sm text-gray-500 mb-6"><?= e($user['email']) ?> · <?= e(ucfirst($user['role'])) ?></p>
    <?php foreach ($errors as $err): ?><p class="mb-2 px-4 py-2 rounded-xl bg-red-50 text-red-700 text-sm"><?= e($err) ?></p><?php endforeach; ?>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div><label class="label">Full Name</label><input class="input" name="name" required value="<?= e($user['name']) ?>"></div>
      <div><label class="label">Phone</label><input class="input" name="phone" required value="<?= e($user['phone']) ?>"></div>
      <fieldset class="pt-4 border-t border-gray-100 space-y-4">
        <legend class="label">Change password (optional)</legend>
        <input class="input" type="password" name="current_password" placeholder="Current password">
        <input class="input" type="password" name="new_password" minlength="8" placeholder="New password">
      </fieldset>
      <button class="btn w-full">Save Changes</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
