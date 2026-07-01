<x-layouts.public>
    <x-slot name="title">İletişim - {{ \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com') }}</x-slot>
    <x-slot name="description">Bize ulaşın. Sorularınız, önerileriniz veya destek talepleriniz için iletişim formunu doldurabilirsiniz.</x-slot>

    <div class="max-w-screen-xl mx-auto px-6 py-16 md:py-24">
        <!-- Header -->
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-primary-container mb-4">Bize Ulaşın</h1>
            <p class="text-lg text-on-surface-variant max-w-2xl mx-auto">Sorularınız, önerileriniz veya kurumsal çözümlerimiz hakkında bilgi almak için formu doldurabilir veya iletişim bilgilerimizden bize ulaşabilirsiniz.</p>
        </div>

        @if (session('success'))
            <div class="max-w-2xl mx-auto mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-12 lg:gap-20">
            <!-- Contact Info -->
            <div class="space-y-10">
                <div class="bg-surface-container-low p-8 rounded-3xl">
                    <h3 class="text-xl font-bold text-primary-container mb-6">İletişim Bilgileri</h3>
                    
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center flex-shrink-0 shadow-sm text-secondary">
                                <span class="material-symbols-outlined">location_on</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-on-surface mb-1">Adres</h4>
                                <p class="text-on-surface-variant text-sm">{{ \App\Helpers\SettingHelper::get('contact_address', 'Teknoloji Geliştirme Bölgesi, Ar-Ge Binası No: 1, İstanbul') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center flex-shrink-0 shadow-sm text-secondary">
                                <span class="material-symbols-outlined">mail</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-on-surface mb-1">E-Posta</h4>
                                <p class="text-on-surface-variant text-sm">{{ \App\Helpers\SettingHelper::get('contact_email', 'info@urundogrula.com') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center flex-shrink-0 shadow-sm text-secondary">
                                <span class="material-symbols-outlined">call</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-on-surface mb-1">Telefon</h4>
                                <p class="text-on-surface-variant text-sm">{{ \App\Helpers\SettingHelper::get('contact_phone', '+90 (850) 123 45 67') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-primary-container p-8 rounded-3xl text-white">
                    <h3 class="text-xl font-bold mb-4">Destek Merkezi</h3>
                    <p class="text-on-primary-container text-sm mb-6">Hızlı çözümler için Sıkça Sorulan Sorular veya Dokümantasyon merkezimizi ziyaret edebilirsiniz.</p>
                    <a href="#" class="inline-flex items-center gap-2 text-secondary-fixed font-bold hover:underline underline-offset-4">
                        Destek Merkezine Git <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-surface-container-lowest p-8 md:p-10 rounded-3xl shadow-[0_20px_40px_rgba(11,28,48,0.05)] border border-outline-variant/20">
                <h3 class="text-2xl font-bold text-primary-container mb-6">Mesaj Gönderin</h3>
                
                <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2">Adınız Soyadınız</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-surface-container-low border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-secondary/50 text-on-surface @error('name') ring-2 ring-error/50 @enderror" placeholder="John Doe" required>
                            @error('name') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-2">Telefon Numarası</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" class="w-full bg-surface-container-low border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-secondary/50 text-on-surface" placeholder="05XX XXX XX XX">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-on-surface mb-2">E-Posta Adresi</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-surface-container-low border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-secondary/50 text-on-surface @error('email') ring-2 ring-error/50 @enderror" placeholder="ornek@sirket.com" required>
                        @error('email') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-on-surface mb-2">Mesajınız</label>
                        <textarea name="message" rows="5" class="w-full bg-surface-container-low border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-secondary/50 text-on-surface resize-none @error('message') ring-2 ring-error/50 @enderror" placeholder="Size nasıl yardımcı olabiliriz?" required>{{ old('message') }}</textarea>
                        @error('message') <p class="text-error text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="w-full bg-secondary text-white font-bold text-lg py-4 rounded-xl shadow-lg hover:shadow-secondary/30 transition-all active:scale-[0.98]">
                        Mesajı Gönder
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.public>
