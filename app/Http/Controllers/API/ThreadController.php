<?php

namespace App\Http\Controllers\API;
use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\CommentReaction;

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

    public function showComment(Request $request){
        $threadId = $request->threadId;
        $_limit = $request->limit ? $request->limit : 5;

        $reactionTypes = ['beer', 'love', 'raised_hands', 'clap'];

        $comments = Comment::select('id', 'user_id', 'body', 'created_at', 'updated_at')
            ->where('thread_id', $threadId)
            ->with(['user' => function($user){
                $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
            }])
            ->limit($_limit)
            ->get();

        foreach($comments as $comment){
            $reactions = [];
            foreach($reactionTypes as $reactionType){
                $reaction = CommentReaction::where([
                    ['type', '=', $reactionType],
                    ['comment_id', '=', $comment->id]
                ])->get();

                if(count($reaction) > 0){
                    $react = [
                        'type' => $reactionType,
                        'total' => count($reaction)
                    ];
                    array_push($reactions, $react);
                }
            }
            
            $comment->reaction = $reactions;
        }
        return response()->json($comments);
    }

    public function createComment(Request $request){
        $currentUser = $request->user();

        $threadId = $request->threadId;
        
        DB::beginTransaction();
        try{
            $storeComment = Comment::create([
                'user_id' => $currentUser->id,
                'thread_id' => $threadId,
                'body' => $request->input('comment')
            ]);
            DB::commit();
            $comment = Comment::select('id', 'user_id', 'body', 'created_at', 'updated_at')
                ->where('id', $storeComment->id)
                ->with(['reaction', 'user' => function($user){
                    $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
                }])
                ->first();
            return response()->json($comment);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }
    }

    public function createReaction(Request $request){
        $currentUser = $request->user();
        $threadId = $request->threadId;
        $commentId = $request->commentId;

        $hasReaction = CommentReaction::where([
            ['user_id', '=', $currentUser->id],
            ['comment_id', '=', $commentId]
        ])->exists();


        DB::beginTransaction();
        try{
            if($hasReaction){
                $updateReaction = CommentReaction::where([
                    ['user_id', '=', $currentUser->id],
                    ['comment_id', '=', $commentId]
                ])->update([
                    'comment_id' => $commentId,
                    'user_id' => $currentUser->id,
                    'type' => $request->input('type')
                ]);
                DB::commit();
                $currentReaction = CommentReaction::where([
                    ['type', '=', $request->input('type')],
                    ['comment_id', '=', $commentId]
                ])->get();
                return response()->json([
                    'type' => $request->input('type'),
                    'total' => count($currentReaction)
                ], 200);
            }else{
                $createReaction = CommentReaction::create([
                    'comment_id' => $commentId,
                    'user_id' => $currentUser->id,
                    'type' => $request->input('type')
                ]);
                DB::commit();
                $currentReaction = CommentReaction::where([
                    ['type', '=', $request->input('type')],
                    ['comment_id', '=', $commentId]
                ])->get();
                return response()->json([
                    'type' => $request->input('type'),
                    'total' => count($currentReaction)
                ], 200);
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }
    }
}
