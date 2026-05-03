{{-- resources/views/rh/reports/attendance.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Relatório de Ponto / Frequência') }}</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg mb-6 p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div><label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mês</label><select
                            name="month"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}"
                                    {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                    </div>
                    <div><label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ano</label><select
                            name="year"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            @for ($y = now()->year; $y >= now()->subYears(5)->year; $y--)
                                <option value="{{ $y }}"
                                    {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select></div>
                    <div class="flex space-x-2"><button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filtrar</button><a
                            href="{{ route('rh.reports.attendance') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md text-sm">Limpar</a>
                    </div>
                </form>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <p class="text-center text-gray-500 py-12">Relatório de ponto será implementado com integração ao
                    sistema de ponto eletrônico.</p>
            </div>
        </div>
    </div>
</x-app-layout>
