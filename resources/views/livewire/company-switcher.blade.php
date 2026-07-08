<div class="fi-sidebar-group px-3 py-2 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-2 px-3 py-1">
        <x-filament::icon
            name="heroicon-o-building-office"
            class="size-5 text-gray-400 dark:text-gray-500 shrink-0"
        />
        <x-filament::input.wrapper class="w-full">
            <x-filament::input.select
                wire:change="switchCompany($event.target.value)"
                class="text-sm"
            >
                @foreach ($companies as $company)
                    <option value="{{ $company['id'] }}" @if ((int) $company['id'] === $activeCompanyId) selected @endif>
                        {{ $company['name'] }}
                    </option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
</div>
