<div class="w-full max-w-3xl mx-auto px-4 sm:px-0 space-y-6" x-data="productCheck()">
    <style>
        @keyframes scanSweep {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }
        .scan-sweep {
            animation: scanSweep 2s ease-in-out infinite;
        }
        [x-cloak] { display: none !important; }
    </style>

    @php $product = $item->batch->product; @endphp

    {{-- Digital Content --}}
    @if($product->type === 'digital' && ($product->digital_file_path || $product->youtube_url))
        <div class="glass rounded-2xl overflow-hidden animate-fade-in" x-data="{ mediaOpen: false }">
            <div class="p-8 text-center">
                @if($product->digital_content_type === 'video')
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-5xl text-primary-600">play_circle</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Video İçerik</h3>
                    <p class="text-sm text-gray-500 mt-1 mb-6">Bu ürün bir video içeriği içermektedir.</p>
                    <button @click="mediaOpen = true"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl transition-colors">
                        <span class="material-symbols-outlined text-lg">play_arrow</span>
                        İzle
                    </button>
                @else
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-5xl text-red-600">picture_as_pdf</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">PDF Belge</h3>
                    <p class="text-sm text-gray-500 mt-1 mb-6">Bu ürün bir PDF belgesi içermektedir.</p>
                    <button @click="mediaOpen = true"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl transition-colors">
                        <span class="material-symbols-outlined text-lg">visibility</span>
                        Görüntüle
                    </button>
                @endif
            </div>

            <template x-teleport="body">
                <div x-show="mediaOpen"
                     x-cloak
                     x-trap.noscroll="mediaOpen"
                     class="fixed inset-0 z-[9999] bg-black flex items-center justify-center"
                     x-on:keydown.escape.window="mediaOpen = false">
                    <button @click="mediaOpen = false"
                            class="absolute top-4 right-4 z-20 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors">
                        <span class="material-symbols-outlined text-3xl">close</span>
                    </button>

                    @if($product->digital_content_type === 'video')
                        @if($product->youtube_embed_url)
                            <iframe src="{{ $product->youtube_embed_url }}?autoplay=1&rel=0"
                                    class="w-full h-full"
                                    allow="autoplay; fullscreen; accelerometer; gyroscope"
                                    allowfullscreen>
                            </iframe>
                        @elseif($product->digital_file_path)
                            <video controls autoplay class="w-full h-full object-contain" playsinline>
                                <source src="{{ Storage::url($product->digital_file_path) }}" type="video/mp4">
                            </video>
                        @endif
                    @elseif($product->digital_content_type === 'pdf' && $product->digital_file_path)
                        <iframe src="{{ Storage::url($product->digital_file_path) }}#view=fitH"
                                class="w-full h-full"
                                allowfullscreen>
                        </iframe>
                    @endif
                </div>
            </template>
        </div>
    @endif

    {{-- Verification Card --}}
    <div class="glass rounded-2xl overflow-hidden animate-fade-in">
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-white text-xl">verified</span>
            <span class="text-white font-semibold">Orijinal Ürün</span>
        </div>

        <div class="p-6 space-y-6">
            {{-- Image + Basic Info --}}
            <div class="flex flex-col sm:flex-row gap-6">
                @if($product->image)
                    <div class="w-full sm:w-48 h-48 flex-shrink-0 rounded-xl overflow-hidden bg-gray-100">
                        <img src="{{ Storage::url($product->image) }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-primary-600 font-medium uppercase tracking-wider">{{ $product->company->name }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $product->name }}</h1>
                    @if($product->description)
                        <div class="mt-3 prose prose-sm max-w-none text-gray-600">
                            {!! $product->description !!}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Details Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($product->serial_number)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Seri Numaras&imath;</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $product->serial_number }}</p>
                    </div>
                @endif
                @if($product->expiration_date)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Son Kullanma Tarihi</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $product->expiration_date->format('d.m.Y') }}</p>
                    </div>
                @endif
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Parti Numaras&imath;</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $item->batch->batch_number }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Benzersiz Kod</p>
                    <p class="text-sm font-mono font-semibold text-gray-900 mt-1 break-all">{{ $item->uuid }}</p>
                </div>
            </div>

            {{-- Social Media --}}
            @php
                $socials = [
                    'web'    => ['url' => $product->website_url,    'label' => 'Web Sitesi',    'icon' => 'language',     'class' => 'hover:bg-gray-200 text-gray-600'],
                    'insta'  => ['url' => $product->instagram_url,  'label' => 'Instagram',      'icon' => 'photo_camera', 'class' => 'hover:bg-pink-100 text-pink-600'],
                    'fb'     => ['url' => $product->facebook_url,   'label' => 'Facebook',       'icon' => 'facebook',     'class' => 'hover:bg-blue-100 text-blue-600'],
                    'tw'     => ['url' => $product->twitter_url,    'label' => 'Twitter / X',    'icon' => null,           'class' => 'hover:bg-gray-200 text-gray-900'],
                    'tt'     => ['url' => $product->tiktok_url,     'label' => 'TikTok',         'icon' => 'music_note',   'class' => 'hover:bg-gray-200 text-gray-900'],
                    'in'     => ['url' => $product->linkedin_url,   'label' => 'LinkedIn',       'icon' => 'business',     'class' => 'hover:bg-blue-100 text-blue-700'],
                ];
                $hasSocials = collect($socials)->filter(fn($s) => $s['url'])->isNotEmpty();
            @endphp

            @if($hasSocials)
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-3">Sosyal Medya</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($socials as $key => $social)
                            @if($social['url'])
                                <a href="{{ $social['url'] }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 transition-colors text-sm font-medium {{ $social['class'] }}">
                                    @if($key === 'tw')
                                        <span class="font-bold">𝕏</span>
                                    @else
                                        <span class="material-symbols-outlined text-sm">{{ $social['icon'] }}</span>
                                    @endif
                                    {{ $social['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Scan Status --}}
    @if($scanSaved)
        <div class="glass rounded-2xl p-6 text-center animate-fade-in">
            <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl text-green-600">check_circle</span>
            </div>
            <p class="text-green-700 font-semibold">Bu ürün başarıyla doğrulandı.</p>
            <p class="text-sm text-gray-500 mt-1">Doğrulama kaydınız oluşturuldu.</p>
        </div>
    @endif
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productCheck', () => ({
            init() {
                this.getLocation();
            },
            getLocation() {
                if (!navigator.geolocation) return;
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        Livewire.dispatch('saveScan', {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        });
                    },
                    () => {
                        Livewire.dispatch('saveScan', { lat: null, lng: null });
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        }));
    });
</script>
