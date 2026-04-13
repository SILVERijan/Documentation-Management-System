<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'sub_title',
        'slug',
        'content',
        'excerpt',
        'application_id',
        'user_id',
        'updated_by',
        'status',
        'version',
        'view_count',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * The application this document belongs to.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * The user who created this document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who last updated this document.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Attachments belonging to this document.
     */
    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class);
    }


    /**
     * Sections within this document (sub-title + content blocks).
     */
    public function sections()
    {
        return $this->hasMany(DocumentSection::class)->orderBy('sort_order');
    }
}
