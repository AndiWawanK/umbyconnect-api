<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Topic;

class ThreadController extends Controller
{
    public function show(Request $request){
        $param = $request->topic;
        $_orderBy = $request->order_by ? $request->order_by : 'desc';
        if($param){
            $thread = Topic::where('id', $param)->with('thread', function($thread) use ($_orderBy){
                $thread->orderBy('created_at', $_orderBy);
            })->get();
            return response()->json($thread);
        }
        
        // TODO
        // Create relation of reaction
        $thread = Thread::with(['user' => function($user){
            $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
        }])->get();
        return response()->json($thread);
    }
}
