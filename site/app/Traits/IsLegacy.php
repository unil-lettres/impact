<?php

namespace App\Traits;

trait IsLegacy
{
    /**
     * Check if the entity is legacy.
     */
    public function isLegacy(): bool
    {
        return $this->legacy_id !== null;
    }
}
