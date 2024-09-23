<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAPIController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository){
        $this -> userRepository = $userRepository;
    }

    public function index(){
        try {
            $user = $this -> userRepository -> all();
            return response() -> json($user);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }

    public function show($id){
        try{
            $user = $this -> userRepository -> findById($id);
            return response() -> json($user);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }

    public function store (Request $request){
        try{
            $this -> userRepository -> create($request);
            return response()->json(['message' => 'User created successfully'],200);
        } catch (ValidationException $e){
            return response()->json(['errors' => $e->errors()], 422);
        }
        catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }


    public function update ($id , Request $request){
        try{
            $user = $this -> userRepository -> update($id , $request);
            return response() -> json(['message' => 'User updated successfully' , 'user' => $user],200);
        }
        catch (ValidationException $e){
            return response()->json(['errors' => $e->errors()], 422);
        }
        catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }

    public function destroy ($id){
        try{
            $this -> userRepository -> delete($id);
            return response() -> json(['message' => 'User deleted successfully'],200);
        }catch(\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }


    public function getUserRoleCount()
    {
        try {
            // Use Eloquent aggregation methods
            $roleCounts = User::select('role')
                ->groupBy('role')
                ->withCount('role as count') // Count the number of users for each role
                ->get();

            return response()->json($roleCounts);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

}
