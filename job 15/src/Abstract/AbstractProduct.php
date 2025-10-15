<?php

declare(strict_types=1);

namespace App\Abstract;

use DateTime;
use PDO;
use RuntimeException;

abstract class AbstractProduct
{
    protected static ?PDO $pdo = null;

    public function __construct(
        protected ?int $id,
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function setPhotos(array $photos): void
    {
        $this->photos = $photos;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    abstract public static function findOneById(int $id): static|false;

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
