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
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reaction(){
        return $this->hasMany(ThreadReaction::class);
    }

    public function comment(){
        return $this->hasMany(Comment::class);
    }

    public function topic(){
        return $this->belongsTo(Topic::class, 'topic_id')->select('id', 'name', 'icon');
    }
    
}
