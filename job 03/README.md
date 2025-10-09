# Job 03 – Base de données `draft-shop`

Ce job illustre la mise en place d'un modèle simple « catégorie ↔ produit » dans une base SQLite.

## Schéma conceptuel

```
Category (1) ──── (N) Product
```

- Chaque produit appartient à une catégorie via `category_id`.
- Une catégorie peut contenir plusieurs produits.

## Tables créées

- `category` : identifiant, nom, description, dates de création et de mise à jour.
- `product` : identifiant, nom, liste de photos (JSON), prix en centimes, description, quantité, `category_id`, dates de création et de mise à jour.

## Lancer le script

```bash
cd "c:/Users/dell/Documents/GitHub/RévisionPoo/job 03"
php index.php
```

Le script :

1. crée la base `draft-shop.sqlite` (SQLite) si elle n'existe pas,
2. crée les tables `category` et `product` avec la bonne relation,
3. insère des exemples de catégories et produits (sans dupliquer si ré-exécution),
4. affiche les enregistrements insérés et indique l'emplacement du fichier SQLite.
