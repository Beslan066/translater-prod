<div class="relative overflow-x-auto px-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title }}</h2>
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
            <th class="px-6 py-3">ID</th>
            <th class="px-6 py-3">Предложение</th>
            <th class="px-6 py-3">Перевод</th>
            <th class="px-6 py-3">Автор</th>
            <th class="px-6 py-3">Дата перевода</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            @php
                // Получаем последний перевод (уже отсортирован в контроллере)
                $latestTranslation = $item->translations->first();
            @endphp
            <tr class="bg-white border-b">
                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->id }}</td>
                <td class="px-6 py-4">{{ $item->sentence }}</td>
                <td class="px-6 py-4">
                    @if($latestTranslation)
                        <div>{{ $latestTranslation->translation }}</div>
                    @else
                        Нет перевода
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($latestTranslation)
                        <div>{{ $latestTranslation->user->name ?? '(Автор неизвестен)' }}</div>
                    @else
                        Нет перевода
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($latestTranslation)
                        {{ $latestTranslation->created_at->format('d.m.Y H:i') }}
                    @else
                        Нет даты
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-6 py-4 text-center text-gray-400">Нет данных</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>