@extends('layouts.home')

@section('content')
<div class="container mx-auto p-6" x-data="{ tab: 'review' }">
    <div class="mb-4 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="tab = 'review'" :class="tab === 'review' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-2 px-1 border-b-2 font-medium text-sm">
                На проверке
            </button>
            <button @click="tab = 'translated'" :class="tab === 'translated' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap pb-2 px-1 border-b-2 font-medium text-sm">
                Переведено
            </button>
        </nav>
    </div>

    {{-- Таб "На проверке" --}}
    <div x-show="tab === 'review'" x-cloak>
        @include('sentences.partials.table', [
            'items' => $sentencesInReview,
            'title' => 'На проверке'
        ])
    </div>

    {{-- Таб "Переведено" --}}
    <div x-show="tab === 'translated'" x-cloak>
        @include('sentences.partials.table', [
            'items' => $sentencesTranslated,
            'title' => 'Переведено'
        ])
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs" defer></script>
@endpush
