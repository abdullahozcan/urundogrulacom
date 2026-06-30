<div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">
        Aktif Firma
    </div>
    <select
        wire:change="switchCompany($event.target.value)"
        class="block w-full rounded-lg border-gray-300 bg-white text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
    >
        @foreach ($companies as $company)
            <option value="{{ $company['id'] }}" @if ((int) $company['id'] === $activeCompanyId) selected @endif>
                {{ $company['name'] }}
            </option>
        @endforeach
    </select>
</div>
