<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'chunk_index',
        'content',
        'page_number',
        'embedding',
        'token_count',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'chunk_index' => 'integer',
            'token_count' => 'integer',
            'embedding' => 'array',
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
