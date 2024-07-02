<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Hossam\Licht\Controllers\LichtBaseController;
use Illuminate\Support\Facades\Hash;

class UserController extends LichtBaseController
{

    public function index()
    {
        $users = User::all();
        $users = UserResource::collection($users);
        return view('users', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {
        $validData = $request->validated();
        $validData['password'] = Hash::make($request->validated('password'));
        $user = User::create($validData);
        return redirect()->route('users.index');
    }

    public function show(User $user)
    {
        return $this->successResponse(UserResource::make($user));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validData = $request->validated();

        if (!empty($validData['password'])) {
            $validData['password'] = Hash::make($validData['password']);
        } else {
            unset($validData['password']);
        }

        $user->update($validData);

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
    }
}
