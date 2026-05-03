{{-- resources/views/admin/settings.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações do Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- General Settings --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações Gerais</h3>
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do
                                    Sistema</label>
                                <input type="text" name="app_name" value="{{ config('app.name') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                                <select name="timezone"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                    <option value="America/Sao_Paulo"
                                        {{ config('app.timezone') === 'America/Sao_Paulo' ? 'selected' : '' }}>São Paulo
                                        (BRT)</option>
                                    <option value="America/Manaus"
                                        {{ config('app.timezone') === 'America/Manaus' ? 'selected' : '' }}>Manaus (AMT)
                                    </option>
                                    <option value="UTC" {{ config('app.timezone') === 'UTC' ? 'selected' : '' }}>UTC
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Idioma
                                    Padrão</label>
                                <select name="locale"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                                    <option value="pt_BR" {{ config('app.locale') === 'pt_BR' ? 'selected' : '' }}>
                                        Português (Brasil)</option>
                                    <option value="en" {{ config('app.locale') === 'en' ? 'selected' : '' }}>English
                                    </option>
                                    <option value="es" {{ config('app.locale') === 'es' ? 'selected' : '' }}>Español
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Email Settings --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações de Email</h3>
                    <form class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Servidor
                                    SMTP</label>
                                <input type="text" value="{{ config('mail.mailers.smtp.host') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Porta</label>
                                <input type="number" value="{{ config('mail.mailers.smtp.port') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                    Remetente</label>
                                <input type="email" value="{{ config('mail.from.address') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome
                                    Remetente</label>
                                <input type="text" value="{{ config('mail.from.name') }}"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Security Settings --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Segurança</h3>
                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Autenticação de 2
                                    Fatores</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Exigir 2FA para usuários
                                    administradores</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                        <div
                            class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Bloqueio por Tentativas
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Bloquear após 5 tentativas de login
                                    falhas</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Backup & Maintenance --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Backup e Manutenção</h3>
                    <div class="flex flex-wrap gap-4">
                        <form action="{{ route('admin.settings.backup') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Criar Backup Agora
                            </button>
                        </form>
                        <button
                            onclick="if(confirm('Limpar cache do sistema?')) { /* lógica */ alert('Cache limpo!'); }"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 text-sm font-medium">
                            Limpar Cache
                        </button>
                        <button
                            onclick="if(confirm('Otimizar banco de dados?')) { /* lógica */ alert('Banco otimizado!'); }"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                            Otimizar Banco
                        </button>
                    </div>
                </div>
            </div>

            {{-- System Info --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informações do Sistema</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">Laravel</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-mono">{{ app()->version() }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">PHP</dt>
                            <dd class="text-gray-900 dark:text-gray-100 font-mono">{{ PHP_VERSION }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">Ambiente</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ app()->environment() }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">Banco de Dados</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ config('database.default') }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">Servidor Web</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' }}
                            </dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <dt class="text-gray-500 dark:text-gray-400">URL Base</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ config('app.url') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
