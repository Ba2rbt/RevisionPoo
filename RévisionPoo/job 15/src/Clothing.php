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

class Clothing extends AbstractProduct implements StockableInterface
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

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMaterialFee(): int
    {
        return $this->materialFee;
    }

    public function setMaterialFee(int $materialFee): void
    {
        $this->materialFee = $materialFee;
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
