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
        match (true) {
            // For enrollments, ensure the related user has a valid account
            // and the related course is active
            $model instanceof Enrollment => $builder
                ->whereHas('user', function ($query) {
                    $query
                        ->where('validity', '>=', Carbon::now())
                        ->orWhere('validity', null);
                })
                ->whereHas('course', function ($query) {
                    $query
                        ->whereNull('deleted_at');
                }),
            // For users, ensure the related user has a valid account
            default => $builder
                ->where('validity', '>=', Carbon::now())
                ->orWhere('validity', null),
        };
    }
}
