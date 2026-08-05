<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'alt',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the parent imageable model.
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full URL to the image.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }

    /**
     * Get the full path on disk.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/public/'.$this->path);
    }
}
