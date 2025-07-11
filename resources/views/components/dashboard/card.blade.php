@props(['title', 'value', 'icon', 'color'])

@php
    $icons = [
        'dollar' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 1v22M17 5H9a3 3 0 0 0 0 6h6a3 3 0 0 1 0 6H6" /></svg>',
        'shield' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'percent' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
    ];
@endphp

<div class="rounded-xl bg-{{ $color }}-500/10 shadow-sm p-6 flex items-center justify-between hover:scale-[1.02] transition-transform hover:shadow-md">
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</h3>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $value }}</p>
    </div>
    <div class="ml-4 bg-{{ $color }}-500/20 rounded-full p-2">
        {!! $icons[$icon] ?? '' !!}
    </div>
</div>
