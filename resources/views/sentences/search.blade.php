@extends('layouts.home')

@section('content')
    <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
        <div class="flex items-center w-full justify-between">
            <div class="p-6">
                <h3 class="mb-8 px-6">Результаты поиска:</h3>
            </div>
        </div>
        <div class="relative overflow-x-auto px-6">
            <div class="relative overflow-x-auto px-6 ">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
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
                            Цена
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Переведен
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sentencesTranslate as $item)
                        @foreach($translates as $translate) @endforeach
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{$item->id}}
                            </th>
                            <td class="px-6 py-4">
                                {{$item->sentence}}
                            </td>
                            <td class="px-6 py-4">
                                {{$translate->translation}}
                            </td>
                            <td class="px-6 py-4">
                                {{$item->locked_by}}
                            </td>
                            <td class="px-6 py-4">
                                {{$item->price}}
                            </td>
                            <td class="px-6 py-4">
                                {{$translate->created_at}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div>
                <a class="font-medium text-blue-600 dark:text-blue-500 hover:underline p-6" href="{{route('home')}}">Назад</a>
            </div>
           <div>
               {{ $sentencesTranslate->links() }}
           </div>
        </div>
    </div>
@endsection
