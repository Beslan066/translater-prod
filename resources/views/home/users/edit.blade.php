@extends('layouts.home')

@section('content')
    <section class="bg-white">
        <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
            <h2 class="mb-4 text-xl font-bold text-gray-900">Редактирование пользователя</h2>
            <form action="{{ route('users.update', $user->id) }}" method="post">
                @csrf
                @method('patch')

                <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                    <div class="sm:col-span-2">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Имя</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5"
                               required>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5"
                               required>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">
                            Новый пароль (оставьте пустым, если не меняется)
                        </label>
                        <input type="password" name="password" id="password"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">
                            Подтвердите пароль
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="role" class="block mb-2 text-sm font-medium text-gray-900">Роль</label>
                        <select id="role" name="role" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @foreach($roles as $id => $role)
                                <option value="{{ $id }}" {{ old('role', $user->role) == $id ? 'selected' : '' }}>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="submit"
                        class="mt-6 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Сохранить изменения
                </button>
            </form>
        </div>
    </section>
@endsection