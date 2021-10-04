<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Models\User;
use App\Models\Thread;
use App\Models\Followers;

class ProfileController extends Controller
{
    public function show(Request $request){
        $param = $request->username 
            ? $request->username 
            : $request->user()->username;

        $userId = User::where('username', $param)->first();

        $isFollow = Followers::where([
            ['follower_id', '=', $request->user()->id],
            ['followed_id', '=', $userId->id] 
        ])->exists();

        $user = User::withCount(['followers AS followers', 'following AS following', 'thread AS thread_total'])
                    ->where('username', $param)
                    ->first();
        $user->isFollow = $isFollow;
        return response()->json($user);
    }

    public function showThread(Request $request){
        $param  = $request->user_id;
        $_limit = $request->limit ? (int)$request->limit : 10;
        if($param){
            $thread = Thread::select('id', 'user_id', 'title', 'body', 'image', 'created_at')->with(['user' => function($user){
                    $user->select('id', 'full_name', 'username', 'avatar', 'major', 'year_class');
                }, 'reaction'])
                ->orderBy('created_at', 'desc')
                ->withCount('comment AS total_comment')
                ->limit($_limit)
                ->where('user_id', '=', $param)
                ->get();

            return response()->json($thread, 200);
        }
    }

    public function showFollowers(Request $request){
        $param = $request->user_id;
        if($param){
            $followers = Followers::with('followers')->where('follower_id', $param)->get();
            $results = array_column($followers->toArray(), 'followers');
            return response()->json($results);
        }
    }
    public function showFollowing(Request $request){
        $param = $request->user_id;
        if($param){
            $following = Followers::with('following')->where('followed_id', $param)->get();
            $results = array_column($following->toArray(), 'following');
            return response()->json($results);
        }
    }

    public function follow(Request $request){
        $currentUserId = $request->user();
        $userId = $request->userId;

        $isFollow = Followers::where([
            ['follower_id', '=', $currentUserId->id],
            ['followed_id', '=', $userId] 
        ])->exists();

        DB::beginTransaction();
        try{
            if($isFollow){
                $unfollow = Followers::where([
                    ['follower_id', '=', $currentUserId->id],
                    ['followed_id', '=', $userId] 
                ])->delete();
                DB::commit();
                return response()->json(['isFollow' => false], 200);
            }else{
                $follow = Followers::create([
                    'follower_id' => $currentUserId->id,
                    'followed_id' => $userId
                ]);
                DB::commit();
                return response()->json(['isFollow' => true], 200);
            }
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }

    }

    public function setAvatar(Request $request){ 
        $user = $request->user();
        
        if($file = $request->file('avatar')){
            $dirName = 'upload/'.$user->username.'/avatar';
            $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($file->getClientOriginalName())['filename']) . time() . '.' .$file->getClientOriginalExtension();
            $file->move(public_path($dirName), $fileName);
            $link = url($dirName) . '/' . $fileName;
            DB::beginTransaction();
            try{
                User::where('id', $user->id)->update([
                    'avatar' => $link
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Avatar successfully updated!',
                    'avatar' => $link
                ]);
            }catch(Exception $e){
                DB::rollback();
                return response()->json(['error' => $e], 400);
            }

        }else{
            DB::beginTransaction();
            try{
                User::where('id', $user->id)->update([
                    'avatar' => $request->input('avatar')
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Avatar successfully updated!',
                    'avatar' => $request->input('avatar')
                ]);
            }catch(Exception $e){
                DB::rollback();
                return response()->json(['error' => $e], 400);
            }
        }
    }
}
