<?php

declare(strict_types=1);

class Product
{
    private static ?PDO $pdo = null;

    public function __construct(
        private int $id,
        private string $name,
        private array $photos,
        private int $price,
        private string $description,
        private int $quantity,
        private int $categoryId,
        private DateTime $createdAt,
        private DateTime $updatedAt
    ) {
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function findOneById(int $id): Product|false
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Connexion PDO non configurée.');
        }

        $stmt = self::$pdo->prepare('SELECT * FROM product WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        }

        return self::hydrate($row);
    }

    public static function findAll(): array
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Connexion PDO non configurée.');
        }

        $stmt = self::$pdo->query('SELECT * FROM product ORDER BY id');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];

        foreach ($rows as $row) {
            $products[] = self::hydrate($row);
        }

        return $products;
    }

    private static function hydrate(array $row): Product
    {
        return new Product(
            (int) $row['id'],
            (string) $row['name'],
            json_decode((string) $row['photos'], true, 512, JSON_THROW_ON_ERROR),
            (int) $row['price'],
            (string) $row['description'],
            (int) $row['quantity'],
            (int) $row['category_id'],
            new DateTime((string) $row['created_at']),
            new DateTime((string) $row['updated_at'])
        );
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPhotos(): array { return $this->photos; }
    public function getPrice(): int { return $this->price; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
}

$databasePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'job 03' . DIRECTORY_SEPARATOR . 'draft-shop.sqlite';
$dsn = 'sqlite:' . $databasePath;

$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

Product::setConnection($pdo);

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

$pdo->prepare(
    'INSERT OR IGNORE INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
)->execute([
    'id' => 700,
    'name' => 'Figurines',
    'description' => 'Mini personnages pour collectionneurs en herbe.',
    'created_at' => '2025-09-25T09:00:00',
    'updated_at' => '2025-10-06T15:00:00',
]);

$productRows = [
    ['id' => 7, 'name' => 'Figurine Dragon Rouge', 'photos' => json_encode(['dragon-rouge-1.jpg', 'dragon-rouge-2.jpg'], JSON_THROW_ON_ERROR), 'price' => 4599, 'description' => 'Un dragon articulé avec flammes amovibles.', 'quantity' => 3, 'category_id' => 700, 'created_at' => '2025-10-04T13:20:00', 'updated_at' => '2025-10-09T08:45:00'],
    ['id' => 8, 'name' => 'Figurine Chevalier d\'Argent', 'photos' => json_encode(['chevalier-argent.jpg'], JSON_THROW_ON_ERROR), 'price' => 3299, 'description' => 'Chevalier articulé avec bouclier et épée.', 'quantity' => 6, 'category_id' => 700, 'created_at' => '2025-10-05T09:10:00', 'updated_at' => '2025-10-08T17:25:00'],
    ['id' => 9, 'name' => 'Figurine Sorcière des Glaces', 'photos' => json_encode(['sorciere-glace.jpg'], JSON_THROW_ON_ERROR), 'price' => 2899, 'description' => 'Maîtresse des tempêtes gelées.', 'quantity' => 7, 'category_id' => 700, 'created_at' => '2025-10-06T10:00:00', 'updated_at' => '2025-10-09T09:30:00'],
];

$productStmt = $pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
);

foreach ($productRows as $row) {
    $productStmt->execute($row);
}

$allProducts = Product::findAll();
var_dump(array_map(fn (Product $p) => $p->getId(), $allProducts));
