<?php

declare(strict_types=1);

class Product
{
    private static ?PDO $pdo = null;

    public function __construct(
        private int $id,
        private string $name,
        /** @var array<int, string> */
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
            throw new RuntimeException('Connexion PDO non configurée pour Product.');
        }

        $stmt = self::$pdo->prepare('SELECT * FROM category WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $this->categoryId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data === false) {
            throw new RuntimeException("Catégorie {$this->categoryId} introuvable.");
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

class Category
{
    private static ?PDO $pdo = null;

    public function __construct(
        private int $id,
        private string $name,
        private string $description,
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
    public function getDescription(): string { return $this->description; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    /**
     * @return array<int, Product>
     */
    public function getProducts(): array
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Connexion PDO non configurée pour Category.');
        }

        $stmt = self::$pdo->prepare('SELECT * FROM product WHERE category_id = :category ORDER BY id');
        $stmt->execute(['category' => $this->id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];

        foreach ($rows as $row) {
            $products[] = new Product(
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

        return $products;
    }
}

$databasePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'job 03' . DIRECTORY_SEPARATOR . 'draft-shop.sqlite';
$dsn = 'sqlite:' . $databasePath;

$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

Product::setConnection($pdo);
Category::setConnection($pdo);

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

$categoryData = [
    ['id' => 700, 'name' => 'Figurines', 'description' => 'Mini personnages pour collectionneurs en herbe.', 'created_at' => '2025-09-25T09:00:00', 'updated_at' => '2025-10-06T15:00:00'],
    ['id' => 800, 'name' => 'Jeux de société', 'description' => 'Des jeux pour toute la famille.', 'created_at' => '2025-09-28T10:15:00', 'updated_at' => '2025-10-07T11:30:00'],
];

$categoryStmt = $pdo->prepare(
    'INSERT OR IGNORE INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
);

foreach ($categoryData as $categoryRow) {
    $categoryStmt->execute($categoryRow);
}

$productData = [
    ['id' => 7, 'name' => 'Figurine Dragon Rouge', 'photos' => json_encode(['dragon-rouge-1.jpg', 'dragon-rouge-2.jpg'], JSON_THROW_ON_ERROR), 'price' => 4599, 'description' => 'Un dragon articulé avec flammes amovibles.', 'quantity' => 3, 'category_id' => 700, 'created_at' => '2025-10-04T13:20:00', 'updated_at' => '2025-10-09T08:45:00'],
    ['id' => 8, 'name' => 'Figurine Chevalier d\'Argent', 'photos' => json_encode(['chevalier-argent.jpg'], JSON_THROW_ON_ERROR), 'price' => 3299, 'description' => 'Chevalier articulé avec bouclier et épée.', 'quantity' => 6, 'category_id' => 700, 'created_at' => '2025-10-05T09:10:00', 'updated_at' => '2025-10-08T17:25:00'],
    ['id' => 15, 'name' => 'Jeu de stratégie Galaxia', 'photos' => json_encode(['galaxia-box.jpg'], JSON_THROW_ON_ERROR), 'price' => 5499, 'description' => 'Conquérez la galaxie entre amis.', 'quantity' => 4, 'category_id' => 800, 'created_at' => '2025-10-03T15:45:00', 'updated_at' => '2025-10-09T09:00:00'],
];

$productStmt = $pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
);

foreach ($productData as $productRow) {
    $productStmt->execute($productRow);
}

$categoryRow = $pdo->query('SELECT * FROM category WHERE id = 700')->fetch(PDO::FETCH_ASSOC);

if ($categoryRow === false) {
    throw new RuntimeException('Catégorie 700 introuvable.');
}

$category = new Category(
    (int) $categoryRow['id'],
    (string) $categoryRow['name'],
    (string) $categoryRow['description'],
    new DateTime((string) $categoryRow['created_at']),
    new DateTime((string) $categoryRow['updated_at'])
);

$products = $category->getProducts();

var_dump($category);
var_dump($products);
