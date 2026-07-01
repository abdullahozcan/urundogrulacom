<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <div>
            <label for="contact_address" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                Adres
                <span class="text-danger-600 dark:text-danger-400">*</span>
            </label>
            <x-filament::input.wrapper
                wire:key="contact_address"
                :valid="! $errors->has('contact_address')"
                class="mt-2"
            >
                <x-filament::input
                    id="contact_address"
                    type="text"
                    wire:model.blur="contact_address"
                />
            </x-filament::input.wrapper>
            @error('contact_address')
                <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="contact_email" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    E-posta (Görünen)
                    <span class="text-danger-600 dark:text-danger-400">*</span>
                </label>
                <x-filament::input.wrapper
                    wire:key="contact_email"
                    :valid="! $errors->has('contact_email')"
                    class="mt-2"
                >
                    <x-filament::input
                        id="contact_email"
                        type="email"
                        wire:model.blur="contact_email"
                    />
                </x-filament::input.wrapper>
                @error('contact_email')
                    <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="contact_phone" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Telefon
                    <span class="text-danger-600 dark:text-danger-400">*</span>
                </label>
                <x-filament::input.wrapper
                    wire:key="contact_phone"
                    :valid="! $errors->has('contact_phone')"
                    class="mt-2"
                >
                    <x-filament::input
                        id="contact_phone"
                        type="tel"
                        wire:model.blur="contact_phone"
                    />
                </x-filament::input.wrapper>
                @error('contact_phone')
                    <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="contact_form_to" class="fi-fo-field-label-label text-sm font-medium leading-6 text-gray-950 dark:text-white">
                Form E-posta Alıcısı
                <span class="text-danger-600 dark:text-danger-400">*</span>
            </label>
            <x-filament::input.wrapper
                wire:key="contact_form_to"
                :valid="! $errors->has('contact_form_to')"
                class="mt-2"
            >
                <x-filament::input
                    id="contact_form_to"
                    type="email"
                    wire:model.blur="contact_form_to"
                />
            </x-filament::input.wrapper>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">İletişim formundan gelen mesajlar bu adrese gönderilir.</p>
            @error('contact_form_to')
                <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-filament-panels::page>
