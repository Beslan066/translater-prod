@extends('layouts.home')

@section('content')
    <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
        <div class="flex items-center w-full justify-between">

            <div>
                <form class="flex items-center max-w-sm mx-auto p-6" action="{{ route('sentences.search') }}" method="GET">
                    @csrf
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full">

                        <input type="text" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 " placeholder="Введите для поиска..." required name="search"/>
                    </div>
                    <button type="submit" class="p-2.5 ms-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                        <span class="sr-only">Найти</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="relative overflow-x-auto px-6 ">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Идентификатор
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Предложение
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Перевод
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Автор
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Переведен
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach($sentences as $item)
                    <tr class="bg-white border-b">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            {{$item->id}}
                        </th>

                        <!-- Вывод текста перевода -->
                        <td class="px-6 py-4">
                            {{$item->translation}}
                        </td>

                        <!-- Вывод предложения по связи 'sentence' -->
                        <td class="px-6 py-4">
                            {{ optional($item->sentence)->sentence }} <!-- Здесь используем optional, чтобы избежать ошибки, если связь отсутствует -->
                        </td>

                        <!-- Вывод автора перевода -->
                        <td class="px-6 py-4">
                            {{ optional($item->user)->name }} <!-- Вывод имени автора -->
                        </td>

                        <!-- Дата создания перевода -->
                        <td class="px-6 py-4">
                            {{$item->created_at}}
                        </td>
                    </tr>
                @endforeach


                </tbody>
            </table>
        </div>
        <div class="mt-2">
            {{$sentences->links()}}
        </div>
    </div>
@endsection