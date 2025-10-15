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
            throw new RuntimeException('Connexion PDO manquante.');
        }

        $stmt = self::$pdo->prepare('SELECT * FROM product WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        }

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
    'id' => 900,
    'name' => 'Robots futuristes',
    'description' => 'Modèles expérimentaux pour missions spéciales.',
    'created_at' => '2025-09-30T08:00:00',
    'updated_at' => '2025-10-08T18:00:00',
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
)->execute([
    'id' => 99,
    'name' => 'Prototype Robot',
    'photos' => json_encode(['robot-front.jpg', 'robot-back.jpg'], JSON_THROW_ON_ERROR),
    'price' => 7999,
    'description' => 'Robot multifonction pour missions spatiales.',
    'quantity' => 2,
    'category_id' => 900,
    'created_at' => '2025-10-05T10:00:00',
    'updated_at' => '2025-10-09T16:30:00',
]);

$result = Product::findOneById(99);
var_dump($result instanceof Product);

$resultMissing = Product::findOneById(123456);
var_dump($resultMissing);
