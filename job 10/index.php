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

    public static function hydrate(array $row): Product
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

    public function update(): Product|false
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Connexion PDO non configurée.');
        }

        $stmt = self::$pdo->prepare(
            'UPDATE product SET
                name = :name,
                photos = :photos,
                price = :price,
                description = :description,
                quantity = :quantity,
                category_id = :category_id,
                created_at = :created_at,
                updated_at = :updated_at
             WHERE id = :id'
        );

        $payload = [
            'id' => $this->id,
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

        return $this;
    }

    public function getId(): int { return $this->id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setPrice(int $price): void { $this->price = $price; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
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
    'id' => 1200,
    'name' => 'Drones',
    'description' => 'Drones autonomes pour explorations.',
    'created_at' => '2025-09-18T11:00:00',
    'updated_at' => '2025-10-11T10:30:00',
]);

$pdo->prepare(
    'INSERT OR IGNORE INTO product (
        id, name, photos, price, description, quantity, category_id, created_at, updated_at
    ) VALUES (
        :id, :name, :photos, :price, :description, :quantity, :category_id, :created_at, :updated_at
    )'
)->execute([
    'id' => 150,
    'name' => 'Drone Explorer MK1',
    'photos' => json_encode(['drone-mk1-front.jpg', 'drone-mk1-top.jpg'], JSON_THROW_ON_ERROR),
    'price' => 5999,
    'description' => 'Drone autonome capable de cartographier des zones hostiles.',
    'quantity' => 10,
    'category_id' => 1200,
    'created_at' => '2025-10-01T09:00:00',
    'updated_at' => '2025-10-10T12:00:00',
]);

$row = $pdo->query('SELECT * FROM product WHERE id = 150')->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    throw new RuntimeException('Impossible de récupérer le produit 150.');
}

$product = Product::hydrate($row);

$product->setName('Drone Explorer MK1 – Edition Pro');
$product->setPrice(6599);
$product->setDescription('Version améliorée avec capteurs thermiques.');
$product->setQuantity(8);
$product->setUpdatedAt(new DateTime('now', new DateTimeZone('UTC')));

$updated = $product->update();

var_dump($updated instanceof Product);

if ($updated instanceof Product) {
    $freshRow = $pdo->query('SELECT name, price, description, quantity FROM product WHERE id = 150')->fetch(PDO::FETCH_ASSOC);
    var_dump($freshRow);
}
