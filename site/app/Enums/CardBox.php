<?php

namespace App\Enums;

use Illuminate\Support\Collection;

final class CardBox
{
    const Box1 = 'box1';

    const Box2 = 'box2';

    const Box3 = 'box3';

    const Box4 = 'box4';

    const Box5 = 'box5';

    public static function getAllBoxes(): Collection
    {
        return collect([
            static::Box1,
            static::Box2,
            static::Box3,
            static::Box4,
            static::Box5,
        ]);
    }
}
