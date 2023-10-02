<?php

namespace App;

use App\Enums\StoragePath;
use App\Scopes\HideAttachmentsScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
     * Get the cards of this file (regular).
     */
    public function cards(): HasMany
    {
        return $this->hasMany('App\Card', 'file_id')
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

    /**
     * Clone a file and return it.
     *
     * @param  string  $prefix Prefix to add to the filename
     */
    public function clone($prefix = ''): ?File
    {
        // Clean filename to keep only the name of the file
        $cleanedFilename = pathinfo($this->filename, PATHINFO_BASENAME);
        $copiedFilename = substr($prefix.$cleanedFilename, 0, 99);

        $success = Storage::disk('public')->copy(
            StoragePath::UploadStandard.'/'.$cleanedFilename,
            StoragePath::UploadStandard.'/'.$copiedFilename,
        );

        if (! $success) {
            return null;
        }

        $file = $this->replicate()->fill(['filename' => $copiedFilename]);

        $file->save();

        return $file;
    }
}
