<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="contact_address" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                Adres
                <span class="text-danger-600 dark:text-danger-400">*</span>
            </label>
            <x-filament::input
                id="contact_address"
                type="text"
                wire:model.blur="contact_address"
                class="mt-2"
            />
            @error('contact_address')
                <p class="fi-fo-field-label-error-label text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="contact_email" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    E-posta (Görünen)
                    <span class="text-danger-600 dark:text-danger-400">*</span>
                </label>
                <x-filament::input
                    id="contact_email"
                    type="email"
                    wire:model.blur="contact_email"
                    class="mt-2"
                />
                @error('contact_email')
                    <p class="fi-fo-field-label-error-label text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_phone" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Telefon
                    <span class="text-danger-600 dark:text-danger-400">*</span>
                </label>
                <x-filament::input
                    id="contact_phone"
                    type="tel"
                    wire:model.blur="contact_phone"
                    class="mt-2"
                />
                @error('contact_phone')
                    <p class="fi-fo-field-label-error-label text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="contact_form_to" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                Form E-posta Alıcısı
                <span class="text-danger-600 dark:text-danger-400">*</span>
            </label>
            <x-filament::input
                id="contact_form_to"
                type="email"
                wire:model.blur="contact_form_to"
                class="mt-2"
            />
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">İletişim formundan gelen mesajlar bu adrese gönderilir.</p>
            @error('contact_form_to')
                <p class="fi-fo-field-label-error-label text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-filament-panels::page>
