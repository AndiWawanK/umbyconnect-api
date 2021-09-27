<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;
    protected $table = 'thread';
    protected $fillable = [
        'user_id',
        'topic_id',
        'title',
        'body',
        'link',
        'image',
        'tag',
        'slug'
    ];
    
}
