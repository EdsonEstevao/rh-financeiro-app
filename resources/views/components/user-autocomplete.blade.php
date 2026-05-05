@props([
    'name' => 'user_id',
    'label' => 'Usuário *',
    'placeholder' => 'Digite nome ou e-mail...',
    'value' => null, // user_id selecionado
    'selectedText' => null, // texto inicial (ex: "João - joao@email.com")
    'searchUrl' => null, // route/url que retorna JSON
])

@php
    $searchUrl ??= route('rh.users.search');
@endphp

<div x-data="userAutocomplete({
    name: @js($name),
    searchUrl: @js($searchUrl),
    initialId: @js(old($name, $value)),
    initialText: @js($selectedText),
})" class="relative">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
        {{ $label }}
    </label>

    {{-- Valor real enviado --}}
    <input type="hidden" :name="name" x-model="selectedId">

    {{-- Campo de busca --}}
    <input type="text" x-model="query" @input.debounce.250ms="onInput" @focus="open = true; if (!loadedOnce) onInput()"
        @keydown.escape="open = false" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
        @keydown.enter.prevent="chooseHighlighted()" placeholder="{{ $placeholder }}"
        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm"
        autocomplete="off">

    {{-- Dropdown --}}
    <div x-show="open" x-cloak @click.outside="open = false"
        class="absolute z-20 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden"
        style="display:none">
        <template x-if="loading">
            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-300">Buscando...</div>
        </template>

        <template x-if="!loading && results.length === 0">
            <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-300">Nenhum resultado.</div>
        </template>

        <template x-for="(item, idx) in results" :key="item.id">
            <button type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                :class="idx === highlighted ? 'bg-gray-50 dark:bg-gray-700' : ''" @mousemove="highlighted = idx"
                @click="choose(item)">
                <div class="font-medium text-gray-900 dark:text-gray-100" x-text="item.name"></div>
                <div class="text-xs text-gray-500 dark:text-gray-300" x-text="item.email"></div>
            </button>
        </template>
    </div>

    <template x-if="selectedId">
        <div class="mt-2 text-xs text-gray-500 dark:text-gray-300 flex items-center gap-2">
            <span>Selecionado: </span>
            <span class="font-medium" x-text="selectedText"></span>
            <button type="button" class="underline" @click="clear()">limpar</button>
        </div>
    </template>

    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('userAutocomplete', (cfg) => ({
                    name: cfg.name,
                    searchUrl: cfg.searchUrl,

                    query: cfg.initialText || '',
                    open: false,
                    loading: false,
                    results: [],
                    highlighted: -1,

                    selectedId: cfg.initialId || '',
                    selectedText: cfg.initialText || '',
                    loadedOnce: false,

                    async onInput() {
                        this.open = true;
                        this.loadedOnce = true;

                        const q = (this.query || '').trim();
                        // Se já existe seleção e o usuário não alterou, não precisa buscar
                        if (q.length < 2) {
                            this.results = [];
                            this.highlighted = -1;
                            return;
                        }

                        this.loading = true;
                        try {
                            const url = new URL(this.searchUrl, window.location.origin);
                            url.searchParams.set('q', q);

                            const res = await fetch(url.toString(), {
                                headers: {
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin',
                            });

                            if (!res.ok) throw new Error('Falha na busca');
                            const data = await res.json();

                            this.results = Array.isArray(data) ? data : (data.data ?? []);
                            this.highlighted = this.results.length ? 0 : -1;
                        } finally {
                            this.loading = false;
                        }
                    },

                    choose(item) {
                        this.selectedId = item.id;
                        this.selectedText = `${item.name} — ${item.email}`;
                        this.query = this.selectedText;
                        this.open = false;
                    },

                    clear() {
                        this.selectedId = '';
                        this.selectedText = '';
                        this.query = '';
                        this.results = [];
                        this.highlighted = -1;
                    },

                    move(dir) {
                        if (!this.results.length) return;
                        const next = this.highlighted + dir;
                        if (next < 0) this.highlighted = this.results.length - 1;
                        else if (next >= this.results.length) this.highlighted = 0;
                        else this.highlighted = next;
                    },

                    chooseHighlighted() {
                        if (this.highlighted < 0) return;
                        const item = this.results[this.highlighted];
                        if (item) this.choose(item);
                    },
                }));
            });
        </script>
    @endpush
@endonce
