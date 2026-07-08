<div class="flex items-center gap-0.5" x-data>
    @foreach(config('languages.supported') as $code => $label)
        <button
            wire:click="switch('{{ $code }}')"
            @class([
                'px-2.5 py-1 text-xs font-semibold uppercase tracking-wider transition-colors cursor-pointer',
                'text-primary-600 dark:text-primary-400 border-b-2 border-primary-600 dark:border-primary-400' => app()->getLocale() === $code,
                'text-gray-400 dark:text-gray-500 border-b-2 border-transparent hover:text-gray-600 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' => app()->getLocale() !== $code,
            ])
        >
            {{ $code }}
        </button>
    @endforeach
</div>