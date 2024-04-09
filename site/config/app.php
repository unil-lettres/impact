<?php

use Illuminate\Support\Facades\Facade;

return [

    'aliases' => Facade::defaultAliases()->merge([
        'Helpers' => App\Helpers\Helpers::class,
        'Number' => Illuminate\Support\Number::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
