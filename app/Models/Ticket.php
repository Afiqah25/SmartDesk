<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'ai_status',
        'category',
        'priority',
        'sentiment',
        'summary',
        'language',
        'category_source',
        'priority_source',
        'reply',
    ];


    protected $attributes = [
        'status' => 'new',
        'ai_status' => 'pending',
    ];

    public function getReferenceAttribute(): string
    {
        return 'SD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}
