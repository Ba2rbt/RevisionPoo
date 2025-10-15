<?php

declare(strict_types=1);

namespace App;

use App\Abstract\AbstractProduct;
use App\Interface\StockableInterface;
use DateTime;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

class Electronic extends AbstractProduct implements StockableInterface
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

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function getWarrantyFee(): int
    {
        return $this->warrantyFee;
    }

    public function setWarrantyFee(int $warrantyFee): void
    {
        $this->warrantyFee = $warrantyFee;
    }

    public function addStocks(int $stock): self
    {
        if ($stock < 0) {
            throw new InvalidArgumentException('Impossible d\'ajouter un stock négatif.');
        }

        $this->quantity += $stock;

        return $this;
    }

    public function removeStocks(int $stock): self
    {
        if ($stock < 0) {
            throw new InvalidArgumentException('Impossible de retirer un stock négatif.');
        }

        if ($stock > $this->quantity) {
            throw new RuntimeException('Stock insuffisant pour effectuer cette opération.');
        }

        $this->quantity -= $stock;

        return $this;
    }

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
