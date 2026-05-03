{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{
    open: false,
    profileMenu: false,
    currentProfile: '{{ auth()->user()->profile }}'
}" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                {{-- Logo --}}
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                {{-- Navigation Links based on Profile --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    {{-- Admin Navigation --}}
                    @role('admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Admin Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Users') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.audit.index')" :active="request()->routeIs('admin.audit.*')">
                            {{ __('Audit Log') }}
                        </x-nav-link>
                    @endrole

                    {{-- RH Navigation --}}
                    @hasanyrole('admin|rh|gerente')
                        <x-nav-link :href="route('rh.dashboard')" :active="request()->routeIs('rh.*')">
                            {{ __('RH') }}
                        </x-nav-link>
                        <x-nav-link :href="route('rh.employees.index')" :active="request()->routeIs('rh.employees.*')">
                            {{ __('Employees') }}
                        </x-nav-link>
                    @endhasanyrole

                    @role('rh')
                        <x-nav-link :href="route('rh.payroll.index')" :active="request()->routeIs('rh.payroll.*')">
                            {{ __('Payroll') }}
                        </x-nav-link>
                        <x-nav-link :href="route('rh.documents.index')" :active="request()->routeIs('rh.documents.*')">
                            {{ __('Documents') }}
                        </x-nav-link>
                    @endrole

                    {{-- Financeiro Navigation --}}
                    @hasanyrole('admin|financeiro|consultor')
                        <x-nav-link :href="route('financeiro.dashboard')" :active="request()->routeIs('financeiro.*')">
                            {{ __('Financeiro') }}
                        </x-nav-link>
                        <x-nav-link :href="route('financeiro.boletos.index')" :active="request()->routeIs('financeiro.boletos.*')">
                            {{ __('Boletos') }}
                        </x-nav-link>
                        <x-nav-link :href="route('financeiro.credit-cards.index')" :active="request()->routeIs('financeiro.credit-cards.*')">
                            {{ __('Credit Cards') }}
                        </x-nav-link>
                    @endhasanyrole

                    @role('financeiro')
                        <x-nav-link :href="route('financeiro.reports.index')" :active="request()->routeIs('financeiro.reports.*')">
                            {{ __('Reports') }}
                        </x-nav-link>
                    @endrole

                    {{-- Funcionário Navigation --}}
                    @role('funcionario')
                        <x-nav-link :href="route('funcionario.dashboard')" :active="request()->routeIs('funcionario.*')">
                            {{ __('My Area') }}
                        </x-nav-link>
                        <x-nav-link :href="route('funcionario.payroll')" :active="request()->routeIs('funcionario.payroll.*')">
                            {{ __('My Payroll') }}
                        </x-nav-link>
                        <x-nav-link :href="route('funcionario.boletos')" :active="request()->routeIs('funcionario.boletos.*')">
                            {{ __('My Boletos') }}
                        </x-nav-link>
                    @endrole
                </div>
            </div>

            {{-- User Profile Dropdown --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="ms-3 relative" x-data="{ open: false }">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button @click="open = !open"
                                class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out">
                                <div class="flex items-center">
                                    <div
                                        class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-2 text-left">
                                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ auth()->user()->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ auth()->user()->profile === 'admin' ? 'red' : (auth()->user()->profile === 'rh' ? 'blue' : 'green') }}-100 text-{{ auth()->user()->profile === 'admin' ? 'red' : (auth()->user()->profile === 'rh' ? 'blue' : 'green') }}-800">
                                                {{ ucfirst(auth()->user()->profile) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ auth()->user()->email }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Membro desde
                                    {{ auth()->user()->created_at->format('d/m/Y') }}</div>
                            </div>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @role('admin')
                                <x-dropdown-link :href="route('admin.settings')">
                                    {{ __('Settings') }}
                                </x-dropdown-link>
                            @endrole

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            {{-- Hamburger for mobile --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @role('admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    Admin Dashboard
                </x-responsive-nav-link>
            @endrole

            @hasanyrole('admin|rh|gerente')
                <x-responsive-nav-link :href="route('rh.employees.index')" :active="request()->routeIs('rh.employees.*')">
                    Employees
                </x-responsive-nav-link>
            @endhasanyrole

            @hasanyrole('admin|financeiro|consultor')
                <x-responsive-nav-link :href="route('financeiro.boletos.index')" :active="request()->routeIs('financeiro.boletos.*')">
                    Boletos
                </x-responsive-nav-link>
            @endhasanyrole
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ auth()->user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profile
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
