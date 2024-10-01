<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileSettingAPIController extends Controller
{
    public function updateProfileInfo(Request $request)
    {
        try {
            $user = auth()->user();

            $request -> validate([
                    'name' => 'required|string',
                    'email' => 'required|email',
                    'phone_number' => 'required|string',
            ]);
    
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);
    
            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function uploadProfilePicture(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);
    
            $profilePicture = $request->file('profile_picture');
    
            $path = $profilePicture->store('profile_pictures', 'public');
    
            $user->profile_picture = $path;
            $user->save();
    
            return response()->json([
                'message' => 'Profile picture uploaded successfully',
                'data' => $user
            ]);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function changePassword(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8',
                'password_confirmation' => 'required|string|same:new_password'
            ]);
    
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'The provided current password is incorrect.'
                ], 422);
            }
    
            $user->password = Hash::make($request->new_password);
            $user->save();
    
            return response()->json([
                'message' => 'Password changed successfully'
            ]);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }
}
