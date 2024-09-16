<?php 
namespace App\Repositories;


use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;


class UserRepository implements UserRepositoryInterface {
    protected $user;

    public function __construct (User $user){
        $this -> user = $user;
    }   
    private function allBuilder() : QueryBuilder 
    {
        return QueryBuilder::for(User::class)
        ->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('role'),
            AllowedFilter::callback('search' , function (Builder $query , $value){
                $query -> where(function ($query) use ($value){
                    $query -> where ('name' , 'LIKE' , "%{$value}%")
                           -> orWhere('phone_number' , 'LIKE' , "%{$value}%")
                           -> orWhere ('email' , 'LIKE' , "%{$value}%")
                           -> orWhere('role' , 'LIKE' , "%{$value}%");
                });
            })
        ])
        -> allowedSorts('created_at' , 'updated_at' , 'role')
        -> defaultSort('-created_at');
    }

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->paginate(10);
    }

    public function findById(int $id): User
    {
        return User::findOrFail($id);
    }

    public function create(Request $request): User
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data = $request->except('profile_picture');

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $fileName, 'public');
            $data['profile_picture'] = $path;
        }
        $user = new User($data);
        $user->save();

        return $user;
    }

    public function update(int $id, Request $request): User
    {
        $user = User::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $file = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $file;
        }

        $user->update($data);
        return $user;
    }


    public function delete(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        $user->delete();
    }

}