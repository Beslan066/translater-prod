@extends('layouts.home')

@section('content')
    <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
        <div class="flex items-center w-full justify-between">
            <div class="flex">
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
                @foreach($sentencesTranslateCompleted as $item)
                    <tr class="bg-white border-b">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                            {{$item->id}}
                        </th>
                        <td class="px-6 py-4">
                            {{$item->sentence}}
                        </td>
                        <td class="px-6 py-4">
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
            <div class="mt-4 flex justify-between items-center">
                {{$sentencesTranslateCompleted->links()}}

                <button id="exportButton" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Экспорт в CSV
                </button>
            </div>

        </div>

    </div>


    <!-- Модальное окно для отображения прогресса -->
    <div id="exportModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Экспорт данных</h3>
                <div class="mt-2 px-7 py-3">
                    <p id="exportStatus" class="text-sm text-gray-500">Подготовка к экспорту...</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                        <div id="exportProgressBar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                    <p id="exportProgressText" class="text-sm text-gray-500 mt-2">0%</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="closeModalButton" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Закрыть
                    </button>
                    <a id="downloadButton" href="#" class="hidden px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 ml-2">
                        Скачать
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>


    <script>
        document.getElementById('exportButton').addEventListener('click', function () {
            const modal = document.getElementById('exportModal');
            modal.style.display = 'block';

            const exportStatus = document.getElementById('exportStatus');
            const progressBar = document.getElementById('exportProgressBar');
            const progressText = document.getElementById('exportProgressText');
            const downloadButton = document.getElementById('downloadButton');

            // Сброс состояния
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            downloadButton.style.display = 'none';
            exportStatus.textContent = 'Подготовка к экспорту...';

            // Запуск экспорта
            fetch('/export/sentences', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Ошибка запуска экспорта: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Batch started:", data);
                    const batchId = data.batch_id;

                    if (!batchId) {
                        throw new Error("Не удалось получить batch_id");
                    }

                    const checkProgress = () => {
                        fetch(`/export/progress/${batchId}`)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`Ошибка проверки прогресса: ${response.status}`);
                                }
                                return response.json();
                            })
                            .then(progressData => {
                                console.log("Прогресс:", progressData);
                                progressBar.style.width = `${progressData.progress}%`;
                                progressText.textContent = `${progressData.progress}%`;

                                if (progressData.finished) {
                                    if (progressData.file_exists) {
                                        exportStatus.textContent = 'Экспорт завершен!';
                                        downloadButton.href = progressData.download_url;
                                        downloadButton.style.display = 'inline-block';
                                    } else {
                                        exportStatus.textContent = 'Файл не найден. Попробуйте позже.';
                                    }
                                } else {
                                    setTimeout(checkProgress, 2000);
                                }
                            })
                            .catch(error => {
                                console.error("Ошибка получения прогресса:", error);
                                exportStatus.textContent = "Ошибка при получении статуса экспорта.";
                            });
                    };

                    setTimeout(checkProgress, 2000);
                })
                .catch(error => {
                    console.error("Ошибка запуска экспорта:", error);
                    exportStatus.textContent = "Ошибка при запуске экспорта.";
                });
        });

        // Закрытие модального окна
        document.getElementById('closeModalButton').addEventListener('click', function () {
            document.getElementById('exportModal').classList.add('hidden');
        });
    </script>
@endsection