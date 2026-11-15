@extends('admin.layout')
@section('title','Import Preview')
@section('content')
<div class="page-head"><div><h1>Import Preview</h1><p>{{ $job['dataset'] }} · {{ count($mappedRows) }} rows</p></div><a class="btn" href="{{ route('admin.imports.map',$token) }}">Edit Mapping</a></div>
<div class="stats-row"><div class="stat"><small>Rows</small><strong>{{ number_format(count($mappedRows)) }}</strong></div><div class="stat"><small>Validation errors</small><strong class="{{ count($validationErrors) ? 'bad' : 'good' }}">{{ count($validationErrors) }}</strong></div><div class="stat"><small>Duplicates in file</small><strong class="{{ count($duplicates) ? 'bad' : 'good' }}">{{ count($duplicates) }}</strong></div><div class="stat"><small>Existing records with changes</small><strong>{{ count($diffs) }}</strong></div></div>
@if($validationErrors)<div class="alert error"><strong>Import blocked.</strong><ul>@foreach(array_slice($validationErrors,0,100) as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@if($duplicates)<div class="alert warning"><strong>Duplicate rows detected.</strong><ul>@foreach(array_slice($duplicates,0,50) as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
@if($job['dataset']==='biodata')
<div class="panel"><h2>Existing leader differences</h2><p class="muted">Only fields whose incoming value differs from the current production value are shown.</p>
@forelse($diffs as $line=>$changes)<details class="diff"><summary>CSV line {{ $line }} · {{ $mappedRows[$line-2]['english_name'] ?? $mappedRows[$line-2]['leader_id'] }}</summary><div class="diff-grid"><div><strong>Field</strong></div><div><strong>Current</strong></div><div><strong>Incoming</strong></div>@forelse($changes as $field=>$change)<div>{{ $field }}</div><div class="old">{{ $change['old'] }}</div><div class="new">{{ $change['new'] }}</div>@empty<div class="muted">No changes.</div>@endforelse</div></details>@empty<p>No existing leaders were changed; rows are new or identical.</p>@endforelse</div>
@endif
<div class="panel"><h2>First 100 mapped rows</h2><div class="table-wrap"><table><thead><tr>@foreach(array_keys($mappedRows[0] ?? []) as $field)<th>{{ $field }}</th>@endforeach</tr></thead><tbody>@foreach(array_slice($mappedRows,0,100) as $row)<tr>@foreach($row as $value)<td class="truncate">{{ $value }}</td>@endforeach</tr>@endforeach</tbody></table></div></div>
<div class="panel approve-panel"><h2>Approve import</h2><p>Production data will be changed only after you click the approval button. The operation is transactional.</p>
<form method="post" action="{{ route('admin.imports.commit',$token) }}" onsubmit="return confirm('Approve this import? Production data will be changed.')">@csrf
@if(in_array($job['dataset'],['career','education','inferred_data','political_networks']))<label class="check"><input type="checkbox" name="replace_related" value="1"> Replace existing related records for the leader IDs in this file <span>Use this when the CSV is a complete replacement, not an incremental update.</span></label>@endif
<button class="btn primary" type="submit" {{ count($validationErrors) ? 'disabled' : '' }}>Approve &amp; Import</button>
<a class="btn danger" href="{{ route('admin.imports.cancel',$token) }}" onclick="event.preventDefault();document.getElementById('cancel-import').submit();">Cancel</a></form>
<form id="cancel-import" method="post" action="{{ route('admin.imports.cancel',$token) }}" class="hidden">@csrf @method('DELETE')</form></div>
@endsection
