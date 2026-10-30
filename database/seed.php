<?php
declare(strict_types=1);
/**
 * Seeds demo data. Run once from the CLI:  php database/seed.php
 * Demo farmer accounts use the password "Farmer@123" – change/remove in production.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this script from the command line.');
}
require_once __DIR__ . '/../functions.php';

$pdo = db();
if ((int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() > 0) {
    exit("Database already seeded.\n");
}

$pdo->beginTransaction();

$cats = [['Vegetables','vegetables','🥦'],['Fruits','fruits','🍎'],['Dairy & Eggs','dairy-eggs','🥛'],
         ['Grains','grains','🌾'],['Honey','honey','🍯'],['Herbs','herbs','🌿']];
$st = $pdo->prepare('INSERT INTO categories (name, slug, emoji) VALUES (?,?,?)');
foreach ($cats as $c) { $st->execute($c); }
$cat = array_column($pdo->query('SELECT id, slug FROM categories')->fetchAll(), 'id', 'slug');

$farmers = [
  ['GreenAcres Farm',     'Kiambu Region',  'Greens & Leafy Vegs', '👨‍🌾', 'greenacres@example.com'],
  ['Limuru Organic Hub',  'Limuru',         'Tomatoes & Vegetables','👩‍🌾', 'limuru@example.com'],
  ['Highland Orchards',   "Murang'a Hills", 'Fruits & Avocados',   '👩‍🌾', 'highland@example.com'],
  ['Rift Valley Apiaries','Naivasha',       'Raw Organic Honey',   '🐝',   'apiaries@example.com'],
  ['Sunshine Poultry',    'Thika',          'Eggs & Poultry',      '👨‍🌾', 'sunshine@example.com'],
  ['Mwea Co-op',          'Mwea',           'Rice & Grains',       '👨‍🌾', 'mwea@example.com'],
];
$u = $pdo->prepare("INSERT INTO users (name,email,phone,password_hash,role) VALUES (?,?,?,?, 'farmer')");
$f = $pdo->prepare('INSERT INTO farmers (user_id,farm_name,location,speciality,avatar,is_verified,bio) VALUES (?,?,?,?,?,1,?)');
$hash = password_hash('Farmer@123', PASSWORD_DEFAULT);
$fid = [];
foreach ($farmers as [$farm, $loc, $spec, $avatar, $email]) {
    $u->execute([$farm, $email, '+254700000000', $hash]);
    $f->execute([(int)$pdo->lastInsertId(), $farm, $loc, $spec, $avatar, "$farm grows chemical-free produce in $loc and delivers within hours of harvest."]);
    $fid[$farm] = (int)$pdo->lastInsertId();
}

$products = [
  ['GreenAcres Farm','vegetables','Fresh Sukuma Wiki (Kale)',60,'bunch','🥬',4.9,1,'Fresh, crisp Sukuma Wiki rich in iron and calcium, harvested early morning.'],
  ['Limuru Organic Hub','vegetables','Organic Tomatoes',120,'kg','🍅',4.8,1,'Vine-ripened organic tomatoes packed with natural flavour and antioxidants.'],
  ['Highland Orchards','fruits','Sweet Hass Avocados',150,'kg','🥑',5.0,1,'Creamy Hass avocados grown in nutrient-dense highland soil.'],
  ['Rift Valley Apiaries','honey','Raw Wild Honey',550,'jar (500g)','🍯',4.9,1,'100% pure unfiltered wild forest honey.'],
  ['Sunshine Poultry','dairy-eggs','Pasture Raised Eggs',380,'tray (30s)','🥚',4.7,0,'Farm-fresh eggs from free-roaming, pasture-fed hens.'],
  ['GreenAcres Farm','vegetables','Fresh Spinach',70,'bunch','🍃',4.8,1,'Tender, dark green organic spinach harvested to order.'],
  ['Highland Orchards','fruits','Sweet Yellow Bananas',110,'bunch','🍌',4.6,0,'Naturally ripened bananas packed with energy and potassium.'],
  ['Mwea Co-op','grains','Whole Grain Brown Rice',220,'kg','🌾',4.9,1,'Unpolished aromatic brown rice from the Mwea valleys.'],
];
$p = $pdo->prepare('INSERT INTO products (farmer_id,category_id,name,price,unit,emoji,rating,is_organic,description,stock) VALUES (?,?,?,?,?,?,?,?,?,100)');
foreach ($products as [$farm, $slug, $name, $price, $unit, $emoji, $rating, $org, $desc]) {
    $p->execute([$fid[$farm], $cat[$slug], $name, $price, $unit, $emoji, $rating, $org, $desc]);
}

$pdo->exec("INSERT INTO promo_codes (code, percent_off) VALUES ('ORGANIC10', 10)");
$pdo->commit();
echo "Seed complete. Next: open setup_admin.php in the browser to create the admin account.\n";
