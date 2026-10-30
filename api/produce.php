<?php
declare(strict_types=1);
require_once __DIR__ . '/../functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $rows  = search_products([
        'q'         => trim((string)($_GET['q'] ?? '')),
        'category'  => (string)($_GET['category'] ?? ''),
        'max_price' => $_GET['max_price'] ?? '',
        'organic'   => !empty($_GET['organic']),
        'sort'      => (string)($_GET['sort'] ?? ''),
    ], $limit);

    $data = array_map(static fn(array $p) => [
        'id'       => (int)$p['id'],
        'name'     => $p['name'],
        'category' => $p['category'],
        'farm'     => $p['farm_name'],
        'price'    => (float)$p['price'],
        'unit'     => $p['unit'],
        'rating'   => (float)$p['rating'],
        'organic'  => (bool)$p['is_organic'],
        'stock'    => (int)$p['stock'],
        'emoji'    => $p['emoji'],
    ], $rows);

    echo json_encode(['count' => count($data), 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
