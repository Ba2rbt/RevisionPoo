<?php

declare(strict_types=1);

class Product
{
    protected static ?PDO $pdo = null;

    public function __construct(
        protected int $id,
        protected string $name,
        protected array $photos,
        protected int $price,
        protected string $description,
        protected int $quantity,
        protected int $categoryId,
        protected DateTime $createdAt,
        protected DateTime $updatedAt
    ) {
    }

    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
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

class Clothing extends Product
{
    public function __construct(
        int $id,
        string $name,
        array $photos,
        int $price,
        string $description,
        int $quantity,
        int $categoryId,
        DateTime $createdAt,
        DateTime $updatedAt,
        private string $size,
        private string $color,
        private string $type,
        private int $materialFee
    ) {
        parent::__construct($id, $name, $photos, $price, $description, $quantity, $categoryId, $createdAt, $updatedAt);
    }

    public function getSize(): string { return $this->size; }
    public function getColor(): string { return $this->color; }
    public function getType(): string { return $this->type; }
    public function getMaterialFee(): int { return $this->materialFee; }
}

class Electronic extends Product
{
    public function __construct(
        int $id,
        string $name,
        array $photos,
        int $price,
        string $description,
        int $quantity,
        int $categoryId,
        DateTime $createdAt,
        DateTime $updatedAt,
        private string $brand,
        private int $warrantyFee
    ) {
        parent::__construct($id, $name, $photos, $price, $description, $quantity, $categoryId, $createdAt, $updatedAt);
    }

    public function getBrand(): string { return $this->brand; }
    public function getWarrantyFee(): int { return $this->warrantyFee; }
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

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS clothing (
        product_id INTEGER PRIMARY KEY,
        size TEXT NOT NULL,
        color TEXT NOT NULL,
        type TEXT NOT NULL,
        material_fee INTEGER NOT NULL,
        FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
    )'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS electronic (
        product_id INTEGER PRIMARY KEY,
        brand TEXT NOT NULL,
        warranty_fee INTEGER NOT NULL,
        FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
    )'
);

$pdo->prepare(
    'INSERT OR IGNORE INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
)->execute([
    'id' => 1300,
    'name' => 'Vêtements',
    'description' => 'Tout pour se vêtir avec style.',
    'created_at' => '2025-09-10T11:00:00',
    'updated_at' => '2025-10-11T14:45:00',
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
)->execute([
    'id' => 1400,
    'name' => 'Électronique',
    'description' => 'Gadgets et appareils high-tech.',
    'created_at' => '2025-09-12T09:15:00',
    'updated_at' => '2025-10-12T10:20:00',
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
)->execute([
    'id' => 501,
    'name' => 'Cape Sélénite',
    'photos' => json_encode(['cape-front.jpg', 'cape-back.jpg'], JSON_THROW_ON_ERROR),
    'price' => 12999,
    'description' => 'Cape enchantée offrant un éclat lunaire.',
    'quantity' => 12,
    'category_id' => 1300,
    'created_at' => '2025-10-05T08:00:00',
    'updated_at' => '2025-10-11T18:30:00',
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO clothing (product_id, size, color, type, material_fee)
     VALUES (:product_id, :size, :color, :type, :material_fee)'
)->execute([
    'product_id' => 501,
    'size' => 'M',
    'color' => 'Argenté',
    'type' => 'Cape',
    'material_fee' => 1999,
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
)->execute([
    'id' => 601,
    'name' => 'Bracelet Communicant Orion',
    'photos' => json_encode(['bracelet-orion.jpg'], JSON_THROW_ON_ERROR),
    'price' => 45999,
    'description' => 'Bracelet intelligent pour communiquer avec les constellations.',
    'quantity' => 5,
    'category_id' => 1400,
    'created_at' => '2025-10-06T07:40:00',
    'updated_at' => '2025-10-13T09:55:00',
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO electronic (product_id, brand, warranty_fee)
     VALUES (:product_id, :brand, :warranty_fee)'
)->execute([
    'product_id' => 601,
    'brand' => 'StarLink Labs',
    'warranty_fee' => 5999,
]);

$clothingRow = $pdo->query(
    'SELECT p.*, c.size, c.color, c.type, c.material_fee
     FROM product p
     JOIN clothing c ON c.product_id = p.id
     WHERE p.id = 501'
)->fetch(PDO::FETCH_ASSOC);

$electronicRow = $pdo->query(
    'SELECT p.*, e.brand, e.warranty_fee
     FROM product p
     JOIN electronic e ON e.product_id = p.id
     WHERE p.id = 601'
)->fetch(PDO::FETCH_ASSOC);

$clothing = new Clothing(
    (int) $clothingRow['id'],
    (string) $clothingRow['name'],
    json_decode((string) $clothingRow['photos'], true, 512, JSON_THROW_ON_ERROR),
    (int) $clothingRow['price'],
    (string) $clothingRow['description'],
    (int) $clothingRow['quantity'],
    (int) $clothingRow['category_id'],
    new DateTime((string) $clothingRow['created_at']),
    new DateTime((string) $clothingRow['updated_at']),
    (string) $clothingRow['size'],
    (string) $clothingRow['color'],
    (string) $clothingRow['type'],
    (int) $clothingRow['material_fee']
);

$electronic = new Electronic(
    (int) $electronicRow['id'],
    (string) $electronicRow['name'],
    json_decode((string) $electronicRow['photos'], true, 512, JSON_THROW_ON_ERROR),
    (int) $electronicRow['price'],
    (string) $electronicRow['description'],
    (int) $electronicRow['quantity'],
    (int) $electronicRow['category_id'],
    new DateTime((string) $electronicRow['created_at']),
    new DateTime((string) $electronicRow['updated_at']),
    (string) $electronicRow['brand'],
    (int) $electronicRow['warranty_fee']
);

var_dump($clothing);
var_dump($electronic);
