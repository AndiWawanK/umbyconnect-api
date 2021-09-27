<?php

namespace App\Http\Controllers\API\Auth;
use Validator;
use DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function entry(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $fieldType = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
  
        $user = User::where([$fieldType => $request->input('email')])->first();

        if(!$user){
            return response()->json(['message' => 'No active account found with the given credentials!'], 404);
        }elseif(!Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Wrong password!'], 401);
        }

        if($user->tokens()->where('name', $request->input('email'))->first()) {
            $user->tokens()->where('tokenable_id', $user->id)->delete();
        }

        $token = $user->createToken($request->input('email'))->plainTextToken;

        return response()->json([
            'status_code' => 200,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}
