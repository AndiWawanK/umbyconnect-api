<?php

namespace App\Http\Controllers\API;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Topic;
use App\Models\Comment;

class ThreadController extends Controller
{
    public function show(Request $request){
        $param = $request->topic;
        $_orderBy = $request->order_by ? $request->order_by : 'desc';
        $_limit = $request->limit ? (int)$request->limit : 10;

        if($param){
            $thread = Thread::select('id', 'user_id', 'topic_id', 'title', 'body', 'image', 'created_at')->with(['user' => function($user){
                    $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
                }, 'reaction'])
                ->where('topic_id', $param)
                ->orderBy('created_at', $_orderBy)
                ->withCount('comment AS total_comment')
                ->limit($_limit)
                ->get();
            return response()->json($thread, 200);
        }
        
        $thread = Thread::select('id', 'user_id', 'title', 'body', 'image', 'created_at')->with(['user' => function($user){
            $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
        }, 'reaction'])
        ->orderBy('created_at', $_orderBy)
        ->withCount('comment AS total_comment')
        ->limit($_limit)
        ->get();
        return response()->json($thread, 200);
    }

    public function comment(Request $request){
        $threadId = $request->threadId;
        $_limit = $request->limit ? $request->limit : 5;

        $thread = Comment::select('id', 'user_id', 'body', 'created_at', 'updated_at')
            ->where('thread_id', $threadId)
            ->with(['reaction', 'user' => function($user){
                $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
            }])
            ->limit($_limit)
            ->get();
        return response()->json($thread);
    }
}
