<!DOCTYPE html>
<html class="light" lang="tr">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>{{ $title ?? \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com | Dijital Ürün Doğrulama') }}</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $description ?? \App\Helpers\SettingHelper::get('site_description', 'Ürün orijinalliği doğrulama ve dijital marka koruma platformu.') }}" />
    <meta name="keywords" content="{{ $keywords ?? \App\Helpers\SettingHelper::get('site_keywords', 'ürün doğrulama, qr kod, orijinal ürün, dijital noter') }}" />
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="{{ $ogTitle ?? $title ?? \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com | Dijital Ürün Doğrulama') }}" />
    <meta property="og:description" content="{{ $ogDescription ?? $description ?? \App\Helpers\SettingHelper::get('site_description', 'Ürün orijinalliği doğrulama platformu.') }}" />
    <meta property="og:image" content="{{ $ogImage ?? \App\Helpers\SettingHelper::get('site_og_image', asset('img/default-og.jpg')) }}" />
    <meta property="og:type" content="{{ $ogType ?? 'website' }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $ogTitle ?? $title ?? \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com') }}" />
    <meta name="twitter:description" content="{{ $ogDescription ?? $description ?? \App\Helpers\SettingHelper::get('site_description', 'Ürün orijinalliği doğrulama platformu.') }}" />
    <meta name="twitter:image" content="{{ $ogImage ?? \App\Helpers\SettingHelper::get('site_og_image', asset('img/default-og.jpg')) }}" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-secondary-container": "#d1d6ff",
                        "on-background": "#0b1c30",
                        "tertiary": "#000301",
                        "primary-fixed-dim": "#b8c7e5",
                        "inverse-on-surface": "#eaf1ff",
                        "inverse-surface": "#213145",
                        "on-primary-container": "#7785a1",
                        "on-secondary": "#ffffff",
                        "surface-container-highest": "#d3e4fe",
                        "surface-container": "#e5eeff",
                        "on-surface": "#0b1c30",
                        "inverse-primary": "#b8c7e5",
                        "on-primary": "#ffffff",
                        "secondary-fixed": "#dee0ff",
                        "on-surface-variant": "#44474d",
                        "surface-variant": "#d3e4fe",
                        "tertiary-container": "#002214",
                        "surface-dim": "#cbdbf5",
                        "background": "#f8f9ff",
                        "error-container": "#ffdad6",
                        "secondary-container": "#0e42ff",
                        "error": "#ba1a1a",
                        "surface-bright": "#f8f9ff",
                        "surface": "#f8f9ff",
                        "on-secondary-fixed-variant": "#002fc9",
                        "on-error-container": "#93000a",
                        "secondary": "#002fc9",
                        "surface-tint": "#515f79",
                        "surface-container-high": "#dce9ff",
                        "surface-container-low": "#eff4ff",
                        "outline-variant": "#c5c6ce",
                        "on-tertiary-container": "#009768",
                        "on-secondary-fixed": "#00105b",
                        "secondary-fixed-dim": "#bac3ff",
                        "on-primary-fixed-variant": "#394760",
                        "on-primary-fixed": "#0c1c33",
                        "on-tertiary-fixed-variant": "#005236",
                        "primary": "#00030b",
                        "tertiary-fixed": "#6ffbbe",
                        "primary-container": "#0e1d34",
                        "on-tertiary-fixed": "#002113",
                        "surface-container-lowest": "#ffffff",
                        "outline": "#75777e",
                        "tertiary-fixed-dim": "#4edea3",
                        "on-tertiary": "#ffffff",
                        "primary-fixed": "#d6e3ff",
                        "on-error": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "fontFamily": {
                        "headline": ["Inter"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .glass-nav {
            background: rgba(248, 249, 255, 0.8);
            backdrop-filter: blur(20px);
        }
    </style>
    @livewireStyles
    {!! \App\Helpers\SettingHelper::get('custom_head_scripts') !!}
</head>

<body class="bg-background text-on-background selection:bg-secondary-fixed-dim flex flex-col min-h-screen">
    <!-- TopNavBar -->
    <header class="docked full-width top-0 sticky z-50 bg-slate-50/80 backdrop-blur-xl shadow-sm">
        <div class="flex justify-between items-center w-full px-6 py-4 max-w-screen-2xl mx-auto">
            <div class="flex items-center gap-8">
                <a class="text-2xl font-black tracking-tighter text-slate-900" href="/">urundogrula.com</a>
                <nav class="hidden md:flex items-center gap-6 font-['Inter'] tracking-tight font-medium text-sm">
                    <a class="text-blue-600 border-b-2 border-blue-600 font-bold pb-1 transition-transform scale-95 active:scale-90" href="/">Doğrula</a>
                    <a class="text-slate-500 hover:text-slate-900 hover:bg-slate-100/50 transition-all duration-300 px-2 py-1 rounded-lg" href="#">Markalar</a>
                    <a class="text-slate-500 hover:text-slate-900 hover:bg-slate-100/50 transition-all duration-300 px-2 py-1 rounded-lg" href="#">Hizmetler</a>
                    <a class="text-slate-500 hover:text-slate-900 hover:bg-slate-100/50 transition-all duration-300 px-2 py-1 rounded-lg" href="#">Kurumsal</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden lg:flex items-center bg-slate-100 px-3 py-1.5 rounded-full">
                    <span class="material-symbols-outlined text-slate-400 text-sm mr-2">search</span>
                    <input class="bg-transparent border-none focus:ring-0 text-xs w-32 outline-none" placeholder="{{ \App\Helpers\SettingHelper::get('search_placeholder', 'Marka ara...') }}" type="text" />
                </div>
                
                <!-- Language Switcher -->
                <div class="relative group">
                    <button class="flex items-center gap-1 text-sm font-semibold text-slate-600 hover:text-slate-900 uppercase">
                        {{ app()->getLocale() }}
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </button>
                    <div class="absolute right-0 mt-2 w-24 bg-white rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 border border-slate-100 overflow-hidden">
                        @foreach(config('languages.supported') as $code => $label)
                            <a href="{{ route('locale.switch', $code) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 {{ app()->getLocale() === $code ? 'font-bold text-blue-600 bg-blue-50/50' : '' }}">
                                {{ strtoupper($code) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="/admin" class="bg-primary-container text-white px-5 py-2 rounded-xl text-sm font-semibold hover:opacity-90 transition-transform active:scale-95">
                    {{ \App\Helpers\SettingHelper::get('login_button', 'Giriş Yap') }}
                </a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="full-width bottom-0 mt-20 bg-slate-900 border-t border-slate-800">
        <div class="flex flex-col md:flex-row justify-between items-center w-full px-8 py-12 max-w-screen-2xl mx-auto gap-6 font-['Inter']">
            <div class="flex flex-col items-center md:items-start gap-2">
                <div class="text-xl font-bold text-white">urundogrula.com</div>
                <p class="text-slate-400 text-[10px] uppercase tracking-widest">Dijital Güven Platformu</p>
            </div>
            <nav class="flex flex-wrap justify-center gap-8 text-xs uppercase tracking-widest font-semibold">
                <a class="text-slate-400 hover:text-emerald-400 underline underline-offset-4 transition-colors duration-200" href="{{ route('blog.show', 'hakkimizda') }}">Hakkımızda</a>
                <a class="text-slate-400 hover:text-emerald-400 underline underline-offset-4 transition-colors duration-200" href="{{ route('blog.show', 'kullanim-kosullari') }}">Kullanım Koşulları</a>
                <a class="text-slate-400 hover:text-emerald-400 underline underline-offset-4 transition-colors duration-200 text-white font-bold" href="{{ route('blog.show', 'gizlilik-politikasi') }}">Gizlilik Politikası</a>
                <a class="text-slate-400 hover:text-emerald-400 underline underline-offset-4 transition-colors duration-200" href="{{ route('blog.show', 'kvkk') }}">KVKK</a>
                <a class="text-slate-400 hover:text-emerald-400 underline underline-offset-4 transition-colors duration-200" href="{{ route('blog.show', 'iletisim') }}">İletişim</a>
            </nav>
            <div class="text-slate-400 text-xs font-medium opacity-80">
                © {{ date('Y') }} urundogrula.com. Tüm Hakları Saklıdır.
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
