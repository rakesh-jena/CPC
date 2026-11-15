@extends('public.layout')
@section('title', 'Leader Directory | CPC Leadership')
@section('meta_description', 'Search and filter CPC Leadership profiles by name, rank, current position, province, gender and age.')

@section('content')
<section class="page-hero compact-hero"><div class="container"><div class="eyebrow light"><span class="eyebrow-line"></span> RESEARCH DATABASE / DIRECTORY</div><h1>Leader <em>directory.</em></h1><p>Search across biographies, career appointments and education records.</p></div></section>
<section class="directory-section"><div class="container">
    <form class="directory-toolbar" method="GET" action="{{ route('public.leaders.index') }}">
        <div class="search-box"><span class="search-icon">⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search names, positions, departments, institutions…" aria-label="Search leaders"><button class="btn btn-dark" type="submit">Search</button></div>
        <div class="toolbar-bottom"><span class="result-count"><strong>{{ number_format($leaders->total()) }}</strong> matching profiles</span><label class="sort-control">Sort by <select name="sort" onchange="this.form.submit()"><option value="name" @selected(request('sort','name')==='name')>Name</option><option value="rank" @selected(request('sort')==='rank')>Rank</option><option value="age_asc" @selected(request('sort')==='age_asc')>Age: youngest</option><option value="age_desc" @selected(request('sort')==='age_desc')>Age: oldest</option></select></label></div>
        @foreach(request()->except(['q','sort','page']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
    </form>

    <div class="directory-layout">
        <aside class="filter-panel">
            <div class="filter-heading"><h2>Refine results</h2><a href="{{ route('public.leaders.index') }}">Reset</a></div>
            <form method="GET" action="{{ route('public.leaders.index') }}" id="filterForm">
                <input type="hidden" name="q" value="{{ request('q') }}"><input type="hidden" name="sort" value="{{ request('sort','name') }}">
                <label class="filter-field">Rank<select name="rank"><option value="">All ranks</option>@foreach($filters['ranks'] as $v)<option value="{{ $v }}" @selected(request('rank')===$v)>{{ $v }}</option>@endforeach</select></label>
                <label class="filter-field">Current position<select name="position"><option value="">All positions</option>@foreach($filters['positions'] as $v)<option value="{{ $v }}" @selected(request('position')===$v)>{{ $v }}</option>@endforeach</select></label>
                <label class="filter-field">Province of employment<select name="posting"><option value="">All provinces</option>@foreach($filters['postingProvinces'] as $v)<option value="{{ $v }}" @selected(request('posting')===$v)>{{ $v }}</option>@endforeach</select></label>
                <label class="filter-field">Birth province<select name="birth"><option value="">All provinces</option>@foreach($filters['birthProvinces'] as $v)<option value="{{ $v }}" @selected(request('birth')===$v)>{{ $v }}</option>@endforeach</select></label>
                <label class="filter-field">Gender<select name="gender"><option value="">All genders</option>@foreach($filters['genders'] as $v)<option value="{{ $v }}" @selected(request('gender')===$v)>{{ $v }}</option>@endforeach</select></label>
                <div class="filter-field"><span>Age range</span><div class="age-fields"><input type="number" min="0" max="120" name="age_min" value="{{ request('age_min') }}" placeholder="Min"><input type="number" min="0" max="120" name="age_max" value="{{ request('age_max') }}" placeholder="Max"></div></div>
                <button class="btn btn-dark filter-submit" type="submit">Apply filters <span>→</span></button>
            </form>
        </aside>

        <div class="results-area">
            @if($leaders->count())
                <div class="leader-results">
                    @foreach($leaders as $person)
                    <a class="leader-row" href="{{ route('public.leaders.show', $person->leader_id) }}">
                        <div class="leader-avatar">{{ mb_substr($person->english_name ?: $person->chinese_name ?: $person->leader_id, 0, 1) }}</div>
                        <div class="leader-main"><div class="leader-name-line"><h3>{{ $person->english_name ?: $person->chinese_name ?: 'Name unavailable' }}</h3><span class="leader-code">{{ $person->leader_id }}</span></div>
                            @if($person->english_name && $person->chinese_name)<div class="person-chinese">{{ $person->chinese_name }}</div>@endif
                            <p>{{ $person->current_position ?: 'Current position not listed' }}</p><div class="leader-tags"><span>{{ $person->rank ?: 'Rank not listed' }}</span><span>{{ $person->province_of_employment ?: 'Province not listed' }}</span></div>
                        </div><span class="row-arrow">↗</span>
                    </a>
                    @endforeach
                </div>
                <div class="pagination-wrap">{{ $leaders->links() }}</div>
            @else
                <div class="empty-state"><span>NO MATCHES</span><h2>No leaders found.</h2><p>Try a broader search or remove one or more filters.</p><a class="btn btn-dark" href="{{ route('public.leaders.index') }}">Clear all filters</a></div>
            @endif
        </div>
    </div>
</div></section>
@endsection
