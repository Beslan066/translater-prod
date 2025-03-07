<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;


use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        // Получение пользователей с их переводами и предложениями со статусом = 2
        $users = User::query()
            ->with(['translations.sentence' => function ($query) {
                $query->where('status', 2); // Учитываем только предложения со статусом = 2
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Список ролей пользователей
        $roles = User::getRoles();

        // Расчет заработков пользователей
        $users = $users->map(function ($user) {
            $user->total_earnings = $user->translations
                ->map(function ($translation) {
                    return $translation->sentence ? $translation->sentence->price : 0;
                })
                ->sum();

            return $user;
        });

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

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Данные пользователя обновлены.');
    }

    public function deleteUser(User $user)
    {
        $user->delete();

        return redirect()->route('users.index');
    }
}
