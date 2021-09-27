<?php

namespace App\Http\Controllers\API\Auth;
use Validator;
use DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'username' => 'required|unique:users',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try{
            $user = User::create([
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make($request->input('password'))
            ]);
            DB::commit();
    
            $token = $user->createToken($request->input('email'))->plainTextToken;
            return response()->json([
                'userdata' => $user,
                'token' => $token
            ]);
        }catch(Exception $e){
            DB::rollback();
            return response()->json(['error' => $e], 400);
        }
    }
}
