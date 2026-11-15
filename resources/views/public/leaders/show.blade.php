@extends('public.layout')
@section('title', ($profile->english_name ?: $profile->chinese_name ?: $profile->leader_id).' | CPC Leadership')
@section('meta_description', 'Research profile, career history and education records for '.($profile->english_name ?: $profile->chinese_name ?: $profile->leader_id).'.')

@section('content')
<section class="profile-hero"><div class="container">
    <div class="breadcrumb"><a href="{{ route('public.home') }}">Home</a><span>/</span><a href="{{ route('public.leaders.index') }}">Leader directory</a><span>/</span><span>{{ $profile->leader_id }}</span></div>
    <div class="profile-heading"><div class="profile-monogram">{{ mb_substr($profile->english_name ?: $profile->chinese_name ?: $profile->leader_id, 0, 1) }}</div><div><div class="eyebrow light">LEADER PROFILE <span class="profile-code">{{ $profile->leader_id }}</span></div><h1>{{ $profile->english_name ?: $profile->chinese_name ?: 'Name unavailable' }}</h1>@if($profile->english_name && $profile->chinese_name)<div class="profile-chinese">{{ $profile->chinese_name }}</div>@endif<p>{{ $profile->current_position ?: 'Current position not listed' }}</p><div class="profile-tags"><span>{{ $profile->rank ?: 'Rank not listed' }}</span><span>{{ $profile->province_of_employment ?: 'Province not listed' }}</span></div></div></div>
</div></section>

<section class="profile-content"><div class="container">
    <nav class="profile-tabs" aria-label="Profile sections"><a href="#biography">Biography</a><a href="#career">Career history</a><a href="#education">Education</a><a href="#networks">Networks</a><a href="#analysis">Research indicators</a></nav>

    <section class="profile-block" id="biography"><div class="block-heading"><div><span class="eyebrow">01 / BIOGRAPHY</span><h2>Personal background</h2></div></div>
        <div class="data-grid">
            @foreach(['English name'=>'english_name','Chinese name'=>'chinese_name','Leader ID'=>'leader_id','Gender'=>'gender','Date of birth'=>'dob','Age'=>'age','Birth location'=>'birth_location','Birth province'=>'birth_province','Ancestral home'=>'ancestral_home','Ethnicity'=>'ethnicity','CPC membership year'=>'cpc_membership_year','Status'=>'status','Current position'=>'current_position','Concurrent position 1'=>'concurrent_position_1','Concurrent position 2'=>'concurrent_position_2','Concurrent position 3'=>'concurrent_position_3','Rank'=>'rank','Province of employment'=>'province_of_employment'] as $label=>$field)
                @if(!empty($profile->$field))<div class="data-cell"><span>{{ $label }}</span><strong>{{ $profile->$field }}</strong></div>@endif
            @endforeach
        </div>
    </section>

    <section class="profile-block" id="career"><div class="block-heading"><div><span class="eyebrow">02 / CAREER</span><h2>Career history</h2><p>Recorded appointments and postings, ordered chronologically where dates are available.</p></div><div class="block-metrics"><strong>{{ $careerStats['appointments'] }}</strong><span>Appointments</span><strong>{{ $careerStats['provinces'] }}</strong><span>Provinces</span></div></div>
        @if($career->count())<div class="timeline">@foreach($career as $item)<article class="timeline-item"><span class="timeline-dot"></span><div class="timeline-date">{{ $item->start_year ?: 'Year n/a' }} — {{ $item->end_year ?: 'Present / n.a.' }}</div><h3>{{ $item->position ?: 'Position not specified' }}</h3><p>{{ $item->department ?: 'Department not specified' }}</p>@if($item->province)<span class="timeline-location">{{ $item->province }}</span>@endif</article>@endforeach</div>
        @else<div class="empty-inline">No career history records are available for this profile.</div>@endif
    </section>

    <section class="profile-block" id="education"><div class="block-heading"><div><span class="eyebrow">03 / EDUCATION</span><h2>Education & training</h2><p>Institutions, qualifications and training records available in the database.</p></div></div>
        @if($education->count())<div class="education-list">@foreach($education as $item)<article class="education-item"><div class="education-years">{{ $item->start_year ?: '—' }}@if($item->end_year) — {{ $item->end_year }}@endif</div><div><span class="feature-kicker">{{ $item->type ?: 'EDUCATION / TRAINING' }}</span><h3>{{ $item->institution ?: 'Institution not listed' }}</h3><p>{{ collect([$item->degree, $item->major])->filter()->implode(' · ') ?: 'Qualification details not listed' }}</p><small>{{ $item->location }}</small></div></article>@endforeach</div>
        @else<div class="empty-inline">No education or training records are available for this profile.</div>@endif
    </section>

    <section class="profile-block" id="networks"><div class="block-heading"><div><span class="eyebrow">04 / PROFESSIONAL NETWORKS</span><h2>Selected network indicators</h2><p>Fields recorded in the political networks dataset. These entries may reflect assessments and should be interpreted cautiously.</p></div></div>
        @if($network)
        <div class="data-grid">
            @foreach(['Primary patron'=>'primary_patron','Secondary patron'=>'secondary_patron','Major political network'=>'major_political_network','Zhejiang network'=>'zhejiang_network','Fujian network'=>'fujian_network','Shanghai network'=>'shanghai_network','Communist Youth League'=>'communist_youth_league','Tsinghua network'=>'tsinghua_network','Sent-down youth'=>'sent_down_youth','Sent-down location'=>'location','Sent-down time period'=>'time_period','Overseas education'=>'overseas_education','Overseas location'=>'overseas_location','Overseas time period'=>'overseas_time_period','Confidence level'=>'confidence_level','Investigated'=>'investigated','Party disciplinary action'=>'party_disciplinary_action','Associates purged'=>'associates_purged','Patron purged'=>'patron_purged','Books published'=>'books_published','Academic papers published'=>'academic_paper_published','US sanctions'=>'us_sanctions','EU sanctions'=>'eu_sanctions','Expected retirement congress'=>'expected_retirement_congress'] as $label=>$field)
                @if(!empty($network->$field))<div class="data-cell"><span>{{ $label }}</span><strong>{{ $network->$field }}</strong></div>@endif
            @endforeach
        </div>
        @else<div class="empty-inline">No network records are available for this profile.</div>@endif
    </section>

    <section class="profile-block analysis-block" id="analysis"><div class="block-heading"><div><span class="eyebrow">05 / ANALYTICAL DATA</span><h2>Research indicators</h2><p>These fields are analytical or inferred, not direct biographical facts.</p></div></div>
        <div class="notice"><strong>Interpret with caution</strong><p>Indicators in this section may be based on incomplete public information, coding decisions or analytical interpretation. They should not be treated as verified facts or definitive predictions.</p></div>
        @if($inferred)
        <div class="data-grid">
            @foreach(['Stem background'=>'stem_background','Central Party School'=>'central_party_school','Provincial Party School'=>'provincial_party_school','National Academy of Governance'=>'national_academy_of_governance','Military academy'=>'military_academy','Worked abroad'=>'worked_abroad','First province'=>'first_province','Total appointments'=>'total_appointments','Total working years as party member'=>'total_working_years_party_member','Total provinces served'=>'total_provinces_served','Local government experience'=>'local_government_experience','Provincial government experience'=>'provincial_government_experience','Provincial party experience'=>'provincial_party_experience','Central government experience'=>'central_government_experience','Central party experience'=>'central_party_experience','SOE experience'=>'soe_experience','PLA experience'=>'pla_experience','Economic policy experience'=>'economic_policy_experience','Financial policy experience'=>'financial_policy_experience','Technology policy experience'=>'technology_policy_experience','Foreign affairs experience'=>'foreign_affairs_experience','Propaganda experience'=>'propaganda_experience','Party organization experience'=>'party_organization_experience','CCDI experience'=>'ccdi_experience','Internal security experience'=>'internal_security_experience','Agriculture / rural affairs'=>'agriculture_rural_affairs','Industrial policy experience'=>'industrial_policy_experience','PLA service experience'=>'pla_service_experience','CMC experience'=>'central_military_commission_experience','Defence industry experience'=>'defence_industry_experience','Coastal experience'=>'coastal_experience','Inland experience'=>'inland_experience','Border province experience'=>'border_province_experience','Minority region experience'=>'minority_region_experience','Municipality experience'=>'municipality_experience','Years to county level'=>'years_to_county','Years to prefecture level'=>'years_to_prefecture','Years to province level'=>'years_to_province','Years to central level'=>'years_to_center','Years to Politburo'=>'years_to_politburo','Years to PSC'=>'years_to_psc','Year entered Politburo'=>'year_entered_politburo','Year entered PSC'=>'year_entered_psc','Served in Secretariat'=>'served_in_secretariat','Served in CCDI leadership'=>'served_in_ccdi_leadership','Served in NPC leadership'=>'served_in_npc_leadership','Served in CPPCC leadership'=>'served_in_cppcc_leadership','Member of CMC'=>'member_of_cmc','Xi proximity score'=>'xi_proximity_score','Factional affiliation score'=>'factional_affiliation_score','Promotion probability'=>'promotion_probability','Retirement probability'=>'retirement_probability','Successor potential'=>'successor_potential'] as $label=>$field)
                @if(!empty($inferred->$field))<div class="data-cell"><span>{{ $label }}</span><strong>{{ $inferred->$field }}</strong></div>@endif
            @endforeach
        </div>
        @else<div class="empty-inline">No analytical indicators are available for this profile.</div>@endif
    </section>
    <div class="back-link"><a class="text-link" href="{{ route('public.leaders.index') }}">← Back to directory</a></div>
</div></section>
@endsection
