<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('created_at', 'desc')->get();
        $roles = User::getRoles();



        return view('home.users.users', compact('users', 'roles'));
    }

    public function edit(User $user)
    {

        $roles = User::getRoles();

        return view('home.users.edit', [
            'user' => $user,
            'roles' => $roles
        ]);

    }


    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        $data = $request->validated();

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Роль пользователя успешно обновлена.');
    }

    public function deleteUser(User $user)
    {
        $user->delete();

        return redirect()->route('users.index');
    }
}
