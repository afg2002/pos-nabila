<?php

namespace Tests\Unit;

class MockStockMovementPolicy
{
    public function delete($user, $stockMovement)
    {
        return true;
    }

    public function update($user, $stockMovement)
    {
        return true;
    }

    public function view($user, $stockMovement)
    {
        return true;
    }
}