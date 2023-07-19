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

    // TODO faire les trucs de mass assignment / casts
    // TODO completer la classe

    protected $fillable = [ 'name' ];

    /**
     * The cards that belong to the tag.
     */
    public function cards(): BelongsToMany
    {
        return $this->belongsToMany(Card::class);
    }

    /**
     * Get the course that owns the tag.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
