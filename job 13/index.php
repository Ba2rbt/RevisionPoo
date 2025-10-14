<?php

declare(strict_types=1);

abstract class AbstractProduct
{
    protected static ?PDO $pdo = null;

    public function __construct(
        protected ?int $id,
        protected string $name,
        /** @var array<int, string> */
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

    protected static function requireConnection(): PDO
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Connexion PDO non configurée.');
        }

        return self::$pdo;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new RuntimeException('Identifiant non défini pour ce produit.');
        }

        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    /** @return array<int, string> */
    public function getPhotos(): array { return $this->photos; }
    /** @param array<int, string> $photos */
    public function setPhotos(array $photos): void { $this->photos = $photos; }

    public function getPrice(): int { return $this->price; }
    public function setPrice(int $price): void { $this->price = $price; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }

    public function getCategoryId(): int { return $this->categoryId; }
    public function setCategoryId(int $categoryId): void { $this->categoryId = $categoryId; }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }

    abstract public static function findOneById(int $id): static|false;

    /**
     * @return array<int, static>
     */
    abstract public static function findAll(): array;

    abstract public function create(): static|false;

    abstract public function update(): static|false;

    protected function baseInsert(): int|false
    {
        $pdo = self::requireConnection();

        $stmt = $pdo->prepare(
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

        if ($stmt->execute($payload) === false) {
            return false;
        }

        $id = $pdo->lastInsertId();

        if ($id === false) {
            return false;
        }

        $this->id = (int) $id;

        return $this->id;
    }

    protected function baseUpdate(): bool
    {
        $pdo = self::requireConnection();

        if ($this->id === null) {
            throw new RuntimeException('Impossible de mettre à jour un produit sans identifiant.');
        }

        $stmt = $pdo->prepare(
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

        return $stmt->execute($payload) !== false;
    }
}

class Clothing extends AbstractProduct
{
    public function __construct(
        ?int $id,
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
    public function setSize(string $size): void { $this->size = $size; }

    public function getColor(): string { return $this->color; }
    public function setColor(string $color): void { $this->color = $color; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = $type; }

    public function getMaterialFee(): int { return $this->materialFee; }
    public function setMaterialFee(int $materialFee): void { $this->materialFee = $materialFee; }

    private static function hydrate(array $row): static
    {
        return new static(
            (int) $row['id'],
            (string) $row['name'],
            json_decode((string) $row['photos'], true, 512, JSON_THROW_ON_ERROR),
            (int) $row['price'],
            (string) $row['description'],
            (int) $row['quantity'],
            (int) $row['category_id'],
            new DateTime((string) $row['created_at']),
            new DateTime((string) $row['updated_at']),
            (string) $row['size'],
            (string) $row['color'],
            (string) $row['type'],
            (int) $row['material_fee']
        );
    }

    public static function findOneById(int $id): static|false
    {
        $pdo = self::requireConnection();

        $stmt = $pdo->prepare(
            'SELECT p.*, c.size, c.color, c.type, c.material_fee
             FROM product p
             JOIN clothing c ON c.product_id = p.id
             WHERE p.id = :id'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        }

        return self::hydrate($row);
    }

    /**
     * @return array<int, static>
     */
    public static function findAll(): array
    {
        $pdo = self::requireConnection();

        $rows = $pdo->query(
            'SELECT p.*, c.size, c.color, c.type, c.material_fee
             FROM product p
             JOIN clothing c ON c.product_id = p.id
             ORDER BY p.id'
        )->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row): static => self::hydrate($row), $rows);
    }

    public function create(): static|false
    {
        if ($this->id !== null) {
            return $this;
        }

        $pdo = self::requireConnection();

        try {
            $pdo->beginTransaction();

            $id = $this->baseInsert();

            if ($id === false) {
                $pdo->rollBack();
                return false;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO clothing (product_id, size, color, type, material_fee)
                 VALUES (:product_id, :size, :color, :type, :material_fee)'
            );

            $payload = [
                'product_id' => $this->id,
                'size' => $this->size,
                'color' => $this->color,
                'type' => $this->type,
                'material_fee' => $this->materialFee,
            ];

            if ($stmt->execute($payload) === false) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();

            return $this;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }

    public function update(): static|false
    {
        if ($this->id === null) {
            throw new RuntimeException('Impossible de mettre à jour un vêtement sans identifiant.');
        }

        $pdo = self::requireConnection();

        try {
            $pdo->beginTransaction();

            if ($this->baseUpdate() === false) {
                $pdo->rollBack();
                return false;
            }

            $stmt = $pdo->prepare(
                'UPDATE clothing SET
                    size = :size,
                    color = :color,
                    type = :type,
                    material_fee = :material_fee
                 WHERE product_id = :product_id'
            );

            $payload = [
                'product_id' => $this->id,
                'size' => $this->size,
                'color' => $this->color,
                'type' => $this->type,
                'material_fee' => $this->materialFee,
            ];

            if ($stmt->execute($payload) === false) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();

            return $this;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }
}

class Electronic extends AbstractProduct
{
    public function __construct(
        ?int $id,
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
    public function setBrand(string $brand): void { $this->brand = $brand; }

    public function getWarrantyFee(): int { return $this->warrantyFee; }
    public function setWarrantyFee(int $warrantyFee): void { $this->warrantyFee = $warrantyFee; }

    private static function hydrate(array $row): static
    {
        return new static(
            (int) $row['id'],
            (string) $row['name'],
            json_decode((string) $row['photos'], true, 512, JSON_THROW_ON_ERROR),
            (int) $row['price'],
            (string) $row['description'],
            (int) $row['quantity'],
            (int) $row['category_id'],
            new DateTime((string) $row['created_at']),
            new DateTime((string) $row['updated_at']),
            (string) $row['brand'],
            (int) $row['warranty_fee']
        );
    }

    public static function findOneById(int $id): static|false
    {
        $pdo = self::requireConnection();

        $stmt = $pdo->prepare(
            'SELECT p.*, e.brand, e.warranty_fee
             FROM product p
             JOIN electronic e ON e.product_id = p.id
             WHERE p.id = :id'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        }

        return self::hydrate($row);
    }

    /**
     * @return array<int, static>
     */
    public static function findAll(): array
    {
        $pdo = self::requireConnection();

        $rows = $pdo->query(
            'SELECT p.*, e.brand, e.warranty_fee
             FROM product p
             JOIN electronic e ON e.product_id = p.id
             ORDER BY p.id'
        )->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row): static => self::hydrate($row), $rows);
    }

    public function create(): static|false
    {
        if ($this->id !== null) {
            return $this;
        }

        $pdo = self::requireConnection();

        try {
            $pdo->beginTransaction();

            $id = $this->baseInsert();

            if ($id === false) {
                $pdo->rollBack();
                return false;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO electronic (product_id, brand, warranty_fee)
                 VALUES (:product_id, :brand, :warranty_fee)'
            );

            $payload = [
                'product_id' => $this->id,
                'brand' => $this->brand,
                'warranty_fee' => $this->warrantyFee,
            ];

            if ($stmt->execute($payload) === false) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();

            return $this;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }

    public function update(): static|false
    {
        if ($this->id === null) {
            throw new RuntimeException('Impossible de mettre à jour un produit électronique sans identifiant.');
        }

        $pdo = self::requireConnection();

        try {
            $pdo->beginTransaction();

            if ($this->baseUpdate() === false) {
                $pdo->rollBack();
                return false;
            }

            $stmt = $pdo->prepare(
                'UPDATE electronic SET
                    brand = :brand,
                    warranty_fee = :warranty_fee
                 WHERE product_id = :product_id'
            );

            $payload = [
                'product_id' => $this->id,
                'brand' => $this->brand,
                'warranty_fee' => $this->warrantyFee,
            ];

            if ($stmt->execute($payload) === false) {
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();

            return $this;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return false;
        }
    }
}

$databasePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'job 03' . DIRECTORY_SEPARATOR . 'draft-shop.sqlite';
$dsn = 'sqlite:' . $databasePath;

$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

AbstractProduct::setConnection($pdo);

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

$pdo->exec('DELETE FROM clothing');
$pdo->exec('DELETE FROM electronic');
$pdo->exec('DELETE FROM product');
$pdo->exec('DELETE FROM category');

$pdo->prepare(
    'INSERT INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
)->execute([
    'id' => 3000,
    'name' => 'Habits Cosmiques',
    'description' => 'Vêtements haute technologie pour voyages spatiaux.',
    'created_at' => '2025-08-30T10:00:00',
    'updated_at' => '2025-10-14T08:30:00',
]);

$pdo->prepare(
    'INSERT INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
)->execute([
    'id' => 3001,
    'name' => 'Électronique Avancée',
    'description' => 'Outils de communication et d\'exploration futuristes.',
    'created_at' => '2025-09-05T11:45:00',
    'updated_at' => '2025-10-14T09:50:00',
]);

$clothing = new Clothing(
    null,
    'Combinaison Nova',
    ['combinaison-nova-front.jpg', 'combinaison-nova-back.jpg'],
    19999,
    'Combinaison isolante avec affichage tête-haute intégré.',
    6,
    3000,
    new DateTime('2025-10-14T11:00:00+00:00'),
    new DateTime('2025-10-14T11:00:00+00:00'),
    'M',
    'Noir sidéral',
    'Combinaison',
    3499
);

$electronic = new Electronic(
    null,
    'Console AstroNav 2',
    ['astronav2-console.jpg'],
    129999,
    'Console de navigation interplanétaire avec IA embarquée.',
    2,
    3001,
    new DateTime('2025-10-14T11:30:00+00:00'),
    new DateTime('2025-10-14T11:30:00+00:00'),
    'Nebula Systems',
    11999
);

var_dump('create clothing', $clothing->create() instanceof Clothing);
var_dump('create electronic', $electronic->create() instanceof Electronic);

$clothing->setPrice(20999);
$clothing->setMaterialFee(3799);
$clothing->setUpdatedAt(new DateTime('now', new DateTimeZone('UTC')));

$electronic->setWarrantyFee(12999);
$electronic->setQuantity(1);
$electronic->setUpdatedAt(new DateTime('now', new DateTimeZone('UTC')));

var_dump('update clothing', $clothing->update() instanceof Clothing);
var_dump('update electronic', $electronic->update() instanceof Electronic);

$foundClothing = Clothing::findOneById($clothing->getId());
$foundElectronic = Electronic::findOneById($electronic->getId());

$allClothing = Clothing::findAll();
$allElectronic = Electronic::findAll();

var_dump('found clothing class', $foundClothing instanceof Clothing);
var_dump('found electronic class', $foundElectronic instanceof Electronic);
var_dump('all clothing count', count($allClothing));
var_dump('all electronic count', count($allElectronic));
