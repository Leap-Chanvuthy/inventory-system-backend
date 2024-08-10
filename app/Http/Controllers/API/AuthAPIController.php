<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthAPIController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user ,
            'authorisation' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $credentials = $request->only('email', 'password');
    
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    
        $user = Auth::user();

        $userDetails = $user->makeHidden('password');

        return response()->json([
            'user' => $userDetails,
            'authorisation' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ], 201);
    }    

    public function changePassword (Request $request){
        $validator = Validator::make($request -> all(),[
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator -> fails()){
            return response()->json(['error' => $validator -> errors()] , 422);
        }

        $user = Auth::user();

        if (!Hash::check($request -> current_password , $user -> password)){
            return response() ->json(['error' => 'Current password is not correct'], 401);
        }

        $user -> password = Hash::make($request -> new_password);
        $user->save();

        return response() -> json(['message' => 'Password updated successfully'],200);
    }


    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
