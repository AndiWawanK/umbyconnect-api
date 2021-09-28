<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreadReaction extends Model
{
    use HasFactory;
    protected $table = 'thread_reaction';
    protected $guarded = [];

    public function thread(){
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
