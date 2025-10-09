<?php

declare(strict_types=1);

class Category
{
    public function __construct(
        private int $id,
        private string $name,
        private string $description,
        private DateTime $createdAt,
        private DateTime $updatedAt
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
}

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

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    /** @return array<int, string> */
    public function getPhotos(): array { return $this->photos; }
    public function getPrice(): int { return $this->price; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    public function getCategory(): Category
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Aucune connexion PDO disponible pour Product.');
        }

        $stmt = self::$pdo->prepare('SELECT * FROM category WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $this->categoryId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new RuntimeException("Catégorie {$this->categoryId} introuvable dans la base.");
        }

        return new Category(
            (int) $data['id'],
            (string) $data['name'],
            (string) $data['description'],
            new DateTime((string) $data['created_at']),
            new DateTime((string) $data['updated_at'])
        );
    }
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

$pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
)->execute([
    'id' => 7,
    'name' => 'Figurine Dragon Rouge',
    'photos' => json_encode(['dragon-rouge-1.jpg', 'dragon-rouge-2.jpg'], JSON_THROW_ON_ERROR),
    'price' => 4599,
    'description' => 'Un dragon articulé avec flammes amovibles.',
    'quantity' => 3,
    'category_id' => 700,
    'created_at' => '2025-10-04T13:20:00',
    'updated_at' => '2025-10-09T08:45:00',
]);

$stmt = $pdo->prepare('SELECT * FROM product WHERE id = :id');
$stmt->execute(['id' => 7]);
$productData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($productData === false) {
    throw new RuntimeException("Aucun produit avec l'id 7 trouvé.");
}

$product = new Product(
    (int) $productData['id'],
    (string) $productData['name'],
    json_decode((string) $productData['photos'], true, 512, JSON_THROW_ON_ERROR),
    (int) $productData['price'],
    (string) $productData['description'],
    (int) $productData['quantity'],
    (int) $productData['category_id'],
    new DateTime((string) $productData['created_at']),
    new DateTime((string) $productData['updated_at'])
);

$category = $product->getCategory();

var_dump($productData);
var_dump($product);
var_dump($category);
