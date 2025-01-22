@extends('layouts.home')

@section('content')

    <section class="bg-white dark:bg-gray-900">
        <div class="py-8 px-4 mx-auto max-w-2xl lg:py-16">
            <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Измените пользователя</h2>
            <form action="{{route('users.updateRole', $user->id)}}" method="post">
                @csrf
                @method('patch')
                    <div>
                        <label for="category" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Роль</label>
                        <select id="category" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option selected="" value="{{$user->role}}">{{\App\Models\User::getRoleName($user->role)}}</option>
                            @foreach($roles as $id => $role)
                                <option value="{{$id}}" {{ $id == $user->role ? 'selected' : '' }}>{{$role}}</option>
                            @endforeach
                        </select>
                    </div>
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 my-2">Изменить</button>
            </form>
        </div>
    </section>


@endsection