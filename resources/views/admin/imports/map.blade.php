@extends('admin.layout')
@section('title','Map Import Columns')
@section('content')
<div class="page-head"><div><h1>Map Columns</h1><p>{{ $label }} · {{ count($job['rows']) }} rows detected.</p></div><a class="btn" href="{{ route('admin.imports.create') }}">Cancel</a></div>
<form method="post" action="{{ route('admin.imports.preview',$token) }}">
@csrf
<div class="panel"><h2>Source → database mapping</h2><p class="muted">Automatic matches are pre-selected. Leave a field blank when the source does not contain it.</p>
<div class="mapping-grid">
@foreach($fields as $field)
<label><span>{{ $field }}</span><select name="mapping[{{ $field }}]"><option value="">— Not mapped —</option>@foreach($job['headers'] as $i=>$header)<option value="{{ $i }}" @if(($job['mapping'][$field] ?? null) === $i) selected @endif>{{ $header }}</option>@endforeach</select></label>
@endforeach
</div>
</div>
<div class="sticky-actions"><button class="btn primary" type="submit">Validate &amp; Preview</button></div>
</form>
@endsection
