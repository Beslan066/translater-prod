@extends('layouts.home')

@section('content')
    @if(auth()->user()->role === 1)
        <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
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
                            Автор
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Цена
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Сбросить
                        </th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sentences as $item)
                        @foreach($lockedUser as $user) @endforeach
                        <tr class="bg-white border-b">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{$item->id}}
                            </th>
                            <td class="px-6 py-4">
                                {{$item->sentence}}
                            </td>
                            <td class="px-6 py-4">
                                {{$user->name}}
                            </td>
                            <td class="px-6 py-4">
                                {{$item->price}}
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{route('sentence.update', $item->id)}}" method="post">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="font-medium text-blue-600 hover:underline">Сбросить</button>
                                </form>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="mt-2 mb-2">
                    {{$sentences->links()}}
                </div>
                <div class="my-4">
                    <a href="{{route('home')}}" class="font-medium text-blue-600 hover:underline">
                        <button type="button" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 focus:outline-none">Вернуться</button>
                    </a>
                </div>
            </div>

        </div>
    @endif
@endsection