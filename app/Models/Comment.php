<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comment';
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reaction(){
        return $this->hasMany(CommentReaction::class, 'id')->select('id', 'type');;
    }
}
