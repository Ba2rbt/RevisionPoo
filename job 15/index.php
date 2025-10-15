<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Abstract\AbstractProduct;
use App\Clothing;
use App\Electronic;

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
    'id' => 5000,
    'name' => 'Vêtements de survie',
    'description' => 'Tenues adaptées aux environnements extrêmes.',
    'created_at' => '2025-10-15T09:00:00',
    'updated_at' => '2025-10-15T09:10:00',
]);

$pdo->prepare(
    'INSERT INTO category (id, name, description, created_at, updated_at)
     VALUES (:id, :name, :description, :created_at, :updated_at)'
)->execute([
    'id' => 5001,
    'name' => 'Technologies d\'exploration',
    'description' => 'Dispositifs scientifiques et outils de communication.',
    'created_at' => '2025-10-15T10:15:00',
    'updated_at' => '2025-10-15T10:20:00',
]);

$clothing = new Clothing(
    null,
    'Combinaison Ionis',
    ['combinaison-ionis-front.jpg', 'combinaison-ionis-back.jpg'],
    24999,
    'Combinaison isolante pour exploration lunaire.',
    8,
    5000,
    new DateTime('2025-10-15T12:00:00+00:00'),
    new DateTime('2025-10-15T12:00:00+00:00'),
    'L',
    'Argent',
    'Combinaison',
    3499
);

$electronic = new Electronic(
    null,
    'Analyseur Quanta-XL',
    ['analyseur-quanta-xl.jpg'],
    119999,
    'Analyseur de terrain multi-spectre.',
    5,
    5001,
    new DateTime('2025-10-15T12:45:00+00:00'),
    new DateTime('2025-10-15T12:45:00+00:00'),
    'NovaLabs',
    15999
);

var_dump('create clothing', $clothing->create() instanceof Clothing);
var_dump('create electronic', $electronic->create() instanceof Electronic);

$clothing->removeStocks(2);
$clothing->addStocks(6);
$electronic->removeStocks(1);
$electronic->addStocks(3);

var_dump('clothing quantity', $clothing->getQuantity());
var_dump('electronic quantity', $electronic->getQuantity());

$clothing->setUpdatedAt(new DateTime('now', new DateTimeZone('UTC')));
$electronic->setUpdatedAt(new DateTime('now', new DateTimeZone('UTC')));

$clothing->update();
$electronic->update();

$foundClothing = Clothing::findOneById($clothing->getId());
$foundElectronic = Electronic::findOneById($electronic->getId());

var_dump('clothing quantity in db', $foundClothing?->getQuantity());
var_dump('electronic quantity in db', $foundElectronic?->getQuantity());
