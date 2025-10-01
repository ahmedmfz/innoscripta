<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'raw'          => 'array',
        'is_active'    => 'boolean',
    ];

    public function source(): BelongsTo {
         return $this->belongsTo(Source::class);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'article_author');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }
}
