<?php

// Imagine que nous créons une fiche pour un jouet dans un magasin.
// Une classe, c'est comme un plan pour fabriquer cette fiche.
class Product
{
    // On garde nos informations précieuses dans des "boîtes" privées.
    private int $id;            // Identifiant unique du produit
    private string $name;       // Nom du produit
    private array $photos;      // Liste des chemins de photos
    private int $price;         // Prix en centimes (pour éviter les virgules)
    private string $description;// Description du produit
    private int $quantity;      // Quantité disponible
    private DateTime $createdAt;// Date de création
    private DateTime $updatedAt;// Date de dernière mise à jour

    // Le constructeur est comme un chef qui remplit toutes les boîtes dès le début.
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

    // Les getters sont comme des fenêtres pour regarder dans les boîtes.
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getPhotos(): array { return $this->photos; }
    public function getPrice(): int { return $this->price; }
    public function getDescription(): string { return $this->description; }
    public function getQuantity(): int { return $this->quantity; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    // Les setters sont comme des petites portes pour remplacer ce qu'il y a dans les boîtes.
    public function setId(int $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setPhotos(array $photos): void { $this->photos = $photos; }
    public function setPrice(int $price): void { $this->price = $price; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setQuantity(int $quantity): void { $this->quantity = $quantity; }
    public function setCreatedAt(DateTime $createdAt): void { $this->createdAt = $createdAt; }
    public function setUpdatedAt(DateTime $updatedAt): void { $this->updatedAt = $updatedAt; }
}

// Maintenant, on crée un vrai produit en utilisant notre plan de fabrication.
$product = new Product(
    1,
    'Peluche Licorne',
    ['photo1.jpg', 'photo2.jpg'],
    2499, // 24,99 € si on convertit en euros
    'Une peluche toute douce et magique.',
    5,
    new DateTime('2025-10-01'),
    new DateTime('2025-10-08')
);

// On regarde certaines informations avec les getters.
var_dump($product->getName());
var_dump($product->getPrice());

// On modifie la quantité comme si on remplissait le rayon.
$product->setQuantity(10);
var_dump($product->getQuantity());

// On peut aussi vérifier tout l'objet pour voir sa structure complète.
var_dump($product);
