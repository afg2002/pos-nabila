<?php

namespace App\Models;

class WarehouseStock extends \App\ProductWarehouseStock
{
    public function getQuantityAttribute()
    {
        return (int) ($this->stock_on_hand ?? 0);
    }
}