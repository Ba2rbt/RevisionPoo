<?php

declare(strict_types=1);

namespace App\Interface;

interface StockableInterface
{
    public function addStocks(int $stock): self;

    public function removeStocks(int $stock): self;
}
