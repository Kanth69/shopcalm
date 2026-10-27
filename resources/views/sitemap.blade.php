{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ route('shop') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    @foreach($categories as $cat)
    <url>
        <loc>{{ route('category.products', $cat->slug) }}</loc>
        <lastmod>{{ $cat->updated_at ? $cat->updated_at->format('Y-m-d') : date('Y-m-d') }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

    @foreach($products as $prod)
    <url>
        <loc>{{ route('product.show', $prod->slug) }}</loc>
        <lastmod>{{ $prod->updated_at ? $prod->updated_at->format('Y-m-d') : date('Y-m-d') }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    <url>
        <loc>{{ route('offers.index') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc>{{ route('contact') }}</loc>
        <lastmod>{{ date('Y-m-d') }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
</urlset>
