<?php

declare(strict_types=1);

class Product
{
    private static ?PDO $pdo = null;

    public function __construct(
        private ?int $id,
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

    public function create(): Product|false
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Connexion PDO non configurée.');
        }

        if ($this->id !== null) {
            return $this;
        }

        $stmt = self::$pdo->prepare(
            'INSERT INTO product (
                name, photos, price, description, quantity, category_id, created_at, updated_at
            ) VALUES (
                :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
            )'
        );

        $payload = [
            'name' => $this->name,
            'photos' => json_encode($this->photos, JSON_THROW_ON_ERROR),
            'price' => $this->price,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'category_id' => $this->categoryId,
            'created_at' => $this->createdAt->format(DateTime::ATOM),
            'updated_at' => $this->updatedAt->format(DateTime::ATOM),
        ];

        $success = $stmt->execute($payload);

        if ($success === false) {
            return false;
        }

        $newId = self::$pdo->lastInsertId();

        if ($newId === false) {
            return false;
        }

        $this->id = (int) $newId;

        return $this;
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

    public function getId(): int
    {
        if ($this->id === null) {
            throw new RuntimeException('Identifiant non défini.');
        }

        return $this->id;
    }

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
    'id' => 1100,
    'name' => 'Vaisseaux',
    'description' => 'Navettes interstellaires miniatures.',
    'created_at' => '2025-09-22T08:30:00',
    'updated_at' => '2025-10-10T12:15:00',
]);

$newProduct = new Product(
    null,
    'Navette Hyperion',
    ['hyperion-front.jpg', 'hyperion-cockpit.jpg'],
    9999,
    'Navette de transport rapide avec hyperpropulsion.',
    2,
    1100,
    new DateTime('now', new DateTimeZone('UTC')),
    new DateTime('now', new DateTimeZone('UTC'))
);

$createdProduct = $newProduct->create();

var_dump($createdProduct instanceof Product);

if ($createdProduct instanceof Product) {
    $fromDatabase = Product::findOneById($createdProduct->getId());
    var_dump($fromDatabase);
}