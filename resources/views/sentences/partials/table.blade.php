<div class="relative overflow-x-auto px-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title ?? 'Таблица' }}</h2>

    @php
        // Защита от null
        $items = $items ?? collect();
    @endphp

    @if($items->count())
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
                        @foreach($item->translations as $translation)
                            <div>{{ $translation->translation }}</div>
                        @endforeach
                    </td>
                    <td class="px-6 py-4">
                        @foreach($item->translations as $translation)
                            <div>{{ $translation->user->name ?? '(Автор неизвестен)' }}</div>
                        @endforeach
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->created_at->format('d.m.Y H:i') }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @if(method_exists($items, 'links'))
            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @endif
    @else
        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-400">Нет данных</td></tr>
    @endif
</div>