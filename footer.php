</main>

<footer class="bg-brand-900 text-white pt-16 pb-10 mt-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-brand-800">
      <div class="space-y-4">
        <div class="flex items-center space-x-2 text-brand-300 font-bold text-xl"><i class="fa-solid fa-leaf"></i><span><?= APP_NAME ?></span></div>
        <p class="text-emerald-100 text-xs leading-relaxed">Connecting organic farmers directly with conscious consumers. Fresh, fair and sustainable produce at your doorstep.</p>
      </div>
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-brand-300 mb-4">Navigation</h4>
        <ul class="space-y-2 text-xs text-emerald-100">
          <li><a href="index.php" class="hover:underline">Home</a></li>
          <li><a href="catalogue.php" class="hover:underline">Produce Catalogue</a></li>
          <li><a href="farmers.php" class="hover:underline">Meet Farmers</a></li>
          <li><a href="about.php" class="hover:underline">About Us</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-brand-300 mb-4">Support</h4>
        <ul class="space-y-2 text-xs text-emerald-100">
          <li><a href="contact.php" class="hover:underline">Contact Us</a></li>
          <li><a href="register.php" class="hover:underline">Become a Farmer Partner</a></li>
          <li><a href="orders.php" class="hover:underline">Track Orders</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-xs font-bold uppercase tracking-wider text-brand-300 mb-4">Weekly Harvest Newsletter</h4>
        <form action="contact.php" method="get" class="flex gap-2">
          <input type="email" name="email" placeholder="Your email..." class="w-full px-3 py-2 text-xs bg-brand-800 text-white rounded-xl placeholder-emerald-300 focus:outline-none">
          <button class="px-4 py-2 bg-brand-500 hover:bg-brand-700 text-white text-xs font-bold rounded-xl">Join</button>
        </form>
      </div>
    </div>
    <p class="mt-8 text-center text-xs text-emerald-300">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved. Good food starts with good farms.</p>
  </div>
</footer>

<script src="assets/app.js"></script>
</body>
</html>
