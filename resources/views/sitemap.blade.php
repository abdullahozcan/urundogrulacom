<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('contact') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    
    @foreach ($pages as $page)
        @php
            $slug = is_array($page->slug) ? ($page->slug[$defaultLocale] ?? current($page->slug)) : $page->slug;
        @endphp
        <url>
            <loc>{{ url('/p/' . $slug) }}</loc>
            <lastmod>{{ $page->updated_at->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    @foreach ($posts as $post)
        @php
            $slug = is_array($post->slug) ? ($post->slug[$defaultLocale] ?? current($post->slug)) : $post->slug;
        @endphp
        <url>
            <loc>{{ url('/blog/' . $slug) }}</loc>
            <lastmod>{{ $post->updated_at->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
        </url>
    @endforeach
</urlset>
