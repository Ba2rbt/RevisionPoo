<?php

// Nouvelle fiche pour nos catégories dans la boutique.
class Category
{
    private int $id;
    private string $name;
    private string $description;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $id,
        string $name,
        string $description,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
}

// On remet notre fiche Produit, avec un lien vers une catégorie.
class Product
{
    private int $id;
    private string $name;
    private array $photos;
    private int $price;
    private string $description;
    private int $quantity;
    private int $categoryId;   // Nouvel identifiant de catégorie
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $id,
        string $name,
        array $photos,
        int $price,
        string $description,
        int $quantity,
        int $categoryId,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->photos = $photos;
        $this->price = $price;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->categoryId = $categoryId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }

    public function getPhotos(): array { return $this->photos; }
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
}

// Exemple d'utilisation pour vérifier le bon fonctionnement.
$category = new Category(
    100,
    'Peluches',
    'Tout ce qui est tout doux et qu\'on peut câliner.',
    new DateTime('2025-09-15'),
    new DateTime('2025-10-05')
);

$product = new Product(
    1,
    'Peluche Licorne',
    ['photo1.jpg', 'photo2.jpg'],
    2499,
    'Une peluche toute douce et magique.',
    5,
    $category->getId(),
    new DateTime('2025-10-01'),
    new DateTime('2025-10-08')
);

var_dump($category->getName());
var_dump($product->getCategoryId());
$product->setCategoryId(200);
var_dump($product->getCategoryId());
var_dump($product);
