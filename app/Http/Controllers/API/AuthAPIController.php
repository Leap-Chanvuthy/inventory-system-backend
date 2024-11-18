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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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


    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);
    
            $otp = rand(100000, 999999);
    
            $user = User::where('email', $request->email)->first();
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);
    
            Mail::raw("Your password reset OTP is: $otp", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Password Reset OTP');
            });
    
            return response()->json(['message' => 'OTP sent to your email.'], 200);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
    }


    public function reset(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|numeric',
                'password' => 'required|min:6|confirmed',
            ]);
        
            $user = User::where('otp', $request->otp)->first();
        
            if (!$user) {
                return response()->json([
                    'errors' => [
                        'otp' => ['The OTP is not valid.']
                    ]
                ], 401);
            }
        
            if ($user->otp_expires_at && now()->isAfter($user->otp_expires_at)) {
                return response()->json([
                    'errors' => [
                        'otp' => ['The OTP is expired.']
                    ]
                ], 401);
            }
        
            $user->forceFill([
                'password' => Hash::make($request->password),
                'otp' => null,
                'otp_expires_at' => null,
            ])->save();
        
            return response()->json(['message' => 'Password reset successful.'], 200);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
    }

}
