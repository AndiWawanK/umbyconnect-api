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

        // TODO 
        // ADD PAGINATION
            // - list thread
            // - list comment

        $param = $request->topic;
        $_orderBy = $request->order_by ? $request->order_by : 'desc';
        if($param){
            $thread = Thread::select('id', 'user_id', 'topic_id', 'title', 'body', 'image', 'created_at')->with(['user' => function($user){
                    $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
                }, 'reaction'])
                ->where('topic_id', $param)
                ->orderBy('created_at', $_orderBy)
                ->withCount('comment AS total_comment')
                ->get();
            return response()->json($thread);
        }
        
        $thread = Thread::select('id', 'user_id', 'title', 'body', 'image', 'created_at')->with(['user' => function($user){
            $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
        }, 'reaction'])
        ->orderBy('created_at', $_orderBy)
        ->withCount('comment AS total_comment')
        ->get();
        return response()->json($thread);
    }

    public function comment(Request $request){
        $threadId = $request->threadId;
        
        $thread = Comment::select('id', 'user_id', 'body', 'created_at', 'updated_at')
            ->where('thread_id', $threadId)
            ->with(['reaction', 'user' => function($user){
                $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
            }])
            ->get();
        return response()->json($thread);
    }
}
