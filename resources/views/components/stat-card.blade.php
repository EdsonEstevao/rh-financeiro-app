{{-- resources/views/components/stat-card.blade.php --}}
@props(['title', 'value', 'color' => 'blue', 'icon' => 'chart-bar'])

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6 hover:shadow-lg transition duration-300">
    <div class="flex items-center">
        <div class="flex-shrink-0 p-3 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-900">
            @switch($icon)
                @case('users')
                    <svg class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                @break

                @case('check-circle')
                    <svg class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @break

                @default
                    <svg class="w-6 h-6 text-{{ $color }}-600 dark:text-{{ $color }}-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
            @endswitch
        </div>
        <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $title }}</h3>
            <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</p>
            </div>
        </div>
    </div>
</div>
