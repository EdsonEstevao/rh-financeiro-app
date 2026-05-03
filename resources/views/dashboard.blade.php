{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Welcome Card --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ __("You're logged in!") }}
                            </h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                {{ __('Welcome to the system. Use the navigation menu to access the available features.') }}
                            </p>
                        </div>
                        <div class="hidden sm:block">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Info Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {{-- User Info Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Profile') }}</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __('Edit Profile') }} →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Role Info Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Your Role') }}</h3>
                                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ auth()->user()->roles->first()?->name ?? 'No role assigned' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ auth()->user()->getAllPermissions()->count() ?? 0 }} {{ __('permissions') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account Status Card --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Account Status') }}</h3>
                                <p class="text-lg font-semibold {{ auth()->user()->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ auth()->user()->is_active ? __('Active') : __('Inactive') }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Member since') }} {{ auth()->user()->created_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Overview --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- Recent Activity --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('Recent Activity') }}
                        </h3>

                        <div class="space-y-4">
                            @php
                                $activities = [
                                    ['icon' => 'login', 'text' => 'Last login', 'time' => auth()->user()->last_login_at?->diffForHumans() ?? 'First access', 'color' => 'blue'],
                                    ['icon' => 'profile', 'text' => 'Account created', 'time' => auth()->user()->created_at->diffForHumans(), 'color' => 'green'],
                                    ['icon' => 'email', 'text' => 'Email ' . (auth()->user()->email_verified_at ? 'verified' : 'not verified'), 'time' => auth()->user()->email_verified_at?->diffForHumans() ?? 'Pending', 'color' => auth()->user()->email_verified_at ? 'purple' : 'yellow'],
                                ];
                            @endforeach

                            @foreach($activities as $activity)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="flex items-center justify-center h-8 w-8 rounded-full bg-{{ $activity['color'] }}-100 dark:bg-{{ $activity['color'] }}-900">
                                            @if($activity['icon'] === 'login')
                                                <svg class="w-4 h-4 text-{{ $activity['color'] }}-600 dark:text-{{ $activity['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                </svg>
                                            @elseif($activity['icon'] === 'profile')
                                                <svg class="w-4 h-4 text-{{ $activity['color'] }}-600 dark:text-{{ $activity['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-{{ $activity['color'] }}-600 dark:text-{{ $activity['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $activity['text'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $activity['time'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            {{ __('Quick Links') }}
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @php
                                $links = [];

                                if (auth()->user()->hasRole('admin')) {
                                    $links[] = ['route' => 'admin.dashboard', 'label' => 'Admin Panel', 'color' => 'red'];
                                }
                                if (auth()->user()->hasRole('admin|rh|gerente')) {
                                    $links[] = ['route' => 'rh.employees.index', 'label' => 'Employees', 'color' => 'blue'];
                                }
                                if (auth()->user()->hasRole('admin|financeiro|consultor')) {
                                    $links[] = ['route' => 'financeiro.boletos.index', 'label' => 'Boletos', 'color' => 'green'];
                                }
                                if (auth()->user()->hasRole('admin|financeiro')) {
                                    $links[] = ['route' => 'financeiro.credit-cards.index', 'label' => 'Credit Cards', 'color' => 'purple'];
                                }
                                if (auth()->user()->hasRole('funcionario')) {
                                    $links[] = ['route' => 'funcionario.payroll', 'label' => 'My Payroll', 'color' => 'yellow'];
                                    $links[] = ['route' => 'funcionario.boletos', 'label' => 'My Boletos', 'color' => 'indigo'];
                                }
                                if (auth()->user()->hasRole('consultor')) {
                                    $links[] = ['route' => 'consultor.dashboard', 'label' => 'Consultor Panel', 'color' => 'pink'];
                                }
                                if (auth()->user()->hasRole('gerente')) {
                                    $links[] = ['route' => 'gerente.team', 'label' => 'My Team', 'color' => 'teal'];
                                }
                            @endphp

                            @foreach($links as $link)
                                <a href="{{ route($link['route']) }}"
                                   class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200 group">
                                    <span class="flex-shrink-0 w-3 h-3 rounded-full bg-{{ $link['color'] }}-500 mr-3"></span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">
                                        {{ $link['label'] }}
                                    </span>
                                    <svg class="ml-auto w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endforeach

                            {{-- Default links for all users --}}
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition duration-200 group">
                                <span class="flex-shrink-0 w-3 h-3 rounded-full bg-gray-500 mr-3"></span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">
                                    {{ __('Profile Settings') }}
                                </span>
                                <svg class="ml-auto w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Notifications (if any) --}}
            @if(session('status'))
                <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                    {{ session('status') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
