<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CPC Leadership | Research Database')</title>
    <meta name="description" content="@yield('meta_description', 'An independent research database exploring the biographies, careers and professional backgrounds of Chinese Communist Party leaders.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="theme-color" content="#101e32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cpc-public.css') }}">
    @stack('head')
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="{{ route('public.home') }}">
            <span class="brand-mark">CPC</span>
            <span class="brand-copy"><strong>LEADERSHIP</strong><small>RESEARCH DATABASE</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" data-nav-toggle><span></span><span></span><span></span></button>
        <nav class="main-nav" data-nav>
            <a href="{{ route('public.home') }}">Overview</a>
            <a href="{{ route('public.leaders.index') }}">Leader directory</a>
            <a href="{{ route('public.leaders.index', ['sort' => 'rank']) }}">Explore leadership</a>
            <a class="nav-cta" href="{{ route('public.leaders.index') }}">Explore database <span>↗</span></a>
        </nav>
    </div>
</header>

<main>@yield('content')</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <div><a class="brand brand-footer" href="{{ route('public.home') }}"><span class="brand-mark">CPC</span><span class="brand-copy"><strong>LEADERSHIP</strong><small>RESEARCH DATABASE</small></span></a><p>An independent research resource for the study of Chinese political leadership.</p></div>
        <div class="footer-links"><a href="{{ route('public.leaders.index') }}">Browse leaders</a><a href="{{ route('public.home') }}#methodology">About the database</a></div>
        <small class="copyright">© {{ date('Y') }} CPC Leadership Research Database</small>
    </div>
</footer>
<script src="{{ asset('js/cpc-public.js') }}" defer></script>
@stack('scripts')
</body>
</html>
