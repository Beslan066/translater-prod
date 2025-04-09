<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateUserRoleRequest;
use App\Models\Translate;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;



use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request)
{
    $query = User::query()
        ->with(['translations.sentence'])
        ->withCount([
            'translations as approved_translations_count' => function($query) {
                $query->whereHas('sentence', fn($q) => $q->where('status', 2));
            },
            'translations as pending_translations_count' => function($query) {
                $query->whereHas('sentence', fn($q) => $q->where('status', 1));
            }
        ])
        ->select([
            'users.*',
            DB::raw('(SELECT COALESCE(SUM(sentences.price), 0) 
                    FROM translates 
                    JOIN sentences ON translates.sentence_id = sentences.id 
                    WHERE translates.user_id = users.id 
                    AND sentences.status = 2) as total_earnings')
        ]);

    // Фильтрация по роли
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    // Сортировка
    $sortField = $request->input('sort', 'created_at');
    $sortDirection = 'desc'; // Всегда по убыванию для критериев
    
    switch ($sortField) {
        case 'earnings':
            $query->orderBy('total_earnings', $sortDirection);
            break;
        case 'translated':
            $query->orderBy('approved_translations_count', $sortDirection);
            break;
        case 'on_review':
            $query->orderBy('pending_translations_count', $sortDirection);
            break;
        default:
            $query->orderBy('created_at', 'desc');
    }

    $users = $query->paginate(10);

    // Вычисляем онлайн статус
    $users->each(fn($user) => $user->is_online = $user->last_seen && now()->diffInMinutes($user->last_seen) < 5);

    return view('home.users.users', [
        'users' => $users,
        'roles' => User::getRoles(),
        'currentSort' => $sortField
    ]);
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
