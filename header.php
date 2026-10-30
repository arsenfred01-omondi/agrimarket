<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Farm Fresh Organic Produce';
$user      = current_user();
$current   = basename($_SERVER['SCRIPT_NAME']);
$navLink   = fn(string $file, string $label) =>
    '<a href="' . $file . '" class="hover:text-brand-800 transition-colors ' . ($current === $file ? 'text-brand-800 font-bold' : '') . '">' . $label . '</a>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> · <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { brand: {
      900:'#1b4332', 800:'#2d6a4f', 700:'#40916c', 500:'#52b788', 300:'#74c69d', 100:'#d8f3dc', 50:'#f4fbf7'
    } } } } };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

<header class="sticky top-0 z-40 bg-white border-b border-gray-100 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
    <a href="index.php" class="flex items-center space-x-3">
      <span class="w-10 h-10 rounded-xl bg-brand-900 flex items-center justify-center shadow-md">
        <i class="fa-solid fa-leaf text-brand-300"></i>
      </span>
      <span class="text-2xl font-bold tracking-tight text-brand-900">Fred<span class="text-brand-500">AgriMarket</span></span>
    </a>

    <nav class="hidden md:flex items-center space-x-7 text-sm font-medium text-gray-600">
      <?= $navLink('index.php', 'Home') ?>
      <?= $navLink('catalogue.php', 'Produce') ?>
      <?= $navLink('farmers.php', 'Farmers') ?>
      <?= $navLink('about.php', 'About') ?>
      <?= $navLink('contact.php', 'Contact') ?>
    </nav>

    <div class="flex items-center space-x-3">
      <form action="catalogue.php" method="get" class="relative hidden lg:block w-56" autocomplete="off">
        <input type="text" name="q" id="global-search" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search produce or farms..."
               class="w-full pl-9 pr-4 py-2 text-sm bg-gray-100 border border-transparent rounded-full focus:outline-none focus:border-brand-500 focus:bg-white transition">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        <div id="search-suggestions" class="hidden absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden"></div>
      </form>

      <a href="cart.php" class="relative p-2 text-gray-700 hover:text-brand-800 transition" aria-label="Cart">
        <i class="fa-solid fa-basket-shopping text-xl"></i>
        <span class="absolute -top-1 -right-1 bg-emerald-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= cart_count() ?></span>
      </a>

      <?php if ($user): ?>
        <div class="relative group">
          <button class="flex items-center gap-2 text-sm font-semibold text-brand-900">
            <span class="w-8 h-8 rounded-full bg-brand-900 text-white flex items-center justify-center text-xs"><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
            <span class="hidden sm:inline"><?= e(explode(' ', $user['name'])[0]) ?></span>
          </button>
          <div class="hidden group-hover:block absolute right-0 pt-2 w-48">
            <div class="bg-white rounded-xl shadow-xl border border-gray-100 py-2 text-sm">
              <a href="profile.php" class="block px-4 py-2 hover:bg-brand-50">My Profile</a>
              <a href="orders.php" class="block px-4 py-2 hover:bg-brand-50">My Orders</a>
              <?php if ($user['role'] === 'farmer'): ?><a href="farmer.php" class="block px-4 py-2 hover:bg-brand-50">Farmer Dashboard</a><?php endif; ?>
              <?php if ($user['role'] === 'admin'): ?><a href="admin.php" class="block px-4 py-2 hover:bg-brand-50">Admin Panel</a><?php endif; ?>
              <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Log Out</a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="px-4 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50 rounded-lg transition">Log In</a>
        <a href="register.php" class="px-4 py-2 text-sm font-semibold text-white bg-brand-900 hover:bg-brand-800 rounded-lg shadow-sm transition">Register</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php foreach (pull_flash() as $f): ?>
  <div class="flash max-w-7xl mx-auto mt-4 mx-4 px-4 py-3 rounded-xl text-sm font-medium <?= $f['type'] === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' ?>" role="alert">
    <?= e($f['message']) ?>
  </div>
<?php endforeach; ?>

<main class="flex-grow">
