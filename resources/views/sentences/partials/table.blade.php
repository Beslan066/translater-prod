<div class="relative overflow-x-auto px-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title ?? 'Таблица' }}</h2>

    @php
        // Защита от null - преобразуем в пустую коллекцию если нужно
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
                    <td class="px-6 py-4 font-medium text-gray-900">
                        {{ $item->id ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->sentence ?? $item->sentence->sentence ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($item->translations) && $item->translations->count())
                            @foreach($item->translations as $translation)
                                <div>{{ $translation->translation ?? '—' }}</div>
                            @endforeach
                        @elseif(isset($item->translation))
                            <div>{{ $item->translation }}</div>
                        @else
                            Нет перевода
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($item->translations) && $item->translations->count())
                            @foreach($item->translations as $translation)
                                <div>{{ $translation->user->name ?? '(Автор неизвестен)' }}</div>
                            @endforeach
                        @elseif(isset($item->user))
                            <div>{{ $item->user->name ?? '(Автор неизвестен)' }}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(isset($item->created_at))
                            {{ $item->created_at->format('d.m.Y H:i') }}
                        @elseif(isset($item->translations) && $item->translations->first()?->created_at)
                            {{ $item->translations->first()->created_at->format('d.m.Y H:i') }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @else
        <div class="text-center py-8 text-gray-500">
            Нет данных для отображения
        </div>
    @endif
</div>