<?php

namespace App;

use App\Scopes\HideAttachmentsScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'name', 'filename', 'status', 'type', 'size', 'width', 'height', 'length', 'course_id', 'card_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

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
    public function course(): HasOne
    {
        return $this->hasOne('App\Course', 'id', 'course_id');
    }

    /**
     * Get the card of this file (attachment).
     */
    public function card(): HasOne
    {
        return $this->hasOne('App\Card', 'id', 'card_id');
    }

    /**
     * Get the cards of this file.
     */
    public function cards(): HasMany
    {
        return $this->hasMany('App\Card', 'file_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if the file is used by card(s). It could be used
     * as the box 1 media or as an attachment.
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
