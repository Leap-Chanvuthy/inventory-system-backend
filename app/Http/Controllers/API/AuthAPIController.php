<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthAPIController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request-> role,
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'user' => $user ,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ]
            ], 201);
        }catch (ValidationException $e){
            return response () -> json(['errors' => $e -> errors()],400);
        } catch (\Exception $e){
            return response () -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $credentials = $request->only('email', 'password');

            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response()->json([
                    'errors' => [
                        'email' => ['The email is invalid.']
                    ]
                ], 404);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'errors' => [
                        'password' => ['The password is incorrect.']
                    ]
                ], 401);
            }

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = Auth::user();

            $userDetails = $user-> makeHidden('password');

            return response()->json([
                'user' => $userDetails,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ]
            ], 201);
        }catch (ValidationException $e){
            return response () -> json(['errors' => $e -> errors()],400);
        } catch (\Exception $e){
            return response () -> json(['error' => $e -> getMessage()],400);
        }
    }




    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

}
