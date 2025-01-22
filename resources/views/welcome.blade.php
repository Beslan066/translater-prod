@extends('layouts.home')

@section('content')

    @if(auth()->user()->role == 1)
	@if(isset($sentences))
	
	@if($sentences->count() == 0)
            <div class="container mx-auto p-6">
                <h2 class="text-3xl font-extrabold dark:text-white py-4">Загрузите файл корпуса и предложения отобразятся на странице</h2>
                <form id="uploadForm" action="{{ route('sentences.upload') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="block text-gray-700 font-bold mb-2">Выберите файл</label>
                        <input type="file" name="file" class="form-control w-full px-3 py-2 border rounded" required>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Загрузить
                    </button>
                </form>
            </div>


            <div class="progress">
                <div id="progress-bar" class="progress-bar" style="width: 0%;"></div>
            </div>
            <p id="progress-text">0%</p>
	@endif
	@else
            <div class="container mx-auto p-6 flex justify-between card-container">
                <div class="bg-white p-6 rounded shadow-md mx-8 w-1/3 flex justify-between items-center">
                    <div>
                        В базе <strong class="text-green-900 focus:text-red-600 ...">1млн</strong> предложений!
                    </div>
                    <div class="flex flex-col items-center">
                        <form action="{{route('sentences.delete')}}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-3 py-2 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">
                                <img src="{{asset('img/icons/delete.svg')}}" alt="" style="width: 20px; height: 20px">
                            </button>
                        </form>
                        <span style="color: rgb(26 86 219)">Очистить</span>

                    </div>
                </div>

                <div class="bg-white p-6 rounded shadow-md  w-1/3 mx-8 flex justify-between items-center">
                    <div>
                        В базе <strong class="text-green-900 focus:text-red-600 ...">{{$users->count()}}</strong> учитель!
                    </div>
                    <div>
                        <a href="{{route('users.index')}}" class="flex flex-col items-center" style="cursor: pointer;">
                            <img src="{{asset('img/icons/arrowright.svg')}}" alt="" style="width: 130px; height: 40px;">
                            <span style="color: rgb(26 86 219)">показать</span>
                        </a>
                    </div>
                </div>

                <div class="bg-white p-6 rounded shadow-md  w-1/3 mx-8 flex justify-between items-center">
                    <div>
                        Переведено: <strong class="text-green-900 focus:text-red-600 ...">{{$sentencesTranslateCompleted->count()}}</strong>
                    </div>
                    <div>
                        <a class="flex flex-col items-center" style="cursor: pointer;" href="{{route('sentence.completed')}}">
                            <img src="{{asset('img/icons/arrowright.svg')}}" alt="" style="width: 130px; height: 40px;">
                            <span style="color: rgb(26 86 219)">показать</span>
                        </a>
                    </div>
                </div>


            </div>
        @endif
    @endif



    @if(auth()->user()->role === 1)
        <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
            <div class="flex items-center w-full justify-between">
                <div>
                    <h3 class="mb-8 px-6">Отправленно на проверку корректору {{$sentencesTranslate->count()}}:</h3>
                </div>
                <div>
                    <form class="flex items-center max-w-sm mx-auto p-6" action="{{ route('sentences.search') }}" method="GET">
                        @csrf
                        <label for="simple-search" class="sr-only">Search</label>
                        <div class="relative w-full">

                            <input type="text" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Введите для поиска..." required name="search"/>
                        </div>
                        <button type="submit" class="p-2.5 ms-2 text-sm font-medium text-white bg-blue-700 rounded-lg border border-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                            <span class="sr-only">Найти</span>
                        </button>
                    </form>
                </div>
            </div>
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
                            Переведен
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sentencesTranslate as $item)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{$item->id}}
                            </th>
                            <td class="px-6 py-4">
                                {{$item->sentence}}
                            </td>
                            <td class="px-6 py-4">
                                <!-- Перебираем все переводы предложения -->
                                @if($item->translations->isNotEmpty())
                                    @foreach($item->translations as $translation)
                                        <div>
                                            <!-- Перевод предложения -->
                                            {{$translation->translation}}
                                        </div>
                                    @endforeach
                                @else
                                    Нет перевода
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <!-- Перебираем все переводы предложения -->
                                @if($item->translations->isNotEmpty())
                                    @foreach($item->translations as $translation)
                                        <div>
                                            <!-- Автор перевода -->
                                            @if($translation->user)
                                                {{$translation->user->name}}
                                            @else
                                                (Автор неизвестен)
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    Нет перевода
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{$item->created_at}}
                            </td>
                        </tr>
                    @endforeach


                    </tbody>
                </table>

                <div class="mt-4">
                    <a class="text-red-800 text-xl	" href="{{route('sentence.district')}}">Показать отклоненные предложения</a>
                </div>
            </div>


        </div>
    @endif





    <!-- Modal -->
    <div id="resultModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle"></h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500" id="modalMessage"></p>
                </div>
            </div>
            <div class="bg-gray-50 p-4 flex justify-end">
                <button id="closeModalButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>

@endsection
