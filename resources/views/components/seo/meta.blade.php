@push('meta_tags')

<title>{{ $isHome ? $title : config('app.name').' | '.$title }}</title>

<link rel="canonical" href="{{ $url }}" />
@if(!empty($post))
    @foreach($post->otherLanguages() as $langPost)
        <link rel="alternate" hreflang="{{ str_replace('_', '-', cms_locale_full($langPost->lang)) }}" href="{{ route('post.show.'.$langPost->lang, ['slug' => $langPost->name]) }}" />
    @endforeach

    <link rel="alternate" hreflang="x-default" href="{{ $url }}" />
@endif

<meta name="description" content="{{ $description }}" />
<meta name="author" content="{{ config('app.name') }}">
<meta name="robots" content="index, follow">
@if( $ogType == "article")
    <meta property="article:published_time" content="{{ $publishedAt }}" />
    <meta property="article:modified_time" content="{{ $modifiedAt }}" />
    <meta property="article:author" content="{{ config('app.name') }}" />
    @if($section)
        <meta property="article:section" content="{{ $section }}" />
    @endif
    @if(!empty($tags))
        <meta property="article:tag" content="{{ implode(', ', $tags) }}" />
    @endif
@endif

@if(!empty($tags))
    <meta name="keywords" content="{{ implode(', ', $tags) }}">
@endif

<meta property="og:title" content="{{ $title }}" />
<meta property="og:type" content="{{ $ogType }}" />
<meta property="og:url" content="{{ $url }}" />
<meta property="og:image" content="{{ $image }}" />
<meta property="og:image:alt" content="{{ $title }} - borítókép" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:locale" content="{{ cms_locale_full() }}" />

<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />
<meta name="twitter:image" content="{{ $image }}" />

@endpush
