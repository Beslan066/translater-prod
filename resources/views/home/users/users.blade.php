@extends('layouts.home')

@section('content')
    @if(isset($users))
        <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
            <div class="flex justify-between items-center">
                <h3 class="mb-8 px-6">Всего пользователей {{$users->total()}}:</h3>
                @if(auth()->user()->role === 1)
                    <div class="mb-4 mr-6 flex items-center space-x-4">
                        <!-- Кнопка фильтра -->
                        <div class="relative">
                            <button id="filterButton" class="text-gray-700 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100">
                            <img src="{{asset('img/icons/filter.svg')}}" alt="">
                            </button>
                            
                            <!-- Выпадающее меню фильтрации -->
                            <div id="filterDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10">
                                <form method="GET" action="{{ route('users.index') }}" class="p-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Роль</label>
                                        <select name="role" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                            <option value="">Все роли</option>
                                            @foreach($roles as $id => $name)
                                                <option value="{{ $id }}" {{ request('role') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Сортировка</label>
                                        <select name="sort" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                                            <option value="">По умолчанию</option>
                                            <option value="earnings" {{ request('sort') == 'earnings' ? 'selected' : '' }}>Заработок</option>
                                            <option value="translated" {{ request('sort') == 'translated' ? 'selected' : '' }}>Переведено</option>
                                            <option value="on_review" {{ request('sort') == 'on_review' ? 'selected' : '' }}>На проверке</option>
                                        </select>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <button type="submit" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                                            Применить
                                        </button>
                                        <a href="{{ route('users.index') }}" class="flex-1 bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 text-center">
                                            Сбросить
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Иконка CSV -->
                        <a href="{{ route('users.export') }}" class="text-gray-700 hover:text-gray-900 rounded-full hover:bg-gray-100" title="Экспорт в CSV">
                            <img src="{{asset('img/icons/csv.svg')}}" alt="Экспорт в CSV">
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Таблица остается без изменений -->
            <div class="relative overflow-x-auto px-6 ">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Идентификатор
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Онлайн
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Имя
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Зарегистрирован
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Роль
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Заработано
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Переведено
                        </th>
                        <th scope="col" class="px-6 py-3">
                            На проверке
                        </th>
                        @if(auth()->user()->role === 1)
                            <th scope="col" class="px-6 py-3">
                                Действие
                            </th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr class="bg-white border-b">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{$user->id}}
                            </th>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-3 w-3 rounded-full @if($user->is_online) bg-green-500 @else bg-gray-400 @endif"></div>
                                    <span class="ml-2">
                                        {{ $user->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{$user->name}}
                            </td>
                            <td class="px-6 py-4">
                                {{$user->created_at}}
                            </td>
                            <td class="px-6 py-4">
                                {{\App\Models\User::getRoleName($user->role)}}
                            </td>

                            @if(isset($user->total_earnings))
                                <td class="px-6 py-4">
                                    {{$user->total_earnings}}
                                </td>
                            @endif

                            <td class="px-6 py-4">
                                {{ $user->translations_status2_count }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $user->translations_status1_count }}
                            </td>

                            @if(auth()->user()->role === 1)
                                <td class="px-6 py-4 flex items-center">
                                    @if($user->role !== 1)
                                        <a href="{{ route('users.edit', $user->id) }}"
                                           class="mx-2 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">
                                            Изменить
                                        </a>

                                        <form action="{{route('user.delete', $user->id)}}" method="post">
                                            @csrf
                                            @method('delete')
                                            <button type="submit"
                                                    onclick="return confirm('Вы уверены, что хотите удалить пользователя?')"
                                                    class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">
                                                Удалить
                                            </button>
                                        </form>
                                    @endif

                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="mt-2">
                    {{$users->links()}}
                </div>
            </div>
        </div>

        <!-- Modal для изменения роли -->
        <div id="roleModal" style="justify-content: center; align-items: center; height: 100%" tabindex="-1"
             class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-modal md:h-full">
            <div class="relative w-full h-full max-w-md md:h-auto">
                <div class="relative bg-white rounded-lg shadow">
                    <button type="button"
                            class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                            data-modal-hide="roleModal">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="px-6 py-6 lg:px-8">
                        <h3 class="mb-4 text-xl font-medium text-gray-900">Изменить роль пользователя</h3>
                        <form class="space-y-6" id="roleForm" method="post" action="">
                            @csrf
                            @method('patch')
                            <div>
                                <label for="role" class="block mb-2 text-sm font-medium text-gray-900">Выберите
                                    роль</label>
                                <select id="role" name="role"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @foreach($roles as $id => $role)
                                        <option value="{{$id}}">{{$role}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit"
                                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
             document.addEventListener('DOMContentLoaded', function () {
                // Управление фильтром
                const filterButton = document.getElementById('filterButton');
                const filterDropdown = document.getElementById('filterDropdown');
                
                filterButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    filterDropdown.classList.toggle('hidden');
                });
                
                document.addEventListener('click', function() {
                    filterDropdown.classList.add('hidden');
                });
                
                filterDropdown.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                // Модальное окно для изменения роли
                const roleModal = document.getElementById('roleModal');
                const roleForm = document.getElementById('roleForm');
                const modalButtons = document.querySelectorAll('.open-modal');
                const modalCloseButton = roleModal.querySelector('[data-modal-hide]');

                // Открыть модальное окно
                modalButtons.forEach(button => {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        const userId = this.dataset.userid;
                        const route = `/users/${userId}/role`;
                        roleForm.action = route;
                        roleModal.classList.remove('hidden');
                        roleModal.classList.add('flex');
                    });
                });

                // Закрыть модальное окно при клике на кнопку закрытия
                modalCloseButton.addEventListener('click', function () {
                    roleModal.classList.add('hidden');
                    roleModal.classList.remove('flex');
                });

                // Закрыть модальное окно при клике вне его области
                window.addEventListener('click', function (event) {
                    if (event.target === roleModal) {
                        roleModal.classList.add('hidden');
                        roleModal.classList.remove('flex');
                    }
                });

                // Закрыть модальное окно при отправке формы
                roleForm.addEventListener('submit', function () {
                    roleModal.classList.add('hidden');
                    roleModal.classList.remove('flex');
                });
            });
        </script>
    @endif
@endsection