@props(['title', 'value', 'icon', 'color'])

<div class="rounded-xl bg-{{ $color }}-500/10 shadow-sm p-6 flex items-center justify-between hover:scale-[1.02] transition-transform hover:shadow-md">
    <div>
        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</h3>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $value }}</p>
    </div>
    <div class="ml-4 bg-{{ $color }}-500/20 rounded-full p-2">
        <x-dynamic-component :component="$icon" class="w-5 h-5 text-{{ $color }}-600" />
    </div>
</div>
