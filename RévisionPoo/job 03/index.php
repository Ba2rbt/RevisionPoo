<?php

declare(strict_types=1);

$databasePath = __DIR__ . DIRECTORY_SEPARATOR . 'shop.sqlite';
$dsn = 'sqlite:' . $databasePath;

$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS category (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        description TEXT NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS product (
        id INTEGER PRIMARY KEY,
        name TEXT NOT NULL,
        photos TEXT NOT NULL,
        price INTEGER NOT NULL,
        description TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        category_id INTEGER NOT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE CASCADE ON UPDATE CASCADE
    )'
);

$categories = [
    [
        'id' => 100,
        'name' => 'Peluches',
        'description' => 'Tout ce qui est tout doux et qu\'on peut câliner.',
        'created_at' => '2025-09-15T10:00:00',
        'updated_at' => '2025-10-05T09:30:00',
    ],
    [
        'id' => 200,
        'name' => 'Jeux éducatifs',
        'description' => 'Apprendre en s\'amusant avec des jeux malins.',
        'created_at' => '2025-09-20T11:15:00',
        'updated_at' => '2025-10-02T14:45:00',
    ],
];

$categoryStmt = $pdo->prepare(
    'INSERT OR IGNORE INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
);

foreach ($categories as $category) {
    $categoryStmt->execute($category);
}

$products = [
    [
        'id' => 1,
        'name' => 'Peluche Licorne',
        'photos' => json_encode(['photo1.jpg', 'photo2.jpg'], JSON_THROW_ON_ERROR),
        'price' => 2499,
        'description' => 'Une peluche toute douce et magique.',
        'quantity' => 5,
        'category_id' => 100,
        'created_at' => '2025-10-01T08:00:00',
        'updated_at' => '2025-10-08T08:00:00',
    ],
    [
        'id' => 2,
        'name' => 'Kit de chimie junior',
        'photos' => json_encode(['kit1.jpg'], JSON_THROW_ON_ERROR),
        'price' => 3499,
        'description' => 'Des expériences amusantes et sûres pour apprendre la chimie.',
        'quantity' => 8,
        'category_id' => 200,
        'created_at' => '2025-10-03T10:30:00',
        'updated_at' => '2025-10-07T12:00:00',
    ],
];

$productStmt = $pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
);

foreach ($products as $product) {
    $productStmt->execute($product);
}

$categoryRows = $pdo->query('SELECT * FROM category ORDER BY id')->fetchAll();
$productRows = $pdo->query('SELECT * FROM product ORDER BY id')->fetchAll();

echo "Catégories enregistrées :\n";
foreach ($categoryRows as $row) {
    print_r($row);
}

echo "\nProduits enregistrés :\n";
foreach ($productRows as $row) {
    $row['photos'] = json_decode($row['photos'], true, 512, JSON_THROW_ON_ERROR);
    print_r($row);
}

echo "\nLa base de données se trouve ici : {$databasePath}\n";
