<div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
        Aktif Firma
    </div>
    <x-filament::input.wrapper>
        <x-filament::input.select
            wire:change="switchCompany($event.target.value)"
        >
            @foreach ($companies as $company)
                <option value="{{ $company['id'] }}" @if ((int) $company['id'] === $activeCompanyId) selected @endif>
                    {{ $company['name'] }}
                </option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
