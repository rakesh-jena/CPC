@extends('public.layout')

@section('title', 'Leadership Network - ' . ($leader->english_name ?? $leader->leader_id))

@section('content')
<div class="container-fluid py-4 cpc-network-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
        <div>
            <div class="text-muted small mb-1">CPC LEADERSHIP / NETWORK ANALYSIS</div>
            <h1 class="h3 mb-1">{{ $leader->english_name ?? $leader->leader_id }}</h1>
            <div class="text-muted">{{ $leader->current_position ?? '' }}</div>
        </div>
        <a href="/leaders/{{ rawurlencode($leader->leader_id) }}" class="btn btn-outline-secondary btn-sm">
            Back to leader profile
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="cpc-stat-card">
                <span>Direct connections</span>
                <strong>{{ number_format($connectionCount) }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cpc-stat-card">
                <span>Minimum relationship score</span>
                <strong>{{ $minScore }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cpc-stat-card">
                <span>Scoring source</span>
                <strong>Client CSV / R output</strong>
            </div>
        </div>
    </div>

    <div class="cpc-network-toolbar">
        <form method="GET" action="{{ route('leaders.network', ['leaderId' => $leader->leader_id]) }}" class="d-flex align-items-center gap-2 flex-wrap">
            <label for="min_score" class="mb-0">Minimum score</label>
            <select name="min_score" id="min_score" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                @foreach([4, 5, 8, 9, 12, 13, 17, 21] as $score)
                    <option value="{{ $score }}" {{ (int)$minScore === $score ? 'selected' : '' }}>{{ $score }}+</option>
                @endforeach
            </select>
            <span class="text-muted small">Scores are the original values supplied by the research team, not percentages.</span>
        </form>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="network-fit">Fit graph</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="network-reset">Reset view</button>
        </div>
    </div>

    <div class="cpc-network-layout">
        <div id="cpc-leader-network" role="img" aria-label="Interactive network graph showing this leader's direct connections"></div>
        <aside class="cpc-network-details">
            <h2 class="h6">Connection details</h2>
            <p class="small text-muted" id="network-help">Select a connected leader or a line to inspect the relationship.</p>
            <div id="network-detail-content">
                <div class="network-detail-name">{{ $leader->english_name ?? $leader->leader_id }}</div>
                <div class="small text-muted">{{ $leader->current_position ?? '' }}</div>
                <div class="small mt-2">Central leader</div>
            </div>
        </aside>
    </div>

    <div class="cpc-network-legend mt-3">
        <span><i class="legend-dot central"></i> Selected leader</span>
        <span><i class="legend-dot connected"></i> Connected leader</span>
        <span><i class="legend-line"></i> Thicker line = stronger relationship score</span>
    </div>
</div>

<style>
.cpc-network-page { color:#172033; }
.cpc-stat-card { background:#f6f8fb; border:1px solid #e5eaf1; border-radius:12px; padding:15px 18px; }
.cpc-stat-card span { display:block; color:#718096; font-size:12px; margin-bottom:5px; }
.cpc-stat-card strong { font-size:22px; font-weight:750; }
.cpc-network-toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; background:#fff; border:1px solid #e5eaf1; border-radius:12px 12px 0 0; padding:12px 15px; }
.cpc-network-layout { display:grid; grid-template-columns:minmax(0,1fr) 300px; min-height:620px; border:1px solid #e5eaf1; border-top:0; border-radius:0 0 12px 12px; overflow:hidden; background:#fff; }
#cpc-leader-network { min-height:620px; width:100%; background:radial-gradient(circle at center,#fff 0,#f8fafc 78%); }
.cpc-network-details { border-left:1px solid #e5eaf1; padding:20px; background:#fff; }
.network-detail-name { font-weight:750; font-size:16px; margin-bottom:5px; }
.network-detail-score { font-size:30px; font-weight:800; color:#173b61; margin:12px 0 2px; }
.network-detail-label { color:#718096; font-size:11px; text-transform:uppercase; letter-spacing:.06em; }
.network-detail-block { border-top:1px solid #edf0f4; padding:12px 0; margin-top:12px; }
.network-detail-block strong { display:block; font-size:12px; margin-bottom:5px; }
.network-detail-block p { font-size:12px; color:#596579; line-height:1.55; margin:0; white-space:pre-wrap; }
.cpc-network-legend { display:flex; gap:22px; flex-wrap:wrap; color:#667085; font-size:12px; }
.legend-dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; }
.legend-dot.central { background:#173b61; }
.legend-dot.connected { background:#2b9a8f; }
.legend-line { display:inline-block; width:25px; border-top:4px solid #93a4b8; margin:0 6px 3px 0; }
@media(max-width:900px) { .cpc-network-layout { grid-template-columns:1fr; } .cpc-network-details { border-left:0; border-top:1px solid #e5eaf1; } #cpc-leader-network { min-height:520px; } }
@media(max-width:600px) { #cpc-leader-network { min-height:440px; } .cpc-network-toolbar { align-items:flex-start; } }
</style>

{{-- Cytoscape.js: for offline hosting, download this file into public/vendor/cytoscape/cytoscape.min.js --}}
<script src="https://cdn.jsdelivr.net/npm/cytoscape@3.30.4/dist/cytoscape.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const graphData = @json($elements);
    const centralId = @json((string) $leader->leader_id);
    const graphEl = document.getElementById('cpc-leader-network');
    const detailEl = document.getElementById('network-detail-content');

    if (!window.cytoscape || !graphEl) {
        graphEl.innerHTML = '<div class="p-4 text-danger">The graph library could not be loaded. Check the Cytoscape.js CDN connection.</div>';
        return;
    }

    if (!graphData.edges.length) {
        graphEl.innerHTML = '<div class="p-4 text-muted">No relationships were found for this leader at the selected minimum score.</div>';
        return;
    }

    const cy = cytoscape({
        container: graphEl,
        elements: [
            ...graphData.nodes,
            ...graphData.edges
        ],
        minZoom: 0.15,
        maxZoom: 3,
        wheelSensitivity: 0.2,
        style: [
            {
                selector: 'node',
                style: {
                    'width': 'data(size)',
                    'height': 'data(size)',
                    'background-color': '#2b9a8f',
                    'border-width': 2,
                    'border-color': '#ffffff',
                    'label': 'data(name)',
                    'color': '#263548',
                    'font-size': 10,
                    'font-weight': 600,
                    'text-wrap': 'wrap',
                    'text-max-width': 105,
                    'text-valign': 'bottom',
                    'text-halign': 'center',
                    'text-margin-y': 7,
                    'overlay-opacity': 0
                }
            },
            {
                selector: 'node[?isCentral]',
                style: {
                    'background-color': '#173b61',
                    'border-color': '#c6a44a',
                    'border-width': 4,
                    'color': '#173b61',
                    'font-size': 13,
                    'font-weight': 800,
                    'z-index': 10
                }
            },
            {
                selector: 'edge',
                style: {
                    'width': 'data(width)',
                    'line-color': '#aab8c8',
                    'curve-style': 'bezier',
                    'opacity': 0.72,
                    'target-arrow-shape': 'none'
                }
            },
            {
                selector: 'edge:selected',
                style: {
                    'line-color': '#c49a36',
                    'opacity': 1
                }
            },
            {
                selector: 'node:selected',
                style: {
                    'border-color': '#c49a36',
                    'border-width': 4
                }
            }
        ],
        layout: {
            name: 'concentric',
            concentric: function (node) {
                return node.id() === centralId ? 100 : 1;
            },
            levelWidth: function () { return 1; },
            minNodeSpacing: 28,
            padding: 55,
            animate: false
        }
    });

    function addDetailBlock(title, value) {
        if (value === null || value === undefined || value === '') return '';
        return '<div class="network-detail-block"><strong>' + escapeHtml(title) + '</strong><p>' + escapeHtml(String(value)) + '</p></div>';
    }

    function escapeHtml(value) {
        return value.replace(/[&<>"']/g, function (char) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]);
        });
    }

    function showNode(node) {
        const d = node.data();
        const profileUrl = '/leaders/' + encodeURIComponent(d.id);

        detailEl.innerHTML =
            '<div class="network-detail-name">' + escapeHtml(d.name || d.id) + '</div>' +
            (d.chineseName ? '<div class="small text-muted">' + escapeHtml(d.chineseName) + '</div>' : '') +
            addDetailBlock('Current position', d.position) +
            addDetailBlock('Rank', d.rank) +
            (d.isCentral
                ? '<div class="network-detail-block"><strong>Selected leader</strong><p>This is the centre of the current network view.</p></div>'
                : '<div class="network-detail-block"><a class="btn btn-sm btn-primary" href="' + profileUrl + '">Open leader profile</a></div>');
    }

    function showEdge(edge) {
        const d = edge.data();
        detailEl.innerHTML =
            '<div class="network-detail-label">Relationship score</div>' +
            '<div class="network-detail-score">' + Number(d.score).toLocaleString() + '</div>' +
            '<div class="small text-muted">Original score supplied in the relationship CSV</div>' +
            addDetailBlock('Relationship types', d.relationshipTypes) +
            addDetailBlock('Total evidence records', d.totalEvidence) +
            addDetailBlock('Career evidence records', d.careerEvidence) +
            addDetailBlock('Education evidence records', d.educationEvidence) +
            addDetailBlock('Evidence details', d.evidenceDetails);
    }

    cy.on('tap', 'node', function (event) { showNode(event.target); });
    cy.on('tap', 'edge', function (event) { showEdge(event.target); });

    document.getElementById('network-fit').addEventListener('click', function () {
        cy.fit(undefined, 55);
    });

    document.getElementById('network-reset').addEventListener('click', function () {
        cy.layout({
            name: 'concentric',
            concentric: function (node) { return node.id() === centralId ? 100 : 1; },
            levelWidth: function () { return 1; },
            minNodeSpacing: 28,
            padding: 55,
            animate: true
        }).run();
        cy.fit(undefined, 55);
        showNode(cy.getElementById(centralId));
    });

    cy.ready(function () {
        cy.fit(undefined, 55);
        showNode(cy.getElementById(centralId));
    });
});
</script>
@endsection
