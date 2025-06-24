@extends('layouts.home')

@section('content')
    @if(isset($translations))
        <div class="container mx-auto p-6 flex flex-col justify-between card-container">
            <div class="flex items-center mb-4">
                <div style="width: 120px; height: 120px" class="mr-4">
                    <img src="{{asset('img/user.png')}}" alt="">
                </div>
                <div>
                    <h3 class="mb-8 text-2xl font-bold text-gray-800">{{$user->name}}</h3>
                    <span>Зарегистрирован(а) {{$user->created_at}}</span>
                </div>
            </div>

            <div class="mb-4">
                <h3 class="text-xl font-extrabold dark:text-white">Переводы на проверке {{$translationsCount}}</h3>
            </div>

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            ID
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Оригинал
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Перевод
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Автор
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Дата создания
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Действия
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($translations as $translation)
                        <tr class="bg-white border-b">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{$translation->id}}
                            </th>
                            <td class="px-6 py-4">
                                {{$translation->sentence->sentence ?? 'N/A'}}
                            </td>
                            <td class="px-6 py-4">
                                <!-- Текущий перевод с идентификатором -->
                                <div class="mb-2 current-translation" id="current-translation-{{ $translation->id }}">
                                    {{$translation->translation ?? 'Нет перевода'}}
                                </div>

                                <!-- Форма редактирования перевода -->
                                <form action="{{ route('translations.edit', $translation->id) }}" method="post" class="edit-translation-form flex items-center gap-2" data-translation-id="{{ $translation->id }}">
                                    @csrf
                                    <textarea name="translation" style="resize: none; width: 300px;" class="border border-gray-300 rounded p-2 w-full translation-textarea" rows="4" required>{{ $translation->translation }}</textarea>
                                    <button type="submit" class="focus:outline-none text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                                        Сохранить
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                {{$translation->user->name ?? 'Автор неизвестен'}}
                            </td>
                            <td class="px-6 py-4">
                                {{$translation->created_at->format('d.m.Y H:i') ?? 'Дата неизвестна'}}
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{route('sentences.approve', $translation->sentence->id)}}" method="post">
                                    @csrf
                                    <button type="submit" class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Подтвердить</button>
                                </form>

                                <form action="{{route('sentences.reject', $translation->sentence->id)}}" method="post">
                                    @csrf
                                    <button type="submit" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">Отклонить</button>
                                </form>

                                <form action="{{route('sentences.delay', $translation->sentence->id)}}" method="post">
                                    @csrf
                                    <button type="submit" class="focus:outline-none text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2">
                                        Отложить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white border-b">
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Нет переводов для отображения
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано с {{$translations->firstItem()}} по {{$translations->lastItem()}} из {{$translations->total()}} записей
                </div>
                <div class="pagination-links">
                    {{$translations->links()}}
                </div>
            </div>
        </div>
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



        @endif
@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация модального окна удаления
            const deleteModal = document.getElementById('deleteModal');
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteForm = document.getElementById('deleteForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('data-url');
                    deleteForm.action = url;
                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex');
                });
            });

            // Закрытие модального окна
            const closeButtons = document.querySelectorAll('[data-modal-hide="deleteModal"]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');
                });
            });

            // Закрытие при клике вне модального окна
            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Обработка формы редактирования перевода
            $(document).on('submit', '.edit-translation-form', function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');
                var formData = form.serialize();
                var translationId = form.data('translation-id');
                var newTranslation = form.find('.translation-textarea').val();

                $.ajax({
                    url: url,
                    type: 'POST', // или 'PUT' в зависимости от вашего роута
                    data: formData,
                    success: function(response) {
                        // Обновляем текст текущего перевода
                        $('#current-translation-' + translationId).text(newTranslation);

                        // Показываем уведомление об успешном сохранении
                        $('#modalTitle').text('Успех');
                        $('#modalMessage').text('Перевод успешно обновлен.');
                        $('#resultModal').removeClass('hidden').addClass('flex');
                    },
                    error: function(xhr) {
                        $('#modalTitle').text('Ошибка');
                        $('#modalMessage').text('Произошла ошибка при обновлении перевода: ' + (xhr.responseJSON?.message || xhr.statusText));
                        $('#resultModal').removeClass('hidden').addClass('flex');
                    }
                });
            });

            // Закрытие модального окна
            $('#closeModalButton').on('click', function() {
                $('#resultModal').removeClass('flex').addClass('hidden');
            });
        });
    </script>
@endpush