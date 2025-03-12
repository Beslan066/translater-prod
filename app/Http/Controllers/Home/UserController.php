<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;



use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with(['translations.sentence' => function ($query) {
                $query->where('status', 2);
            }])
            ->withCount([
                'translations as translations_status2_count' => function ($query) {
                    $query->whereHas('sentence', function ($q) {
                        $q->where('status', 2);
                    });
                },
                'translations as translations_status1_count' => function ($query) {
                    $query->whereHas('sentence', function ($q) {
                        $q->where('status', 1);
                    });
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $roles = User::getRoles();

        // Добавляем проверку онлайн-статуса
        $users->each(function ($user) {
            $user->is_online = $user->last_seen
                && now()->diffInMinutes($user->last_seen) < 5;

            $user->total_earnings = $user->translations->sum(function ($translation) {
                return $translation->sentence->price ?? 0;
            });
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

    public function export()
    {
        $users = User::query()
            ->where('role', 3) // Фильтр по статусу
            ->with(['translations.sentence'])
            ->withCount([
                'translations as translations_status2_count' => function ($query) {
                    $query->whereHas('sentence', function ($q) {
                        $q->where('status', 2);
                    });
                },
                'translations as translations_status1_count' => function ($query) {
                    $query->whereHas('sentence', function ($q) {
                        $q->where('status', 1);
                    });
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Остальной код остается без изменений
        $users->each(function ($user) {
            $user->is_online = $user->last_seen && now()->diffInMinutes($user->last_seen) < 5;
            $user->total_earnings = $user->translations->sum(function ($translation) {
                return $translation->sentence->price ?? 0;
            });
        });

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_status3_' . date('Y-m-d') . '.csv"',
        ];

        return new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'Онлайн',
                'Имя',
                'Зарегистрирован',
                'Роль',
                'Заработано',
                'Переведено',
                'На проверке'
            ], ';');

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->is_online ? 'Online' : 'Offline',
                    $user->name,
                    $user->created_at,
                    \App\Models\User::getRoleName($user->role),
                    $user->total_earnings,
                    $user->translations_status2_count,
                    $user->translations_status1_count
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
