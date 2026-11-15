@extends('public.layout')
@section('title', 'CPC Leadership | Chinese Political Leadership Research Database')
@section('meta_description', 'Explore a structured research database of Chinese Communist Party leaders, career histories, education and professional backgrounds.')

@section('content')
<section class="home-hero">
    <div class="hero-grid"></div>
    <div class="container hero-inner">
        <div class="hero-copy">
            <div class="eyebrow light"><span class="eyebrow-line"></span> A RESEARCH DATABASE</div>
            <h1>Understanding the people <em>behind power.</em></h1>
            <p class="hero-lede">Explore the biographies, career trajectories, education and professional networks of China's political leadership through a structured, searchable database.</p>
            <div class="hero-actions">
                <a class="btn btn-gold" href="{{ route('public.leaders.index') }}">Explore the directory <span>↗</span></a>
                <a class="text-link light-link" href="#methodology">About this project <span>↓</span></a>
            </div>
        </div>
        <div class="hero-side">
            <div class="hero-index">01 <span>/ RESEARCH</span></div>
            <div class="hero-note">A structured view of leadership biographies, career paths and institutional experience.</div>
            <div class="hero-rule"></div>
            <div class="hero-note muted">Search. Compare. Trace career trajectories.</div>
        </div>
    </div>
    <div class="hero-bottom container"><span>CHINESE POLITICAL LEADERSHIP</span><span>DATA-LED RESEARCH</span><span>PUBLIC DATABASE</span></div>
</section>

<section class="stats-section">
    <div class="container stats-grid">
        <div class="stats-intro"><span class="eyebrow">THE DATABASE</span><h2>Leadership, mapped.</h2><p>Explore structured records across biography, appointments and education.</p></div>
        <div class="stat"><strong>{{ number_format($stats['leaders']) }}</strong><span>Leader profiles</span></div>
        <div class="stat"><strong>{{ number_format($stats['career_records']) }}</strong><span>Career records</span></div>
        <div class="stat"><strong>{{ number_format($stats['education_records']) }}</strong><span>Education records</span></div>
        <div class="stat"><strong>{{ number_format($stats['provinces']) }}</strong><span>Provinces represented</span></div>
    </div>
</section>

<section class="explore-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">START EXPLORING</span><h2>Follow the data.</h2></div><a class="text-link" href="{{ route('public.leaders.index') }}">View all leaders <span>→</span></a></div>
        <div class="feature-grid">
            <a class="feature-card feature-dark" href="{{ route('public.leaders.index') }}"><span class="feature-number">01</span><div><span class="feature-kicker">BIOGRAPHIES</span><h3>Leader directory</h3><p>Search profiles by name, rank, position, province and other biographical details.</p></div><span class="feature-arrow">↗</span></a>
            <a class="feature-card" href="{{ route('public.leaders.index', ['sort' => 'rank']) }}"><span class="feature-number">02</span><div><span class="feature-kicker">CAREER PATHS</span><h3>Institutional experience</h3><p>Trace appointments across departments, provinces and levels of government.</p></div><span class="feature-arrow">↗</span></a>
            <a class="feature-card" href="{{ route('public.leaders.index') }}"><span class="feature-number">03</span><div><span class="feature-kicker">EDUCATION</span><h3>Academic backgrounds</h3><p>Explore institutions, degrees, fields of study and training histories.</p></div><span class="feature-arrow">↗</span></a>
        </div>
    </div>
</section>

<section class="people-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">IN THE DATABASE</span><h2>Explore leadership profiles.</h2></div><a class="text-link" href="{{ route('public.leaders.index') }}">Browse directory <span>→</span></a></div>
        <div class="people-grid">
            @foreach($featured as $person)
                <a class="person-card" href="{{ route('public.leaders.show', $person->leader_id) }}">
                    <span class="person-id">{{ $person->leader_id }}</span>
                    <h3>{{ $person->english_name ?: $person->chinese_name }}</h3>
                    @if($person->english_name && $person->chinese_name)<div class="person-chinese">{{ $person->chinese_name }}</div>@endif
                    <p>{{ $person->current_position ?: 'Position not listed' }}</p>
                    <div class="person-meta"><span>{{ $person->rank ?: 'Rank not listed' }}</span><span>{{ $person->province_of_employment ?: '—' }}</span></div>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="method-section" id="methodology">
    <div class="container method-grid">
        <div><span class="eyebrow light">ABOUT THE PROJECT</span><h2>Context matters.<br><em>Evidence matters more.</em></h2></div>
        <div class="method-copy"><p>CPC Leadership is designed as a structured research resource for examining China's political elite. It brings together biographical information, career appointments, education and selected professional-network indicators in one searchable interface.</p><p>Records may vary in completeness. Analytical or inferred indicators are separated from biographical records and should be read as research assessments, not established facts. Users should consult original sources and apply their own judgement.</p><a class="btn btn-outline-light" href="{{ route('public.leaders.index') }}">Start your research <span>↗</span></a></div>
    </div>
</section>
@endsection
