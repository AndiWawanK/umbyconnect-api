<?php

namespace App\Http\Controllers\API;
use DB;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Topic;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\ThreadReaction;

class ThreadController extends Controller
{
    public function show(Request $request){
        $param = $request->topic;
        $_orderBy = $request->order_by ? $request->order_by : 'desc';
        $_limit = $request->limit ? (int)$request->limit : 10;

        if($param){
            $thread = Thread::select('id', 'user_id', 'topic_id', 'title', 'link', 'body', 'image', 'total_view', 'created_at')->with(['user' => function($user){
                    $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
                }])
                ->where('topic_id', $param)
                ->orderBy('created_at', $_orderBy)
                ->withCount(['comment AS total_comment', 'reaction as total_reaction'])
                ->limit($_limit)
                ->get();
            return response()->json($thread, 200);
        }
        
        $thread = Thread::select('id', 'user_id', 'title', 'body', 'image', 'total_view', 'created_at')->with(['user' => function($user){
            $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
        }])
        ->orderBy('created_at', $_orderBy)
        ->withCount(['comment AS total_comment', 'reaction AS total_reaction'])
        ->limit($_limit)
        ->get();
        return response()->json($thread, 200);
    }

    public function showComment(Request $request){
        $currentUser = $request->user();
        $threadId = $request->threadId;
        $_limit = $request->limit ? $request->limit : 5;

        $reactionTypes = ['beer', 'love', 'raised_hands', 'clap'];

        $comments = Comment::select('id', 'user_id', 'body', 'created_at', 'updated_at')
            ->where('thread_id', $threadId)
            ->with(['user' => function($user){
                $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
            }])
            ->orderBy('created_at', 'asc')
            ->limit($_limit)
            ->get();

        foreach($comments as $comment){
            $reactions = [];
            foreach($reactionTypes as $reactionType){
                $reaction = CommentReaction::where([
                    ['type', '=', $reactionType],
                    ['comment_id', '=', $comment->id]
                ])->get();
                $userId = array_column($reaction->toArray(), 'user_id');
                if(count($reaction) > 0){
                    $react = [
                        'user_id' => $userId,
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

    public function createCommentReaction(Request $request){
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
                $userReaction = array_column($currentReaction->toArray(), 'user_id');
                return response()->json([
                    'user_id' => $userReaction,
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
                $userReaction = array_column($currentReaction->toArray(), 'user_id');
                return response()->json([
                    'user_id' => $userReaction,
                    'type' => $request->input('type'),
                    'total' => count($currentReaction)
                ], 200);
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }
    }

    public function createThread(Request $request){
        $currentUser = $request->user();
        $validator = Validator::make($request->all(), [
            'topic_id' => 'required',
            'title' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try{
            $link = null;
            if($file = $request->file('image')){
                $filePath = 'upload/'.$currentUser->username.'/thread';
                $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($file->getClientOriginalName())['filename']) . time() . '.' .$file->getClientOriginalExtension();
                $file->move(public_path($filePath), $fileName);
                $link = url($filePath) . '/' . $fileName;
            }
            $createThread = Thread::create([
                'user_id' => $currentUser->id,
                'topic_id' => $request->input('topic_id'),
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'link' => $request->input('link'),
                'image' => $link,
                'total_view' => 0
            ]);
            DB::commit();
            $thread = Thread::select('id', 'user_id', 'topic_id', 'title', 'link', 'body', 'image', 'total_view', 'created_at')->with(['user' => function($user){
                $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
            }, 'reaction'])
                ->where([
                    ['topic_id', '=', $request->input('topic_id')],
                    ['id', '=', $createThread->id]
                ])
                ->withCount('comment AS total_comment')
                ->first();
            return response()->json($thread, 201);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }

    }

    public function createThreadReaction(Request $request){
        $currentUser = $request->user();
        $threadId = $request->threadId;

        $hasReaction = ThreadReaction::where([
            ['user_id', '=', $currentUser->id],
            ['thread_id', '=', $threadId]
        ])->exists();

        DB::beginTransaction();
        try{

            if($hasReaction){
                $updateReaction = ThreadReaction::where([
                    ['user_id', '=', $currentUser->id],
                    ['thread_id', '=', $threadId]
                ])->update([
                    'thread_id' => $threadId,
                    'user_id' => $currentUser->id,
                    'type' => $request->input('type')
                ]);
                DB::commit();
                $currentReaction = ThreadReaction::where([
                    ['thread_id', '=', $threadId]
                ])->get();
                return response()->json([
                    'type' => $request->input('type'),
                    'total' => count($currentReaction)
                ], 200);
            }else{
                $createReaction = ThreadReaction::create([
                    'thread_id' => $threadId,
                    'user_id' => $currentUser->id,
                    'type' => $request->input('type')
                ]);
                DB::commit();
                $currentReaction = ThreadReaction::where([
                    ['thread_id', '=', $threadId]
                ])->get();
                return response()->json([
                    'type' => $request->input('type'),
                    'total' => count($currentReaction)
                ], 200);
            }

        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error', $e], 400);
        }

    }

    public function countThreadView(Request $request){
        $currentThread = Thread::where('id', $request->threadId)->first();
        $currentView = $currentThread->total_view === null ? 0 : $currentThread->total_view;
        DB::beginTransaction();
        try{
            $updateView = Thread::where('id', $request->threadId)->update([
                'total_view' => $currentView + 1
            ]);
            DB::commit();
            $currentThread = Thread::where('id', $request->threadId)->first();
            return response()->json([
                'action' => 'Updating total view.',
                'current_view' => $currentThread->total_view
            ]);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }
    }
}
