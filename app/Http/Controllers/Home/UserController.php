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
    public function index(Request $request)
    {
        // Основной запрос для получения пользователей
        $users = User::query()
            ->with(['translations.sentence' => function ($query) {
                $query->where('status', 2); // Загружаем только утвержденные переводы (status=2)
            }])
            ->withCount([
                // Счетчик подтвержденных переводов (status=2)
                'translations as translations_status2_count' => function ($query) {
                    $query->whereHas('sentence', function ($q) {
                        $q->where('status', 2);
                    });
                },
                // Счетчик переводов на проверке (status=1)
                'translations as translations_status1_count' => function ($query) {
                    $query->whereHas('sentence', function ($q) {
                        $q->where('status', 1);
                    });
                }
            ])
            // Фильтрация по роли, если указана
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            // Сортировка в зависимости от выбранного параметра
            ->when($request->filled('sort'), function ($query) use ($request) {
                switch ($request->sort) {
                    case 'earnings':
                        // Сортировка по заработку будет применена после загрузки
                        break;
                    case 'translated':
                        $query->orderBy('translations_status2_count', $request->get('order', 'desc'));
                        break;
                    case 'on_review':
                        $query->orderBy('translations_status1_count', $request->get('order', 'desc'));
                        break;
                    case 'online':
                        $query->orderByRaw('last_seen > NOW() - INTERVAL 5 MINUTE DESC');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            }, function ($query) {
                $query->orderBy('created_at', 'desc'); // Сортировка по умолчанию
            })
            ->paginate(10);
    
        // Добавляем дополнительные вычисляемые поля
        $users->each(function ($user) {
            // Проверка онлайн-статуса (был онлайн менее 5 минут назад)
            $user->is_online = $user->last_seen && now()->diffInMinutes($user->last_seen) < 5;
            
            // Расчет общего заработка
            $user->total_earnings = $user->translations->sum(function ($translation) {
                return $translation->sentence->price ?? 0;
            });
        });
    
        // Дополнительная сортировка по заработку (после загрузки данных)
        if ($request->get('sort') === 'earnings') {
            $order = $request->get('order', 'desc');
            $sorted = $order === 'asc' 
                ? $users->getCollection()->sortBy('total_earnings')
                : $users->getCollection()->sortByDesc('total_earnings');
            
            $users->setCollection($sorted);
        }
    
        $roles = User::getRoles(); // Получаем все возможные роли
    
        return view('home.users.users', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->all() // Передаем параметры фильтрации в представление
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
