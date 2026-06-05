<?php

use App\Helpers\Helpers;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Number;

return [

    'aliases' => Facade::defaultAliases()->merge([
        'Helpers' => Helpers::class,
        'Number' => Number::class,
        'Redis' => Redis::class,
    ])->toArray(),

];
