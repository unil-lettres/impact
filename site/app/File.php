<?php

namespace App;

use App\Scopes\HideAttachmentsScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'filename',
        'status',
        'progress',
        'type',
        'size',
        'width',
        'height',
        'length',
        'course_id',
        'card_id',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new HideAttachmentsScope());
    }

    /**
     * Get the course of this file.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the card of this file (attachment).
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Get the cards of this file (regular).
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if the file is used by card(s). It could be used
     * as a regular file or as an attachment.
     */
    public function isUsed(): bool
    {
        return $this->cards()->exists() || $this->card()->exists();
    }

    /**
     * Check if the file is an attachment
     */
    public function isAttachment(): bool
    {
        return ! is_null($this->card_id);
    }
}
