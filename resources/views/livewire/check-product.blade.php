<div class="max-w-screen-2xl mx-auto space-y-6 px-4 sm:px-6 py-6" x-data="productCheck()">
    @php $product = $item->batch->product; @endphp

    {{-- Digital Content Modal --}}
    @if($product->type === 'digital' && ($product->digital_file_path || $product->youtube_url))
        <template x-teleport="body">
            <div x-show="mediaOpen" x-cloak x-trap.noscroll="mediaOpen"
                 class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center"
                 x-on:keydown.escape.window="mediaOpen = false">
                <button @click="mediaOpen = false"
                        class="absolute top-4 right-4 z-20 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors">
                    <span class="material-symbols-outlined text-3xl">close</span>
                </button>
                @if($product->digital_content_type === 'video')
                    @if($product->youtube_embed_url)
                        <iframe src="{{ $product->youtube_embed_url }}?autoplay=1&rel=0"
                                class="w-full h-full" allow="autoplay; fullscreen; accelerometer; gyroscope" allowfullscreen>
                        </iframe>
                    @elseif($product->digital_file_path)
                        <video controls autoplay class="w-full h-full object-contain" playsinline>
                            <source src="{{ Storage::url($product->digital_file_path) }}" type="video/mp4">
                        </video>
                    @endif
                @elseif($product->digital_content_type === 'pdf' && $product->digital_file_path)
                    <iframe src="{{ Storage::url($product->digital_file_path) }}#view=fitH"
                            class="w-full h-full" allowfullscreen>
                    </iframe>
                @endif
            </div>
        </template>
    @endif

    {{-- Hero: Verification Status --}}
    <section class="relative bg-white border border-deep-navy/10 rounded-xl overflow-hidden p-6 md:p-12 flex flex-col md:flex-row items-center gap-6 md:gap-12 shadow-sm">
        <div class="absolute inset-0 bg-grid-subtle pointer-events-none opacity-50"></div>
        <div class="relative z-10 flex-shrink-0">
            <div class="w-32 h-32 md:w-48 md:h-48 rounded-full bg-success-green/10 flex items-center justify-center inner-glow-success border-4 border-success-green/20"
                 style="animation: badgePulse 4s infinite ease-in-out;">
                <span class="material-symbols-outlined text-success-green !text-[64px] md:!text-[96px]" style="font-variation-settings: 'wght' 700;">verified</span>
            </div>
        </div>
        <div class="relative z-10 text-center md:text-left">
            @if($scanSaved)
                <span class="inline-block bg-success-green/10 text-success-green font-bold text-xs px-3 py-1 rounded-full mb-2 uppercase tracking-widest">
                    Doğrulanmış Kayıt
                </span>
                <h1 class="text-3xl md:text-4xl font-bold text-deep-navy mb-1 tracking-tight">Ürün Doğrulandı</h1>
                @if($redirectUrl)
                    <p class="text-on-surface-variant flex items-center justify-center md:justify-start gap-1 text-sm">
                        <span class="material-symbols-outlined text-sm">open_in_new</span>
                        Yönlendiriliyorsunuz: <span class="font-bold break-all">{{ $redirectUrl }}</span>
                    </p>
                @else
                    <p class="text-on-surface-variant flex items-center justify-center md:justify-start gap-1 text-sm">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        Doğrulama Zamanı: <span class="font-bold">{{ now()->locale(app()->getLocale())->isoFormat('DD MMMM YYYY, HH:mm:ss') }} (GMT+3)</span>
                    </p>
                @endif
            @else
                <span class="inline-block bg-status-warning/10 text-status-warning font-bold text-xs px-3 py-1 rounded-full mb-2 uppercase tracking-widest">
                    Doğrulanıyor
                </span>
                <h1 class="text-3xl md:text-4xl font-bold text-deep-navy mb-1 tracking-tight">Ürün Taranıyor</h1>
                <p class="text-on-surface-variant text-sm">Konum bilginiz alınıyor, lütfen bekleyin...</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-2 justify-center md:justify-start">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-surface-container rounded border border-deep-navy/5">
                    <span class="material-symbols-outlined text-trust-blue text-[18px]">security</span>
                    <span class="text-xs font-semibold tracking-wider text-deep-navy">Sertifika ID: #{{ substr($item->uuid, 0, 4) }}-{{ substr($item->uuid, 4, 4) }}-{{ substr($item->uuid, 8, 4) }}-{{ substr($item->uuid, 12, 4) }}</span>
                </div>
            </div>
        </div>
        <div class="hidden xl:block absolute right-12 top-1/2 -translate-y-1/2 opacity-10">
            <span class="material-symbols-outlined !text-[200px] text-deep-navy">verified_user</span>
        </div>
    </section>

    {{-- Action Engine Banner --}}
    @if($scanSaved && $actionContent)
        @php $isWarning = $actionContent['style'] === 'warning'; @endphp
        <section class="{{ $isWarning ? 'bg-status-warning/10 border-status-warning/30' : 'bg-trust-blue/10 border-trust-blue/30' }} border rounded-xl px-6 py-4 flex items-start gap-3 shadow-sm">
            <span class="material-symbols-outlined {{ $isWarning ? 'text-status-warning' : 'text-trust-blue' }} flex-shrink-0">
                {{ $isWarning ? 'warning' : 'campaign' }}
            </span>
            <div>
                @if($actionContent['title'])
                    <h3 class="font-semibold {{ $isWarning ? 'text-status-warning' : 'text-trust-blue' }}">{{ $actionContent['title'] }}</h3>
                @endif
                @if($actionContent['body'])
                    <p class="text-sm text-on-surface">{{ $actionContent['body'] }}</p>
                @endif
            </div>
        </section>
    @endif

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left: Product Information (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Product Info Table --}}
            <div class="bg-white border border-deep-navy/10 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-surface-ice border-b-2 border-trust-blue flex justify-between items-center">
                    <h2 class="font-semibold text-lg text-deep-navy">Ürün Bilgileri</h2>
                    <span class="material-symbols-outlined text-trust-blue">inventory_2</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <tbody>
                            <tr class="bg-surface-ice/50 border-b border-deep-navy/5">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant w-1/3">Ürün Adı</th>
                                <td class="px-6 py-4 font-semibold text-deep-navy">{{ $product->getName() }}</td>
                            </tr>
                            @if($product->serial_number)
                            <tr class="border-b border-deep-navy/5">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Seri Numarası</th>
                                <td class="px-6 py-4 font-mono font-bold text-trust-blue tracking-wider">{{ $product->serial_number }}</td>
                            </tr>
                            @endif
                            <tr class="bg-surface-ice/50 border-b border-deep-navy/5">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Üretici Firma</th>
                                <td class="px-6 py-4 font-semibold text-on-surface">{{ $product->company->name }}</td>
                            </tr>
                            <tr class="border-b border-deep-navy/5">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Parti Numarası</th>
                                <td class="px-6 py-4 font-mono text-on-surface">{{ $item->batch->batch_number }}</td>
                            </tr>
                            <tr class="bg-surface-ice/50 border-b border-deep-navy/5">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Benzersiz Kod</th>
                                <td class="px-6 py-4 font-mono text-on-surface break-all text-sm">{{ $item->uuid }}</td>
                            </tr>
                            <tr class="{{ $product->serial_number ? 'bg-surface-ice/50' : '' }}">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Son Kullanma / Garanti</th>
                                <td class="px-6 py-4 text-on-surface">{{ $product->expiration_date ? $product->expiration_date->format('d.m.Y') : 'Belirtilmemiş' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Documents Section --}}
            @if($product->documents->isNotEmpty())
                <div class="space-y-4">
                    <h2 class="font-semibold text-lg text-deep-navy px-1">Resmi Belgeler ve Sertifikalar</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($product->documents as $doc)
                            <div class="group bg-white border border-deep-navy/10 rounded-xl p-4 flex flex-col gap-3 hover:border-trust-blue transition-all cursor-pointer shadow-sm">
                                <div class="w-12 h-12 rounded bg-surface-container flex items-center justify-center text-trust-blue group-hover:bg-trust-blue group-hover:text-white transition-colors">
                                    <span class="material-symbols-outlined !text-[28px]">description</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-deep-navy mb-1">{{ $doc->original_name ?? basename($doc->file_path) }}</h3>
                                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">PDF Belgesi</p>
                                </div>
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener noreferrer"
                                   class="mt-auto flex items-center gap-2 text-trust-blue font-bold text-sm group-hover:underline">
                                    <span class="material-symbols-outlined text-[18px]">download</span> İndir (PDF)
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Product Preview & Security (4 cols) --}}
        <aside class="lg:col-span-4 space-y-6">
            {{-- Product Image --}}
            @if($product->image)
                <div class="bg-white border border-deep-navy/10 rounded-xl p-4 shadow-sm">
                    <div class="aspect-square bg-surface-container rounded-lg overflow-hidden mb-3 relative">
                        <img src="{{ Storage::url($product->image) }}"
                             alt="{{ $product->getName() }}"
                             class="w-full h-full object-cover">
                        <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full shadow-md">
                            <span class="text-xs font-bold text-deep-navy">FİZİKSEL ÜRÜN</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">Üretici Görseli</p>
                        <p class="text-xs text-on-surface italic">Görsel, sistem kayıtlarındaki orijinal ürün ile eşleşmektedir.</p>
                    </div>
                </div>
            @endif

            {{-- Digital Content Trigger --}}
            @if($product->type === 'digital' && ($product->digital_file_path || $product->youtube_url))
                <div class="bg-white border border-deep-navy/10 rounded-xl p-4 shadow-sm text-center">
                    @if($product->digital_content_type === 'video')
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-4xl text-primary-600">play_circle</span>
                        </div>
                        <h3 class="font-semibold text-deep-navy">Video İçerik</h3>
                        <p class="text-xs text-on-surface-variant mt-1 mb-4">Bu ürün bir video içeriği içermektedir.</p>
                        <button @click="mediaOpen = true"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-trust-blue hover:opacity-90 text-white font-medium rounded-lg transition-all text-sm">
                            <span class="material-symbols-outlined text-lg">play_arrow</span>
                            İzle
                        </button>
                    @else
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-4xl text-red-600">picture_as_pdf</span>
                        </div>
                        <h3 class="font-semibold text-deep-navy">PDF Belge</h3>
                        <p class="text-xs text-on-surface-variant mt-1 mb-4">Bu ürün bir PDF belgesi içermektedir.</p>
                        <button @click="mediaOpen = true"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:opacity-90 text-white font-medium rounded-lg transition-all text-sm">
                            <span class="material-symbols-outlined text-lg">visibility</span>
                            Görüntüle
                        </button>
                    @endif
                </div>
            @endif

            {{-- Security Checklist --}}
            <div class="bg-deep-navy text-white rounded-xl p-6 shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <span class="material-symbols-outlined !text-[120px]">shield</span>
                </div>
                <div class="relative z-10">
                    <h3 class="font-semibold text-lg mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined">security</span>
                        Güvenlik Bilgisi
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex gap-3">
                            <span class="material-symbols-outlined text-success-green flex-shrink-0">check_circle</span>
                            <div class="text-sm">
                                <p class="font-bold">Manipülasyon Korumalı</p>
                                <p class="text-surface-variant/70">Bu kayıt blockchain tabanlı sistemimizle mühürlenmiştir.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="material-symbols-outlined text-success-green flex-shrink-0">check_circle</span>
                            <div class="text-sm">
                                <p class="font-bold">Yetkili Satıcı</p>
                                <p class="text-surface-variant/70">Ürün yetkili bir dağıtım kanalından tedarik edilmiştir.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="material-symbols-outlined text-success-green flex-shrink-0">check_circle</span>
                            <div class="text-sm">
                                <p class="font-bold">Aktif Garanti</p>
                                <p class="text-surface-variant/70">Ürün garanti kapsamındadır.</p>
                            </div>
                        </li>
                    </ul>
                    <a href="{{ route('page.show', 'gizlilik-politikasi') }}"
                       class="w-full mt-6 bg-white text-deep-navy font-bold py-3 rounded-lg hover:bg-surface-ice transition-colors block text-center text-sm">
                        Sertifikayı Doğrula
                    </a>
                </div>
            </div>

            {{-- Social Media --}}
            @php
                $socials = [
                    'web'    => ['url' => $product->website_url,    'label' => 'Web Sitesi',    'icon' => 'language'],
                    'insta'  => ['url' => $product->instagram_url,  'label' => 'Instagram',      'icon' => 'photo_camera'],
                    'fb'     => ['url' => $product->facebook_url,   'label' => 'Facebook',       'icon' => 'facebook'],
                    'tw'     => ['url' => $product->twitter_url,    'label' => 'Twitter / X',    'icon' => null],
                    'tt'     => ['url' => $product->tiktok_url,     'label' => 'TikTok',         'icon' => 'music_note'],
                    'in'     => ['url' => $product->linkedin_url,   'label' => 'LinkedIn',       'icon' => 'business'],
                ];
                $hasSocials = collect($socials)->filter(fn($s) => $s['url'])->isNotEmpty();
            @endphp

            @if($hasSocials)
                <div class="bg-white border border-deep-navy/10 rounded-xl p-4 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Sosyal Medya</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($socials as $key => $social)
                            @if($social['url'])
                                <a href="{{ $social['url'] }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-container/50 hover:bg-surface-container transition-colors text-xs font-semibold text-on-surface-variant hover:text-trust-blue">
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
        </aside>
    </div>

    {{-- Survey Section (Action Engine) --}}
    @if($scanSaved && $survey && !$surveySubmitted)
        <div class="bg-white border border-deep-navy/10 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-surface-ice border-b border-deep-navy/5 flex items-center justify-between">
                <h2 class="font-semibold text-deep-navy flex items-center gap-2">
                    <span class="material-symbols-outlined text-trust-blue">poll</span>
                    {{ $survey->name }}
                </h2>
            </div>
            <form wire:submit="submitSurvey" class="p-6 space-y-5">
                @foreach($survey->questions as $question)
                    <div>
                        <label class="block text-sm font-semibold text-deep-navy mb-2">
                            {{ $question->getLabel() }}
                            @if($question->is_required)<span class="text-status-error">*</span>@endif
                        </label>
                        @if($question->type === 'text')
                            <input type="text"
                                   wire:model="surveyAnswers.{{ $question->id }}"
                                   class="w-full rounded-xl border border-deep-navy/10 focus:border-trust-blue focus:ring-trust-blue text-sm px-4 py-3"
                                   maxlength="500">
                        @else
                            <div class="space-y-2">
                                @foreach($question->getOptions() as $option)
                                    <label class="flex items-center gap-2 text-sm text-on-surface cursor-pointer hover:text-trust-blue">
                                        <input type="{{ $question->type === 'multiple' ? 'checkbox' : 'radio' }}"
                                               wire:model="surveyAnswers.{{ $question->id }}"
                                               value="{{ $option }}"
                                               class="accent-trust-blue w-4 h-4">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        @error("surveyAnswers.{$question->id}") <p class="text-sm text-status-error mt-1">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <button type="submit"
                        class="px-5 py-2.5 bg-trust-blue text-white font-semibold rounded-lg hover:opacity-90 transition-all text-sm">
                    Anketi Gönder
                </button>
            </form>
        </div>
    @elseif($scanSaved && $survey && $surveySubmitted)
        <div class="bg-white border border-deep-navy/10 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 text-sm text-success-green font-medium flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                Anket yanıtlarınız için teşekkürler.
            </div>
        </div>
    @endif

    {{-- Review Section --}}
    <div class="bg-white border border-deep-navy/10 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-surface-ice border-b border-deep-navy/5 flex items-center justify-between">
            <h2 class="font-semibold text-deep-navy">Ürün Değerlendirmesi</h2>
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
                    <span class="text-sm text-on-surface-variant">{{ number_format($avgRating, 1) }} ({{ $reviewsCount }})</span>
                </div>
            @endif
        </div>
        <div class="p-6 space-y-6" x-data="{ hoverRating: 0 }">
            @if($existingReview)
                <div class="bg-surface-ice/50 rounded-xl p-4">
                    <div class="flex items-center gap-1 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="material-symbols-outlined text-lg {{ $i <= $existingReview->rating ? 'text-yellow-400' : 'text-gray-300' }}">star</span>
                        @endfor
                    </div>
                    @if($existingReview->comment)
                        <p class="text-sm text-on-surface">{{ $existingReview->comment }}</p>
                    @endif
                    <p class="text-xs text-on-surface-variant mt-2">
                        @if($existingReview->is_approved)
                            ✅ Onaylandı
                        @else
                            ⏳ Onay bekliyor
                        @endif
                    </p>
                </div>
            @elseif(!$reviewSaved)
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
                              class="w-full rounded-xl border border-deep-navy/10 focus:border-trust-blue focus:ring-trust-blue resize-none text-sm px-4 py-3"></textarea>
                    @error('comment') <p class="text-sm text-status-error mt-1">{{ $message }}</p> @enderror
                    <button type="submit"
                            class="mt-4 px-5 py-2.5 bg-trust-blue text-white font-semibold rounded-lg hover:opacity-90 transition-all text-sm">
                        Gönder
                    </button>
                </form>
            @else
                <p class="text-sm text-success-green font-medium">Yorumunuz başarıyla gönderildi. Onaylandığında görünecektir.</p>
            @endif

            @php $approvedReviews = $product->approvedReviews()->latest()->get(); @endphp
            @if($approvedReviews->isNotEmpty())
                <div class="border-t border-deep-navy/5 pt-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-3">Diğer Yorumlar</p>
                    <div class="space-y-3">
                        @foreach($approvedReviews as $review)
                            <div class="bg-surface-ice/50 rounded-xl p-3">
                                <div class="flex items-center gap-1 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}">star</span>
                                    @endfor
                                </div>
                                @if($review->comment)
                                    <p class="text-sm text-on-surface">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Mobile Bottom Nav --}}
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 py-3 md:hidden bg-white border-t border-deep-navy/10 shadow-lg rounded-t-full">
        <div class="flex flex-col items-center justify-center bg-surface-container rounded-full p-2 scale-95 duration-100">
            <span class="material-symbols-outlined text-trust-blue">qr_code_scanner</span>
            <span class="text-[10px] font-bold uppercase tracking-widest text-trust-blue">Doğrula</span>
        </div>
        <div class="flex flex-col items-center justify-center text-on-surface-variant p-2">
            <span class="material-symbols-outlined">history</span>
            <span class="text-[10px] font-bold uppercase tracking-widest">Geçmiş</span>
        </div>
        <div class="flex flex-col items-center justify-center text-on-surface-variant p-2">
            <span class="material-symbols-outlined">description</span>
            <span class="text-[10px] font-bold uppercase tracking-widest">Belgeler</span>
        </div>
        <div class="flex flex-col items-center justify-center text-on-surface-variant p-2">
            <span class="material-symbols-outlined">person</span>
            <span class="text-[10px] font-bold uppercase tracking-widest">Profil</span>
        </div>
    </nav>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productCheck', () => ({
            mediaOpen: false,
            redirectUrl: @js($redirectUrl),
            scanRequested: false,
            init() {
                this.setDeviceCookie();
                this.getLocation();
                if (this.redirectUrl) {
                    setTimeout(() => this.finishScan(), 2000);
                }
            },
            setDeviceCookie() {
                const name = 'device_token';
                const value = @js($deviceToken);
                if (!document.cookie.split('; ').find(row => row.startsWith(name + '='))) {
                    document.cookie = name + '=' + value + '; path=/; max-age=' + (60 * 60 * 24 * 365 * 10);
                }
            },
            finishScan(lat = null, lng = null) {
                if (this.scanRequested) return;
                this.scanRequested = true;
                Livewire.dispatch('saveScan', { lat, lng });
            },
            getLocation() {
                if (!navigator.geolocation) {
                    this.finishScan();
                    return;
                }
                const options = this.redirectUrl
                    ? { timeout: 2000 }
                    : { enableHighAccuracy: true, timeout: 10000 };
                navigator.geolocation.getCurrentPosition(
                    (position) => this.finishScan(position.coords.latitude, position.coords.longitude),
                    () => this.finishScan(),
                    options
                );
            }
        }));
    });
</script>