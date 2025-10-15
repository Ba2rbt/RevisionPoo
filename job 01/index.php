<?php

class Product
{
    private int $id;
    private string $name;
    private array $photos;
    private int $price;
    private string $description;
    private int $quantity;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $id,
        string $name,
        array $photos,
        int $price,
        string $description,
        int $quantity,
        DateTime $createdAt,
        DateTime $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->photos = $photos;
        $this->price = $price;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPhotos(): array { return $this->photos; }
    public function getPrice(): int { return $this->price; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setPhotos(array $photos): void { $this->photos = $photos; }
    public function setPrice(int $price): void { $this->price = $price; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
}

$product = new Product(
    1,
    'Peluche Licorne',
    ['photo1.jpg', 'photo2.jpg'],
    2499,
    'Une peluche toute douce et magique.',
    5,
    new DateTime('2025-10-01'),
    new DateTime('2025-10-08')
);

var_dump($product->getName());
var_dump($product->getPrice());

$product->setQuantity(10);
var_dump($product->getQuantity());

var_dump($product);
