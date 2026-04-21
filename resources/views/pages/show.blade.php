<x-layouts.public>
    <x-slot name="title">{{ $page->title }} - {{ \App\Helpers\SettingHelper::get('site_title', 'urundogrula.com') }}</x-slot>
    <x-slot name="description">{{ Str::limit(strip_tags($page->content), 150) }}</x-slot>
    <x-slot name="ogTitle">{{ $page->title }}</x-slot>
    <x-slot name="ogDescription">{{ Str::limit(strip_tags($page->content), 150) }}</x-slot>

    <article class="max-w-4xl mx-auto px-6 py-16 md:py-24">
        <header class="mb-12 border-b border-outline-variant/30 pb-8">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-primary-container">
                {{ $page->title }}
            </h1>
        </header>

        <div class="prose prose-lg prose-slate max-w-none prose-headings:font-bold prose-headings:text-primary-container prose-a:text-secondary prose-img:rounded-2xl hover:prose-a:text-secondary-fixed-variant">
            {!! $page->content !!}
        </div>
    </article>
</x-layouts.public>
