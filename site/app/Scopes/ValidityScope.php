<?php

namespace App\Scopes;

use App\Enrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Carbon;

class ValidityScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Ensure the user has a valid account
        match (true) {
            $model instanceof Enrollment => $builder
                ->whereHas('user', function ($query) {
                    $query->where('validity', '>=', now())
                        ->orWhere('validity', null);
                }),
            default => $builder // User::class
                ->where('validity', '>=', Carbon::now())
                ->orWhere('validity', null),
        };
    }
}
