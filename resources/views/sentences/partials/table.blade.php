<div class="relative overflow-x-auto px-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title }}</h2>

    {{-- КОМПЛЕКСНЫЙ ФИКС --}}
    @php
        // 1. Проверяем существует ли переменная
        if (!isset($items) || is_null($items)) {
            $items = collect();
        }

        // 2. Преобразуем в коллекцию если это не итерируемый объект
        if (!is_iterable($items)) {
            $items = collect();
        }

        // 3. Проверяем есть ли метод count()
        $hasItems = true;
        try {
            $hasItems = count($items) > 0;
        } catch (Exception $e) {
            $hasItems = false;
            $items = collect();
        }
    @endphp

    @if($hasItems)
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="px-6 py-3">ID</th>
                <th class="px-6 py-3">Предложение</th>
                <th class="px-6 py-3">Перевод</th>
                <th class="px-6 py-3">Автор</th>
                <th class="px-6 py-3">Дата</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr class="bg-white border-b">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->id }}</td>
                    <td class="px-6 py-4">{{ $item->sentence }}</td>
                    <td class="px-6 py-4">
                        @if(isset($item->translations) && is_iterable($item->translations))
                            @foreach($item->translations as $translation)
                                <div>{{ $translation->translation }}</div>
                            @endforeach
                        @else
                            Нет перевода
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($item->translations) && is_iterable($item->translations))
                            @foreach($item->translations as $translation)
                                <div>{{ $translation->user->name ?? '(Автор неизвестен)' }}</div>
                            @endforeach
                        @else
                            Нет перевода
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->created_at->format('d.m.Y H:i') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            @if(method_exists($items, 'links'))
                {{ $items->links() }}
            @endif
        </div>
    @else
        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-400">Нет данных</td></tr>
    @endif
</div>