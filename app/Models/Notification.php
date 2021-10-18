<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notification';
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromuser(){
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function thread(){
        return $this->belongsTo(Thread::class, 'thread_id');
    }
}
