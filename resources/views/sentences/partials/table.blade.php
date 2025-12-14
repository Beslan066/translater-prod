<div class="relative overflow-x-auto px-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ $title }}</h2>
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
        <tr>
            <th class="px-6 py-3">ID перевода</th>
            <th class="px-6 py-3">ID предложения</th>
            <th class="px-6 py-3">Предложение</th>
            <th class="px-6 py-3">Перевод</th>
            <th class="px-6 py-3">Автор перевода</th>
            <th class="px-6 py-3">Дата перевода</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $translation)
            <tr class="bg-white border-b">
                <td class="px-6 py-4 font-medium text-gray-900">
                    {{ $translation->id }}
                </td>
                <td class="px-6 py-4 font-medium text-gray-900">
                    {{ $translation->sentence_id }}
                </td>
                <td class="px-6 py-4">
                    {{ $translation->sentence->sentence ?? 'Предложение не найдено' }}
                </td>
                <td class="px-6 py-4">
                    {{ $translation->translation ?? 'Нет перевода' }}
                </td>
                <td class="px-6 py-4">
                    {{ $translation->user->name ?? '(Автор неизвестен)' }}
                </td>
                <td class="px-6 py-4">
                    {{ $translation->created_at->format('d.m.Y H:i') }}
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-6 py-4 text-center text-gray-400">Нет данных</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>