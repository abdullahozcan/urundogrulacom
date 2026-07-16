<x-layouts.public>
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 md:pt-20 pb-16 md:pb-32">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-6 grid md:grid-cols-2 gap-8 md:gap-12 items-center">
            <div class="z-10 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high text-on-secondary-fixed-variant text-[10px] font-bold uppercase tracking-widest mb-6 mx-auto md:mx-0">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                    {{ \App\Helpers\SettingHelper::get('hero_badge', 'Dijital Noter Güvencesi') }}
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tighter text-primary-container mb-6 leading-[1.1] md:leading-[0.95]">
                    {!! \App\Helpers\SettingHelper::get('hero_title', 'Ürününüzü <span class="text-secondary">Doğrulayın</span>') !!}
                </h1>
                <p class="text-on-surface-variant text-base sm:text-lg max-w-lg mx-auto md:mx-0 mb-8 md:mb-10 leading-relaxed">
                    {{ \App\Helpers\SettingHelper::get('hero_description', 'Ürün sahteciliğine karşı en güçlü kalkan. QR kod teknolojisi ile saniyeler içinde ürünün orijinalliğini teyit edin, güvenle kullanın.') }}
                </p>
                <div class="flex flex-col sm:flex-row flex-wrap gap-4 justify-center md:justify-start">
                    <button class="px-6 md:px-8 py-3 md:py-4 bg-gradient-to-r from-secondary-container to-secondary text-white rounded-xl font-bold text-base md:text-lg shadow-xl hover:shadow-secondary-container/20 transition-all active:scale-95 w-full sm:w-auto">
                        {{ \App\Helpers\SettingHelper::get('hero_button_1', 'Hemen Tara') }}
                    </button>
                    <button class="px-6 md:px-8 py-3 md:py-4 bg-surface-container-lowest text-primary-container rounded-xl font-bold text-base md:text-lg border border-outline-variant/15 hover:bg-surface-container-low transition-all active:scale-95 w-full sm:w-auto">
                        {{ \App\Helpers\SettingHelper::get('hero_button_2', 'Marka Çözümleri') }}
                    </button>
                </div>
            </div>
            <div class="relative mt-8 md:mt-0">
                <div class="absolute -inset-10 bg-secondary/5 blur-[80px] md:blur-[120px] rounded-full"></div>
                <div class="relative rounded-3xl overflow-hidden bg-surface-container-low p-3 md:p-4 shadow-2xl">
                    <img alt="Ürün Doğrulama Deneyimi" class="w-full aspect-square object-cover rounded-2xl shadow-inner" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAF_ARGj7KQb19I6eL55OY2qLV4W57D5ZjtHsvSTVR8fVohfPV6OE3q_bMEbpKHkOvfV9bx5r74DqvweSkNrGBnQanYp4-b3a83FiuoQZLEX0xgCxDALynKk6ZF5skVIzgsD9w5YCyDU7Gkdl4yktIaBPkHUsVWPyz_Y6QaV5xdVrYig-tgrmJjTG2XWxmmiMmIgvdnsxyPM7yng41U0lju6GO67qrW944TwtwwaJ3UzVOPBQx98DIF-7gWBSHYKrCuMpa_Qpcr" />
                    <div class="absolute bottom-6 md:bottom-10 left-6 right-6 md:left-10 md:right-10 glass-nav p-4 md:p-6 rounded-2xl border border-white/20 shadow-lg">
                        <div class="flex items-center gap-3 md:gap-4">
                            <div class="w-10 h-10 md:w-12 md:h-12 bg-tertiary-container rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-tertiary-fixed-dim text-lg md:text-xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            </div>
                            <div>
                                <div class="text-[9px] md:text-[10px] font-bold text-on-primary-container uppercase tracking-wider">Durum</div>
                                <div class="text-primary-container font-bold text-base md:text-xl tracking-tight">Orijinal Ürün</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section (3 Steps) -->
    <section class="bg-surface-container-low py-16 md:py-24">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-6">
            <div class="text-center mb-12 md:mb-20">
                <h2 class="text-xs md:text-sm font-bold text-secondary uppercase tracking-[0.2em] mb-3 md:mb-4">{{ \App\Helpers\SettingHelper::get('process_badge', 'Sistem Nasıl Çalışır?') }}</h2>
                <p class="text-3xl md:text-4xl font-extrabold tracking-tight text-primary-container">{{ \App\Helpers\SettingHelper::get('process_title', 'Üç Basit Adımda Güven') }}</p>
            </div>
            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-12">
                <!-- Step 1 -->
                <div class="bg-surface-container-lowest p-8 md:p-10 rounded-3xl group hover:bg-primary-container transition-colors duration-500">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-surface-container-high rounded-2xl flex items-center justify-center mb-6 md:mb-8 group-hover:bg-secondary-container transition-colors">
                        <span class="material-symbols-outlined text-secondary text-2xl md:text-3xl group-hover:text-white">{{ \App\Helpers\SettingHelper::get('process_step1_icon', 'qr_code_scanner') }}</span>
                    </div>
                    <h3 class="text-xl md:text-2xl font-bold text-primary-container mb-3 md:mb-4 group-hover:text-white transition-colors">{{ \App\Helpers\SettingHelper::get('process_step1_title', '1. Tara') }}</h3>
                    <p class="text-sm md:text-base text-on-surface-variant group-hover:text-on-primary-container transition-colors leading-relaxed">
                        {{ \App\Helpers\SettingHelper::get('process_step1_desc', 'Ürün üzerindeki özel QR kodu akıllı telefonunuzun kamerasıyla saniyeler içinde okutun.') }}
                    </p>
                </div>
                <!-- Step 2 -->
                <div class="bg-surface-container-lowest p-8 md:p-10 rounded-3xl group hover:bg-primary-container transition-colors duration-500">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-surface-container-high rounded-2xl flex items-center justify-center mb-6 md:mb-8 group-hover:bg-secondary-container transition-colors">
                        <span class="material-symbols-outlined text-secondary text-2xl md:text-3xl group-hover:text-white">{{ \App\Helpers\SettingHelper::get('process_step2_icon', 'verified_user') }}</span>
                    </div>
                    <h3 class="text-xl md:text-2xl font-bold text-primary-container mb-3 md:mb-4 group-hover:text-white transition-colors">{{ \App\Helpers\SettingHelper::get('process_step2_title', '2. Doğrula') }}</h3>
                    <p class="text-sm md:text-base text-on-surface-variant group-hover:text-on-primary-container transition-colors leading-relaxed">
                        {{ \App\Helpers\SettingHelper::get('process_step2_desc', 'Sistemimiz veritabanındaki şifreli kayıtları saniyeler içinde kontrol ederek onay versin.') }}
                    </p>
                </div>
                <!-- Step 3 -->
                <div class="bg-surface-container-lowest p-8 md:p-10 rounded-3xl group hover:bg-primary-container transition-colors duration-500">
                    <div class="w-14 h-14 md:w-16 md:h-16 bg-surface-container-high rounded-2xl flex items-center justify-center mb-6 md:mb-8 group-hover:bg-secondary-container transition-colors">
                        <span class="material-symbols-outlined text-secondary text-2xl md:text-3xl group-hover:text-white">{{ \App\Helpers\SettingHelper::get('process_step3_icon', 'shopping_bag') }}</span>
                    </div>
                    <h3 class="text-xl md:text-2xl font-bold text-primary-container mb-3 md:mb-4 group-hover:text-white transition-colors">{{ \App\Helpers\SettingHelper::get('process_step3_title', '3. Güvenle Kullan') }}</h3>
                    <p class="text-sm md:text-base text-on-surface-variant group-hover:text-on-primary-container transition-colors leading-relaxed">
                        {{ \App\Helpers\SettingHelper::get('process_step3_desc', 'Ürününüzün orijinalliğinden emin olun ve markanın sunduğu özel avantajlardan yararlanın.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 md:py-32">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 md:mb-16 gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-xs md:text-sm font-bold text-on-tertiary-container uppercase tracking-[0.2em] mb-3 md:mb-4">{{ \App\Helpers\SettingHelper::get('bento_badge', 'Marka Çözümleri') }}</h2>
                    <p class="text-3xl md:text-5xl font-extrabold tracking-tighter text-primary-container">{!! \App\Helpers\SettingHelper::get('bento_title', 'Sahteciliğe Son Verin, <br class="hidden md:block" />Marka Değerinizi Koruyun') !!}</p>
                </div>
                <a class="flex items-center gap-2 text-secondary font-bold hover:underline underline-offset-4 text-sm md:text-base" href="#">
                    {{ \App\Helpers\SettingHelper::get('bento_link', 'Tüm Özellikleri Keşfet') }} <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-4 md:gap-6 md:auto-rows-[280px]">
                <!-- Large Feature -->
                <div class="sm:col-span-2 md:col-span-8 bg-primary-container rounded-3xl p-8 md:p-10 flex flex-col justify-end relative overflow-hidden group min-h-[240px] md:min-h-0">
                    <div class="absolute top-0 right-0 p-6 md:p-8">
                        <span class="material-symbols-outlined text-tertiary-fixed text-5xl md:text-7xl opacity-20" style="font-variation-settings: 'FILL' 1;">shield_with_heart</span>
                    </div>
                    <h3 class="text-white text-2xl md:text-3xl font-bold mb-3 md:mb-4 relative z-10">{{ \App\Helpers\SettingHelper::get('bento_box1_title', '%100 Marka Koruması') }}</h3>
                    <p class="text-on-primary-container text-sm md:text-lg max-w-md relative z-10">{{ \App\Helpers\SettingHelper::get('bento_box1_desc', 'Tedarik zincirindeki her bir ürünü benzersiz kimliklerle takip edin ve pazar güvenliğini sağlayın.') }}</p>
                    <div class="absolute inset-0 bg-gradient-to-br from-secondary/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <!-- Small Feature 1 -->
                <div class="md:col-span-4 bg-tertiary-container rounded-3xl p-8 md:p-10 flex flex-col justify-end relative overflow-hidden min-h-[200px] md:min-h-0">
                    <span class="material-symbols-outlined text-tertiary-fixed-dim text-3xl md:text-4xl mb-3 md:mb-4" style="font-variation-settings: 'FILL' 1;">analytics</span>
                    <h3 class="text-white text-lg md:text-xl font-bold mb-2">{{ \App\Helpers\SettingHelper::get('bento_box2_title', 'Derin Analitik') }}</h3>
                    <p class="text-on-primary-container text-xs md:text-sm">{{ \App\Helpers\SettingHelper::get('bento_box2_desc', 'Ürünlerinizin nerede, ne zaman ve kim tarafından tarandığını anlık olarak izleyin.') }}</p>
                </div>
                <!-- Small Feature 2 -->
                <div class="md:col-span-4 bg-surface-container-high rounded-3xl p-8 md:p-10 flex flex-col justify-end relative overflow-hidden min-h-[200px] md:min-h-0">
                    <span class="material-symbols-outlined text-secondary text-3xl md:text-4xl mb-3 md:mb-4" style="font-variation-settings: 'FILL' 1;">loyalty</span>
                    <h3 class="text-primary-container text-lg md:text-xl font-bold mb-2">{{ \App\Helpers\SettingHelper::get('bento_box3_title', 'Sadakat Programı') }}</h3>
                    <p class="text-on-surface-variant text-xs md:text-sm">{{ \App\Helpers\SettingHelper::get('bento_box3_desc', 'Orijinal ürün alan müşterilerinize özel kampanya ve ödüller sunun.') }}</p>
                </div>
                <!-- Medium Feature -->
                <div class="sm:col-span-2 md:col-span-8 bg-surface-container-low rounded-3xl p-8 md:p-10 flex flex-col justify-end relative overflow-hidden min-h-[240px] md:min-h-0">
                    <div class="absolute inset-0 opacity-10">
                        <img alt="Data Vision" class="w-full h-full object-cover" src="{{ \App\Helpers\SettingHelper::get('bento_box4_image', 'https://lh3.googleusercontent.com/aida-public/AB6AXuCV3AUhchu_7gHI1pXumAHy89ibiDQgUijjUjB09NHSELt7s_M_JcR_7bkz8TmdBHYB8JfUzzQaZoo4JcaAO5Q1DIc61RvulxvEXYdZJtsQzIzdM7V4lr1ZT8EZMrksx50G9JCuWV09IXkKuWSKdqHBioAX220No37MCu3mWNoNrdLwdx6Hlgm9QBfS1PoqTQGCHmhqpRiJILVSpRyyeY5jT4hiE6zBTL7q2x52Fsq3nom_fxl9d_HpKlAggXMI6qNBfvuFJB4s') }}" />
                    </div>
                    <h3 class="text-primary-container text-2xl md:text-3xl font-bold mb-3 md:mb-4 relative z-10">{{ \App\Helpers\SettingHelper::get('bento_box4_title', 'Küresel Takip Ağı') }}</h3>
                    <p class="text-on-surface-variant text-sm md:text-lg max-w-lg relative z-10">{{ \App\Helpers\SettingHelper::get('bento_box4_desc', 'Dünya çapında ürün güvenliğini tek bir merkezden yönetin. Sınırları aşan koruma kalkanı.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blog / News Section -->
    @if($posts->count() > 0)
    <section class="bg-surface-container-low py-16 md:py-32">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-10 md:mb-16 gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-xs md:text-sm font-bold text-secondary uppercase tracking-[0.2em] mb-3 md:mb-4">{{ \App\Helpers\SettingHelper::get('blog_badge', 'Güncel Haberler') }}</h2>
                    <p class="text-3xl md:text-4xl font-extrabold tracking-tight text-primary-container">{{ \App\Helpers\SettingHelper::get('blog_title', 'Sektörel Gelişmeler ve <br class="hidden md:block"/>Güncellemeler') }}</p>
                </div>
                <a class="flex items-center gap-2 text-secondary font-bold hover:underline underline-offset-4 text-sm md:text-base" href="#">
                    {{ \App\Helpers\SettingHelper::get('blog_link', 'Tüm Yazıları Gör') }} <span class="material-symbols-outlined">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
                @foreach($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="group block bg-surface-container-lowest rounded-3xl overflow-hidden hover:shadow-[0_20px_40px_rgba(11,28,48,0.06)] transition-all duration-300">
                    <div class="aspect-[4/3] w-full overflow-hidden bg-surface-container-high relative">
                        @if($post->image)
                            <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-outline text-5xl opacity-20">image</span>
                            </div>
                        @endif
                        <div class="absolute top-4 left-4 bg-surface/90 backdrop-blur text-primary-container text-[10px] md:text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                            {{ $post->created_at->format('d M Y') }}
                        </div>
                    </div>
                    <div class="p-6 md:p-8">
                        <h3 class="text-lg md:text-xl font-bold text-primary-container mb-2 md:mb-3 group-hover:text-secondary transition-colors line-clamp-2">
                            {{ $post->title }}
                        </h3>
                        <div class="text-on-surface-variant text-xs md:text-sm leading-relaxed line-clamp-3">
                            {{ strip_tags($post->content) }}
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- CTA Section -->
    <section class="max-w-screen-2xl mx-auto px-4 md:px-6 pb-12 md:pb-20">
        <div class="bg-secondary p-8 md:p-16 rounded-3xl md:rounded-[2.5rem] flex flex-col items-center text-center relative overflow-hidden">
            <div class="absolute -top-12 -left-12 md:-top-24 md:-left-24 w-32 h-32 md:w-64 md:h-64 bg-white/10 rounded-full blur-2xl md:blur-3xl"></div>
            <div class="absolute -bottom-12 -right-12 md:-bottom-24 md:-right-24 w-48 h-48 md:w-96 md:h-96 bg-primary-container/20 rounded-full blur-2xl md:blur-3xl"></div>
            <h2 class="text-white text-3xl sm:text-4xl md:text-5xl font-black tracking-tight mb-6 md:mb-8 relative z-10 leading-tight">
                {!! \App\Helpers\SettingHelper::get('cta_title', 'Geleceğin Güven Standartlarını <br class="hidden md:block" />Bugün Belirleyin') !!}
            </h2>
            <div class="flex flex-col sm:flex-row justify-center gap-4 md:gap-6 relative z-10 w-full sm:w-auto">
                <button class="bg-white text-secondary px-8 md:px-10 py-4 md:py-5 rounded-2xl font-bold text-base md:text-xl shadow-lg hover:bg-surface-container-lowest transition-all active:scale-95 w-full sm:w-auto">
                    {{ \App\Helpers\SettingHelper::get('cta_button_1', 'Ücretsiz Demoya Başla') }}
                </button>
                <button class="bg-primary-container text-white px-8 md:px-10 py-4 md:py-5 rounded-2xl font-bold text-base md:text-xl shadow-lg border border-white/10 transition-all active:scale-95 w-full sm:w-auto">
                    {{ \App\Helpers\SettingHelper::get('cta_button_2', 'Bize Ulaşın') }}
                </button>
            </div>
        </div>
    </section>

    <!-- Partner Logos Slider -->
    @if($partners->count() > 0)
    <section class="w-full bg-white py-12 md:py-16 border-t border-slate-100 overflow-hidden relative">
        <div class="max-w-screen-2xl mx-auto px-6 mb-8 text-center">
            <p class="text-sm font-bold text-slate-400 tracking-widest uppercase">BİZİMLE ÇALIŞAN GÜÇLÜ MARKALAR</p>
        </div>
        
        <!-- Mask for smooth fade at edges -->
        <div class="absolute inset-y-0 left-0 w-24 md:w-48 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute inset-y-0 right-0 w-24 md:w-48 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>

        <div class="flex overflow-hidden group">
            <div class="flex space-x-12 md:space-x-24 animate-infinite-scroll group-hover:[animation-play-state:paused] items-center">
                <!-- Double the logos to ensure seamless looping -->
                @foreach($partners as $partner)
                    <div class="flex-shrink-0 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                        <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}" class="h-10 md:h-16 w-auto object-contain max-w-[150px] md:max-w-[200px]">
                    </div>
                @endforeach
                @foreach($partners as $partner)
                    <div class="flex-shrink-0 grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                        <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}" class="h-10 md:h-16 w-auto object-contain max-w-[150px] md:max-w-[200px]">
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</x-layouts.public>
