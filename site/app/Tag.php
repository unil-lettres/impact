<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'course_id'];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The cards that belong to the tag.
     */
    public function cards(): BelongsToMany
    {
        return $this
            ->belongsToMany(Card::class)
            ->withPivot([
                'created_at AS pivot_created_at',
                'id AS pivot_id',
            ])
            ->withTimestamps()
            ->orderBy('pivot_created_at')

            // Can't use fraction (microseconds) for timestamp. If the timestamp
            // is the same (can happen with migrated data), we rely on the id
            // instead.
            ->orderBy('pivot_id');
    }

    /**
     * Get the course that owns the tag.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
