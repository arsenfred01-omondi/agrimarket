<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

$errors = [];
$old = ['name' => current_user()['name'] ?? '', 'email' => current_user()['email'] ?? ($_GET['email'] ?? ''), 'subject' => '', 'message' => ''];

if (is_post()) {
    csrf_check();
    foreach ($old as $k => $_) $old[$k] = trim((string)($_POST[$k] ?? ''));
    if ($old['name'] === '' || $old['subject'] === '' || mb_strlen($old['message']) < 10) $errors[] = 'Please complete all fields (message at least 10 characters).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

    if (!$errors) {
        db()->prepare('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)')
            ->execute([mb_substr($old['name'], 0, 120), $old['email'], mb_substr($old['subject'], 0, 150), mb_substr($old['message'], 0, 4000)]);
        flash('success', 'Thanks! Your message has been received — we will reply shortly.');
        redirect('contact.php');
    }
}

$pageTitle = 'Contact Us';
require __DIR__ . '/header.php';
?>
<div class="max-w-xl mx-auto px-4 py-12">
  <div class="card p-8">
    <h1 class="text-2xl font-extrabold text-brand-900 mb-1">Contact Us</h1>
    <p class="text-sm text-gray-500 mb-6">Questions about an order, or want to sell with us? Send us a note.</p>
    <?php foreach ($errors as $err): ?><p class="mb-2 px-4 py-2 rounded-xl bg-red-50 text-red-700 text-sm"><?= e($err) ?></p><?php endforeach; ?>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="label">Name</label><input class="input" name="name" required value="<?= e($old['name']) ?>"></div>
        <div><label class="label">Email</label><input class="input" type="email" name="email" required value="<?= e($old['email']) ?>"></div>
      </div>
      <div><label class="label">Subject</label><input class="input" name="subject" required value="<?= e($old['subject']) ?>"></div>
      <div><label class="label">Message</label><textarea class="input" name="message" rows="5" required><?= e($old['message']) ?></textarea></div>
      <button class="btn w-full">Send Message</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
