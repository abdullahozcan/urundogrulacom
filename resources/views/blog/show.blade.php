<x-layouts.public>
    <x-slot name="title">{{ $post->title }} - {{ \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com') }}</x-slot>
    <x-slot name="description">{{ Str::limit(strip_tags($post->content), 150) }}</x-slot>
    <x-slot name="ogTitle">{{ $post->title }}</x-slot>
    <x-slot name="ogDescription">{{ Str::limit(strip_tags($post->content), 150) }}</x-slot>
    @if($post->image)
        <x-slot name="ogImage">{{ url(Storage::url($post->image)) }}</x-slot>
    @endif
    <x-slot name="ogType">article</x-slot>

    <article class="max-w-4xl mx-auto px-6 py-16 md:py-24">
        <!-- Header -->
        <header class="mb-12 md:mb-16 text-center">
            <div class="mb-6 flex justify-center">
                <span class="bg-secondary-container/10 text-secondary-fixed-variant text-xs font-bold px-3 py-1.5 rounded-full tracking-wider uppercase">
                    {{ $post->created_at->translatedFormat('d F Y') }}
                </span>
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-primary-container leading-tight mb-8">
                {{ $post->title }}
            </h1>
            
            @if($post->image)
            <div class="w-full aspect-[21/9] md:aspect-[2/1] rounded-3xl overflow-hidden shadow-2xl relative bg-surface-container">
                <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 ring-1 ring-inset ring-black/10 rounded-3xl"></div>
            </div>
            @endif
        </header>

        <!-- Content -->
        <div class="prose prose-lg prose-slate max-w-none prose-headings:font-bold prose-headings:text-primary-container prose-a:text-secondary prose-img:rounded-2xl prose-img:shadow-lg hover:prose-a:text-secondary-fixed-variant">
            {{ $post->content }}
        </div>

        <!-- Footer / Back to home -->
        <footer class="mt-20 pt-10 border-t border-outline-variant/30 flex justify-between items-center">
            <a href="/" class="inline-flex items-center gap-2 text-on-surface-variant hover:text-secondary font-medium transition-colors">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Anasayfaya Dön
            </a>
            
            <!-- Social Share (Optional dummy buttons) -->
            <div class="flex gap-4">
                <a href="#" class="w-10 h-10 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface hover:bg-secondary hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-sm">share</span>
                </a>
            </div>
        </footer>
    </article>
</x-layouts.public>
