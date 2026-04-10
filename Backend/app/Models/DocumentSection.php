<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSection extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'sub_title',
        'content',
        'level',
        'parent_id',
        'sort_order',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function parent()
    {
        return $this->belongsTo(DocumentSection::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentSection::class, 'parent_id')->orderBy('sort_order');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
