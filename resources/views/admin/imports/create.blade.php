@extends('admin.layout')
@section('title','New Data Import')
@section('content')
<div class="page-head"><div><h1>New Data Import</h1><p>Upload CSV files into a private staging area, map columns, validate them, review differences, then approve the import.</p></div><a class="btn" href="{{ route('admin.imports.index') }}">Staging Tables</a></div>
<div class="panel import-panel">
<form method="post" action="{{ route('admin.imports.upload') }}" enctype="multipart/form-data">
@csrf
<label>Dataset
<select name="dataset" required>
<option value="">Select dataset</option>
@foreach($datasets as $key=>$dataset)<option value="{{ $key }}">{{ $dataset['label'] }} — {{ $dataset['table'] }}</option>@endforeach
</select></label>
<label>CSV file <input type="file" name="file" accept=".csv,.txt" required></label>
<div class="notice"><strong>Safe import flow:</strong> the upload is stored under <code>storage/app</code>, not <code>public</code>. Nothing is written to production until you approve the preview.</div>
<button class="btn primary" type="submit">Upload &amp; Continue</button>
</form></div>
<div class="panel"><h2>Import rules</h2><ul class="checklist"><li>Maximum preview: 5,000 rows per upload.</li><li>Leader IDs are required.</li><li>Related datasets must reference an existing leader.</li><li>Existing biodata is compared field-by-field before approval.</li><li>Career records use <code>leader_id + seq</code> as their natural key.</li><li>Education rows are matched by leader, type, institution and start year.</li><li>Network and inferred records are updated by leader ID.</li><li>All production writes run inside a database transaction and roll back on failure.</li></ul></div>
@endsection
