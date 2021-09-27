<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;

class ThreadController extends Controller
{
    public function show(Request $request){
        $thread = Thread::get();
        return response()->json($thread);
    }
}
