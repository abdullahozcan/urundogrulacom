<x-layouts.public>
    <x-slot name="title">Sistem Hatası (500) - {{ \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com') }}</x-slot>

    <div class="flex-grow flex items-center justify-center min-h-[60vh] px-6 py-24">
        <div class="text-center max-w-2xl mx-auto">
            <div class="inline-flex items-center justify-center w-24 h-24 md:w-32 md:h-32 rounded-full bg-error/10 text-error mb-8">
                <span class="material-symbols-outlined text-6xl md:text-7xl">warning</span>
            </div>
            
            <h1 class="text-6xl md:text-8xl font-black text-primary-container tracking-tighter mb-4">500</h1>
            <h2 class="text-2xl md:text-3xl font-bold text-on-surface mb-6">Sunucu kaynaklı bir hata oluştu.</h2>
            
            <p class="text-on-surface-variant text-lg mb-10 max-w-md mx-auto">
                Sistemimizde geçici bir sorun var. Teknik ekibimiz durumdan haberdar edildi ve çözmek için çalışıyor. Lütfen daha sonra tekrar deneyin.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/" class="bg-secondary text-white font-bold px-8 py-4 rounded-xl shadow-lg hover:shadow-secondary/30 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">refresh</span>
                    Anasayfayı Yenile
                </a>
                <a href="{{ route('contact') }}" class="bg-surface-container-high text-on-surface font-bold px-8 py-4 rounded-xl shadow-sm hover:bg-surface-variant transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">report_problem</span>
                    Sorunu Bildir
                </a>
            </div>
        </div>
    </div>
</x-layouts.public>
