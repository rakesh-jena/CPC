<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Throwable;

class ImportController extends Controller
{
    private $datasets = [
        'biodata' => ['table' => 'biodata_import', 'target' => 'leaders', 'label' => 'Leader Biodata'],
        'career' => ['table' => 'career_import', 'target' => 'career_history', 'label' => 'Career History'],
        'education' => ['table' => 'education_import', 'target' => 'education_training', 'label' => 'Education & Training'],
        'inferred_data' => ['table' => 'inferred_data_import', 'target' => 'inferred_data', 'label' => 'Inferred Data'],
        'political_networks' => ['table' => 'political_networks_import', 'target' => 'political_networks', 'label' => 'Political Networks'],
    ];

    private $fields = [
        'biodata' => [
            'leader_id','english_name','chinese_name','status','gender','dob','age','birth_location','birth_province','ancestral_home','ethnicity','cpc_membership_year','current_position','concurrent_position_1','concurrent_position_2','concurrent_position_3','rank','province_of_employment','father_name','father_position','father_rank','mother_name','mother_position','revolutionary_family','princeling','spouse_name','spouse_position','number_of_children','children_details','children_occupation','personal_business_holdings','family_business_holdings','offshore_assets','property_holdings','sent_down_youth','sent_down_location','sent_down_time_period','overseas_education','overseas_location','overseas_time_period','diplomatic_posting','foreign_languages','foreign_trips_post_2022'
        ],
        'career' => ['leader_id','seq','position','department','province','start_year','end_year'],
        'education' => ['leader_id','type','institution','degree','major','location','start_year','end_year'],
        'inferred_data' => [
            'leader_id','stem_background','central_party_school','provincial_party_school','national_academy_of_governance','military_academy','worked_abroad','location','time_period','diplomatic_posting','foreign_languages','foreign_trips_post_2022','first_province','total_appointments','total_working_years_party_member','total_provinces_served','local_government_experience','provincial_government_experience','provincial_party_experience','central_government_experience','central_party_experience','soe_experience','pla_experience','economic_policy_experience','financial_policy_experience','technology_policy_experience','foreign_affairs_experience','propaganda_experience','party_organization_experience','ccdi_experience','internal_security_experience','agriculture_rural_affairs','industrial_policy_experience','pla_service_experience','central_military_commission_experience','defence_industry_experience','coastal_experience','inland_experience','border_province_experience','minority_region_experience','municipality_experience','years_to_county','years_to_prefecture','years_to_province','years_to_center','years_to_politburo','years_to_psc','year_entered_politburo','year_entered_psc','served_in_secretariat','served_in_ccdi_leadership','served_in_npc_leadership','served_in_cppcc_leadership','member_of_cmc','xi_proximity_score','factional_affiliation_score','promotion_probability','retirement_probability','successor_potential'
        ],
        'political_networks' => [
            'leader_id','sent_down_youth','location','time_period','overseas_education','overseas_location','overseas_time_period','primary_patron','secondary_patron','major_political_network','zhejiang_network','fujian_network','shanghai_network','communist_youth_league','tsinghua_network','personal_business_holdings','family_business_holdings','offshore_assets','property_holdings','confidence_level','investigated','party_disciplinary_action','associates_purged','patron_purged','books_published','academic_paper_published','us_sanctions','eu_sanctions','expected_retirement_congress'
        ],
    ];

    public function index()
    {
        $counts = [];
        foreach ($this->datasets as $key => $dataset) {
            $counts[$dataset['table']] = DB::table($dataset['table'])->count();
        }
        return view('admin.imports.index', compact('counts'));
    }

    public function show($table)
    {
        abort_unless(in_array($table, array_column($this->datasets, 'table')), 404);
        $rows = DB::table($table)->paginate(50);
        return view('admin.imports.show', compact('table', 'rows'));
    }

    public function clear($table)
    {
        abort_unless(in_array($table, array_column($this->datasets, 'table')), 404);
        DB::table($table)->delete();
        AuditService::log('cleared_staging',$table,null,null,null,'Cleared staging table '.$table);
        return back()->with('success', "Cleared {$table}.");
    }

    public function create()
    {
        return view('admin.imports.create', ['datasets' => $this->datasets]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'dataset' => 'required|in:biodata,career,education,inferred_data,political_networks',
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $dataset = $request->dataset;
        $uploaded=$request->file('file');
        $path = $uploaded->store('admin-imports');
        $absolute = Storage::path($path);
        $historyId=(string)Str::uuid();
        DB::table('import_history')->insert(['id'=>$historyId,'user_id'=>auth()->id(),'dataset'=>$dataset,'filename'=>$uploaded->getClientOriginalName(),'storage_path'=>$path,'file_hash'=>hash_file('sha256',$absolute),'status'=>'uploaded','created_at'=>now(),'updated_at'=>now()]);
        [$headers, $rows, $errors] = $this->readCsv($absolute, 5000);

        if (!$headers) {
            Storage::delete($path);
            return back()->withErrors(['file' => 'The CSV file is empty or has no header row.']);
        }

        $mapping = $this->autoMap($dataset, $headers);
        $token = (string) Str::uuid();
        session()->put('cpc_import.' . $token, [
            'dataset' => $dataset,
            'path' => $path,
            'headers' => $headers,
            'mapping' => $mapping,
            'rows' => $rows,
            'read_errors' => $errors,
            'created_at' => now()->toIso8601String(),
            'history_id' => $historyId,
        ]);

        return redirect()->route('admin.imports.map', $token);
    }

    public function map($token)
    {
        $job = session('cpc_import.' . $token);
        abort_unless($job, 404);
        return view('admin.imports.map', [
            'token' => $token,
            'job' => $job,
            'fields' => $this->fields[$job['dataset']],
            'label' => $this->datasets[$job['dataset']]['label'],
        ]);
    }

    public function preview(Request $request, $token)
    {
        $job = session('cpc_import.' . $token);
        abort_unless($job, 404);
        $fields = $this->fields[$job['dataset']];
        $rules = [];
        foreach ($fields as $field) {
            $rules['mapping.' . $field] = 'nullable|integer|min:0|max:' . max(0, count($job['headers']) - 1);
        }
        $request->validate($rules);
        $job['mapping'] = $request->input('mapping', []);
        DB::table('import_history')->where('id',$job['history_id'])->update(['status'=>'previewed','row_count'=>count($job['rows']),'updated_at'=>now()]);
        session()->put('cpc_import.' . $token, $job);

        [$mappedRows, $validationErrors, $duplicates, $diffs] = $this->prepare($job);
        return view('admin.imports.preview', compact('token','job','mappedRows','validationErrors','duplicates','diffs'));
    }

    public function commit(Request $request, $token)
    {
        $job = session('cpc_import.' . $token);
        abort_unless($job, 404);
        $request->validate(['replace_related' => 'nullable|boolean']);
        [$mappedRows, $validationErrors, $duplicates] = $this->prepare($job, true);

        if ($validationErrors || $duplicates) {
            DB::table('import_history')->where('id',$job['history_id'])->update(['status'=>'blocked','error_count'=>count($validationErrors),'duplicate_count'=>count($duplicates),'error_summary'=>substr(implode(' | ',array_merge($validationErrors,$duplicates)),0,5000),'updated_at'=>now()]);
            return redirect()->route('admin.imports.preview', $token)
                ->withErrors(['import' => 'Import blocked because validation errors or duplicate rows remain.']);
        }

        $dataset = $job['dataset'];
        $replace = $request->boolean('replace_related');
        $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        DB::table('import_history')->where('id',$job['history_id'])->update(['status'=>'approved','approved_at'=>now(),'approved_by'=>auth()->id(),'updated_at'=>now()]);

        try {
            DB::transaction(function () use ($dataset, $mappedRows, $replace, &$counts) {
                $target = $this->datasets[$dataset]['target'];
                if ($dataset === 'biodata') {
                    foreach ($mappedRows as $row) {
                        $id = $row['leader_id'];
                        $exists = DB::table('leaders')->where('leader_id', $id)->exists();
                        DB::table('leaders')->updateOrInsert(['leader_id' => $id], $this->cleanRow($row, ['leader_id']));
                        $exists ? $counts['updated']++ : $counts['created']++;
                    }
                    return;
                }

                $leaderIds = array_values(array_unique(array_column($mappedRows, 'leader_id')));
                if ($replace && $leaderIds) {
                    DB::table($target)->whereIn('leader_id', $leaderIds)->delete();
                }

                foreach ($mappedRows as $row) {
                    if ($dataset === 'career') {
                        $key = ['leader_id' => $row['leader_id'], 'seq' => (int) $row['seq']];
                        $exists = DB::table($target)->where($key)->exists();
                        DB::table($target)->updateOrInsert($key, $this->cleanRow($row, array_keys($key)));
                    } elseif ($dataset === 'education') {
                        $query = DB::table($target)->where('leader_id', $row['leader_id'])->where('type', $row['type'])->where('institution', $row['institution']);
                        if ($row['start_year'] !== null) $query->where('start_year', $row['start_year']);
                        $existing = $query->first();
                        if ($existing) {
                            DB::table($target)->where('education_id', $existing->education_id)->update($this->cleanRow($row, ['leader_id']));
                            $exists = true;
                        } else {
                            DB::table($target)->insert($this->cleanRow($row));
                            $exists = false;
                        }
                    } else {
                        $key = ['leader_id' => $row['leader_id']];
                        $exists = DB::table($target)->where($key)->exists();
                        DB::table($target)->updateOrInsert($key, $this->cleanRow($row, ['leader_id']));
                    }
                    $exists ? $counts['updated']++ : $counts['created']++;
                }
            });
        } catch (Throwable $e) {
            DB::table('import_history')->where('id',$job['history_id'])->update(['status'=>'failed','error_count'=>1,'error_summary'=>substr($e->getMessage(),0,5000),'updated_at'=>now()]);
            report($e);
            return back()->withErrors(['import' => 'Import failed and was rolled back. Check the Laravel log for details.']);
        }

        DB::table('import_history')->where('id',$job['history_id'])->update(['status'=>'completed','created_count'=>$counts['created'],'updated_count'=>$counts['updated'],'skipped_count'=>$counts['skipped'],'completed_at'=>now(),'updated_at'=>now()]);
        AuditService::log('import_completed','import_history',$job['history_id'],null,$counts,'CSV import completed');
        if (!empty($job['path'])) Storage::delete($job['path']);
        session()->forget('cpc_import.' . $token);

        return redirect()->route('admin.imports.create')->with('success', "Import completed: {$counts['created']} created, {$counts['updated']} updated.");
    }

    public function cancel($token)
    {
        $job = session()->pull('cpc_import.' . $token);
        if ($job) { DB::table('import_history')->where('id',$job['history_id'])->update(['status'=>'cancelled','updated_at'=>now()]); if (!empty($job['path'])) Storage::delete($job['path']); }
        return redirect()->route('admin.imports.create')->with('success', 'Import cancelled; production data was not changed.');
    }

    private function readCsv($path, $limit)
    {
        $fh = fopen($path, 'r');
        $headers = fgetcsv($fh);
        $headers = array_map(function ($v) { return trim((string) $v, " \t\n\r\0\x0B\"\xEF\xBB\xBF"); }, $headers ?: []);
        $rows = [];
        $errors = [];
        $line = 1;
        while (($row = fgetcsv($fh)) !== false && count($rows) < $limit) {
            $line++;
            if (count($row) > count($headers)) {
                $errors[] = "Line {$line}: more values than headers.";
                continue;
            }
            $row = array_pad($row, count($headers), null);
            $rows[] = array_map(function ($v) { return is_string($v) ? trim($v) : $v; }, $row);
        }
        fclose($fh);
        return [$headers, $rows, $errors];
    }

    private function autoMap($dataset, $headers)
    {
        $mapping = [];
        foreach ($this->fields[$dataset] as $field) {
            $aliases = [$field, str_replace('_', ' ', $field), strtolower(str_replace('_', ' ', $field))];
            if ($dataset === 'biodata') {
                $aliases = array_merge($aliases, [
                    'Leader ID' => 'leader_id', 'English Name' => 'english_name', 'Chinese Name (Simplified)' => 'chinese_name', 'DOB' => 'dob',
                ]);
            }
            if ($dataset === 'career') $aliases = array_merge($aliases, ['Leader_ID' => 'leader_id', 'Seq' => 'seq', 'Start_Year' => 'start_year', 'End_Year' => 'end_year']);
            if ($dataset === 'education') $aliases = array_merge($aliases, ['Leader_ID' => 'leader_id', 'Start_Year' => 'start_year', 'End_Year' => 'end_year']);
            $found = null;
            foreach ($headers as $i => $header) {
                $norm = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($header)));
                foreach ($aliases as $aliasKey => $aliasValue) {
                    $candidate = is_string($aliasKey) ? $aliasKey : $aliasValue;
                    $candidate = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($candidate)));
                    if ($norm === $candidate || $norm === strtolower($field)) { $found = $i; break 2; }
                }
            }
            $mapping[$field] = $found;
        }
        return $mapping;
    }

    private function prepare($job, $forCommit = false)
    {
        $mappedRows = [];
        $validationErrors = $job['read_errors'];
        $duplicates = [];
        $diffs = [];
        $seen = [];
        $fields = $this->fields[$job['dataset']];

        foreach ($job['rows'] as $index => $raw) {
            $row = [];
            foreach ($fields as $field) {
                $idx = array_key_exists($field, $job['mapping']) ? $job['mapping'][$field] : null;
                $value = ($idx !== null && $idx !== '' && isset($raw[$idx])) ? trim((string) $raw[$idx]) : null;
                $row[$field] = $this->normaliseValue($field, $value);
            }
            $line = $index + 2;
            if (!$row['leader_id']) $validationErrors[] = "Line {$line}: leader_id is required.";
            if ($job['dataset'] === 'career' && (!is_numeric($row['seq']) || (int)$row['seq'] < 1)) $validationErrors[] = "Line {$line}: seq must be a positive integer.";
            if (in_array($job['dataset'], ['biodata','career','education','inferred_data','political_networks'], true) && $row['leader_id'] && !DB::table('leaders')->where('leader_id',$row['leader_id'])->exists() && $job['dataset'] !== 'biodata') {
                $validationErrors[] = "Line {$line}: leader_id {$row['leader_id']} does not exist in leaders.";
            }

            $key = $this->duplicateKey($job['dataset'], $row);
            if ($key && isset($seen[$key])) $duplicates[] = "Line {$line}: duplicate of line {$seen[$key]} ({$key}).";
            if ($key) $seen[$key] = $line;

            if ($job['dataset'] === 'biodata' && $row['leader_id']) {
                $existing = DB::table('leaders')->where('leader_id',$row['leader_id'])->first();
                if ($existing) {
                    $changes = [];
                    foreach ($fields as $field) {
                        $new = $row[$field]; $old = $existing->{$field};
                        if ((string)$new !== (string)$old) $changes[$field] = ['old'=>$old, 'new'=>$new];
                    }
                    $diffs[$line] = $changes;
                }
            }
            $mappedRows[] = $row;
        }
        if (count($job['rows']) >= 5000) $validationErrors[] = 'Preview is limited to 5,000 rows. Split larger files before importing.';
        return [$mappedRows, $validationErrors, $duplicates, $diffs];
    }

    private function duplicateKey($dataset, $row)
    {
        if (!$row['leader_id']) return null;
        if ($dataset === 'career') return $row['leader_id'] . ':' . $row['seq'];
        if ($dataset === 'education') return implode(':', [$row['leader_id'],$row['type'],$row['institution'],$row['start_year']]);
        return $row['leader_id'];
    }

    private function cleanRow($row, $exclude = [])
    {
        foreach ($exclude as $key) unset($row[$key]);
        return $row;
    }

    private function normaliseValue($field, $value)
    {
        if ($value === null || $value === '') return null;
        if (in_array($field, ['age','cpc_membership_year','seq','start_year','end_year','years_to_county','years_to_prefecture','years_to_province','years_to_center','years_to_politburo','years_to_psc','year_entered_politburo','year_entered_psc'], true)) {
            return is_numeric($value) ? (int)$value : $value;
        }
        return $value;
    }
}
