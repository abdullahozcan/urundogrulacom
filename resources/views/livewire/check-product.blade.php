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

    {{-- Scan Status (top) --}}
    @if($scanSaved)
        <div class="rounded-2xl px-4 py-3 sm:p-5 text-center animate-fade-in bg-green-600">
            <div class="flex items-center justify-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-lg sm:text-xl text-white">check_circle</span>
                </div>
                <p class="text-white font-semibold text-sm sm:text-base">Bu ürün başarıyla doğrulandı.</p>
            </div>
            <p class="text-xs text-white/70 mt-1">Doğrulama kaydınız oluşturuldu.</p>
        </div>
    @endif

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
                             alt="{{ $product->getName() }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-primary-600 font-medium uppercase tracking-wider">{{ $product->company->name }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $product->getName() }}</h1>
                    @if($product->getDescription())
                        <div class="mt-3 prose prose-sm max-w-none text-gray-600">
                            {{ $product->getDescription() }}
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

            @if($product->documents->isNotEmpty())
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-3">Belgeler</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($product->documents as $doc)
                            <a href="{{ Storage::url($doc->file_path) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-100 hover:bg-primary-100 transition-colors text-sm font-medium text-gray-700 hover:text-primary-700">
                                <span class="material-symbols-outlined text-sm">description</span>
                                {{ $doc->original_name ?? basename($doc->file_path) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Review Card --}}
    <div class="glass rounded-2xl overflow-hidden animate-fade-in" x-data="{ hoverRating: 0 }" x-init="$watch('hoverRating', () => {})">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Ürün Değerlendirmesi</h2>
                @php
                    $avgRating = $product->reviews->avg('rating');
                    $reviewsCount = $product->reviews->count();
                @endphp
                @if($reviewsCount > 0)
                    <div class="flex items-center gap-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="material-symbols-outlined text-sm {{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-300' }}">star</span>
                            @endfor
                        </div>
                        <span class="text-sm text-gray-500">{{ number_format($avgRating, 1) }} ({{ $reviewsCount }})</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($existingReview)
                {{-- Kullanıcının kendi yorumu --}}
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center gap-1 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="material-symbols-outlined text-lg {{ $i <= $existingReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">star</span>
                        @endfor
                    </div>
                    @if($existingReview->comment)
                        <p class="text-sm text-gray-700">{{ $existingReview->comment }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">
                        @if($existingReview->is_approved)
                            ✅ Onaylandı
                        @else
                            ⏳ Onay bekliyor
                        @endif
                    </p>
                </div>
            @elseif(!$reviewSaved)
                {{-- Yorum formu --}}
                <form wire:submit="submitReview">
                    <div class="flex items-center gap-1 mb-4">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button"
                                    x-on:click="$wire.set('rating', {{ $i }})"
                                    x-on:mouseenter="hoverRating = {{ $i }}"
                                    x-on:mouseleave="hoverRating = 0"
                                    class="text-3xl focus:outline-none transition-colors hover:scale-110">
                                <span class="material-symbols-outlined text-3xl"
                                      :class="(hoverRating >= {{ $i }} || $wire.rating >= {{ $i }}) ? 'text-yellow-400' : 'text-gray-300'">
                                    star
                                </span>
                            </button>
                        @endfor
                    </div>

                    <textarea wire:model="comment"
                              rows="3"
                              placeholder="Yorumunuz..."
                              class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500 resize-none text-sm"></textarea>
                    @error('comment') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                    <x-filament::button type="submit" class="mt-4" size="sm">
                        Gönder
                    </x-filament::button>
                </form>
            @else
                <p class="text-sm text-green-600 font-medium">Yorumunuz başarıyla gönderildi. Onaylandığında görünecektir.</p>
            @endif

            {{-- Diğer onaylı yorumlar --}}
            @php $approvedReviews = $product->approvedReviews()->latest()->get(); @endphp
            @if($approvedReviews->isNotEmpty())
                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-3">Diğer Yorumlar</p>
                    <div class="space-y-3">
                        @foreach($approvedReviews as $review)
                            <div class="bg-gray-50 rounded-xl p-3">
                                <div class="flex items-center gap-1 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">star</span>
                                    @endfor
                                </div>
                                @if($review->comment)
                                    <p class="text-sm text-gray-700">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productCheck', () => ({
            init() {
                this.setDeviceCookie();
                this.getLocation();
            },
            setDeviceCookie() {
                const name = 'device_token';
                const value = @js($deviceToken);
                if (!document.cookie.split('; ').find(row => row.startsWith(name + '='))) {
                    document.cookie = name + '=' + value + '; path=/; max-age=' + (60 * 60 * 24 * 365 * 10);
                }
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
