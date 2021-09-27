<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use DB;

class ProfileController extends Controller
{
    public function show(Request $request){
        $param = $request->username;
        if($param){
            dd($param);
        }else{
            dd('Nothing else');
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
                    'message' => 'Avatar successfully updated!',
                    'avatar' => $request->input('avatar')
                ]);
                DB::commit();
                return response()->json([
                    'avatar' => $request->input('avatar')
                ]);
            }catch(Exception $e){
                DB::rollback();
                return response()->json(['error' => $e], 400);
            }
        }
    }
}
