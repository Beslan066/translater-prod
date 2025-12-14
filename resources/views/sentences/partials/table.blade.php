<div class="relative overflow-x-auto px-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title }}</h2>

    {{-- ФИКС: Проверяем что $items существует и не null --}}
    @if(isset($items) && (is_array($items) || $items instanceof \Countable))
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
            {{-- ФИКС: @forelse вместо @foreach --}}
            @forelse($items as $item)
                <tr class="bg-white border-b">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->id }}</td>
                    <td class="px-6 py-4">{{ $item->sentence }}</td>
                    <td class="px-6 py-4">
                        @forelse($item->translations as $translation)
                            <div>{{ $translation->translation }}</div>
                        @empty
                            Нет перевода
                        @endforelse
                    </td>
                    <td class="px-6 py-4">
                        @forelse($item->translations as $translation)
                            <div>{{ $translation->user->name ?? '(Автор неизвестен)' }}</div>
                        @empty
                            Нет перевода
                        @endforelse
                    </td>
                    <td class="px-6 py-4">
                        {{ $item->created_at->format('d.m.Y H:i') }}
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
    @else
        {{-- Если $items вообще не существует --}}
        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-400">Нет данных (переменная не задана)</td></tr>
    @endif
</div>