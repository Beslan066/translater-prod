@extends('layouts.home')

@section('content')
    <div class="container mx-auto p-6 flex flex-col justify-between mx-8">
        <div class="flex items-center w-full justify-between">
            <div class="flex">
                <form class="flex items-center max-w-sm mx-auto p-6" action="{{ route('sentence.completed') }}" method="GET">
                    <label for="simple-search" class="sr-only">Search</label>
                    <div class="relative w-full">
                        <input type="text" id="simple-search"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5"
                               placeholder="Введите для поиска..."
                               name="search"
                               value="{{ request('search') }}"/>
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

        <!-- Форма фильтрации и сортировки -->
        <div class="bg-gray-50 p-4 rounded-lg mb-4 mx-6">
            <form method="GET" action="{{ route('sentence.completed') }}" class="grid grid-cols-1 md:grid-cols-7 gap-4">
                <!-- Фильтр по дате от -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата от</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Фильтр по дате до -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Дата до</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Фильтр по переводчику -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Переводчик</label>
                    <select name="translator_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все</option>
                        @foreach($translators as $translator)
                            <option value="{{ $translator->id }}" {{ request('translator_id') == $translator->id ? 'selected' : '' }}>
                                {{ $translator->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Фильтр по корректору -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Корректор</label>
                    <select name="reviewer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все</option>
                        @foreach($correctors as $corrector)
                            <option value="{{ $corrector->id }}" {{ request('reviewer_id') == $corrector->id ? 'selected' : '' }}>
                                {{ $corrector->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Сортировка по -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Сортировать по</label>
                    <select name="sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="date" {{ request('sort_by', 'date') == 'date' ? 'selected' : '' }}>Дате подтверждения</option>
                        <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID (сначала новые/старые)</option>
                        <option value="sentence" {{ request('sort_by') == 'sentence' ? 'selected' : '' }}>Предложению (А-Я)</option>
                    </select>
                </div>

                <!-- Порядок сортировки -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Порядок</label>
                    <select name="sort_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="desc" {{ request('sort_order', 'desc') == 'desc' ? 'selected' : '' }}>Сначала новые</option>
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Сначала старые</option>
                    </select>
                </div>

                <!-- Кнопки -->
                <div class="flex gap-2 items-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Применить
                    </button>
                    <a href="{{ route('sentence.completed') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                        Сбросить
                    </a>
                </div>
            </form>
        </div>

        <!-- Информация о текущей сортировке -->
        <div class="text-sm text-gray-600 mb-2 px-6">
            @php
                $sortByText = [
                    'date' => 'дате подтверждения',
                    'id' => 'ID',
                    'sentence' => 'предложению'
                ][request('sort_by', 'date')];

                $sortOrderText = request('sort_order', 'desc') == 'desc' ? 'новые сначала' : 'старые сначала';
            @endphp
            📊 Сортировка по <strong>{{ $sortByText }}</strong> ({{ $sortOrderText }})
        </div>

        <div class="relative overflow-x-auto px-6">
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
                        Переводчик
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Корректор
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Подтвержден
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Действия
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
                                        {{$translation->translation}}
                                    </div>
                                @endforeach
                            @else
                                Нет перевода
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($item->translations->isNotEmpty())
                                @foreach($item->translations as $translation)
                                    <div>
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
                            @if($item->reviewer)
                                {{$item->reviewer->name}}
                                @if($item->reviewer->role == 2)
                                    <span class="text-xs text-green-600">(Корректор)</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{$item->reviewed_at ? $item->reviewed_at->format('d.m.Y H:i') : $item->created_at->format('d.m.Y H:i')}}
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="returnToPending({{ $item->id }})"
                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-xs">
                                Вернуть на проверку
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-4 flex justify-between items-center">
                {{ $sentencesTranslateCompleted->appends(request()->query())->links() }}

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

    <!-- Форма для возврата на проверку -->
    <form id="returnForm" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
    </form>

    <script>
        function returnToPending(sentenceId) {
            if (confirm('Вы уверены, что хотите вернуть это предложение на проверку?')) {
                const form = document.getElementById('returnForm');
                form.action = `/sentences/${sentenceId}/return-to-pending`;
                form.submit();
            }
        }

        document.getElementById('exportButton').addEventListener('click', function () {
            const modal = document.getElementById('exportModal');
            modal.classList.remove('hidden');

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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
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


@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
@endif