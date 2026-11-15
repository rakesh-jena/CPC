<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;

class LeaderController extends Controller
{
    private $fields = [
        'english_name','chinese_name','status','gender','dob','age','birth_location','birth_province','ancestral_home','ethnicity',
        'cpc_membership_year','current_position','concurrent_position_1','concurrent_position_2','concurrent_position_3','rank','province_of_employment',
        'father_name','father_position','father_rank','mother_name','mother_position','revolutionary_family','princeling','spouse_name','spouse_position',
        'number_of_children','children_details','children_occupation','personal_business_holdings','family_business_holdings','offshore_assets','property_holdings',
        'sent_down_youth','sent_down_location','sent_down_time_period','overseas_education','overseas_location','overseas_time_period','diplomatic_posting',
        'foreign_languages','foreign_trips_post_2022'
    ];

    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));
        $status = trim($request->input('status', ''));
        $gender = trim($request->input('gender', ''));
        $province = trim($request->input('province', ''));

        $query = DB::table('leaders')->select('*');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $like = '%' . $q . '%';
                $w->where('leader_id', 'ilike', $like)->orWhere('english_name', 'ilike', $like)
                  ->orWhere('chinese_name', 'ilike', $like)->orWhere('current_position', 'ilike', $like);
            });
        }
        if ($status !== '') $query->whereRaw('trim(status) = ?', [$status]);
        if ($gender !== '') $query->whereRaw('trim(gender) = ?', [$gender]);
        if ($province !== '') $query->whereRaw('trim(province_of_employment) = ?', [$province]);

        $leaders = $query->orderBy('english_name')->paginate(25)->withQueryString();
        $statuses = DB::table('leaders')->select('status')->whereNotNull('status')->distinct()->orderBy('status')->pluck('status');
        $genders = DB::table('leaders')->select('gender')->whereNotNull('gender')->distinct()->orderBy('gender')->pluck('gender');
        $provinces = DB::table('leaders')->select('province_of_employment')->whereNotNull('province_of_employment')->distinct()->orderBy('province_of_employment')->pluck('province_of_employment');

        return view('admin.leaders.index', compact('leaders','q','status','gender','province','statuses','genders','provinces'));
    }

    public function create()
    {
        return view('admin.leaders.form', ['leader' => null, 'mode' => 'create']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $leaderId = trim((string)$request->input('leader_id'));
        $request->validate(['leader_id' => 'required|string|max:100']);
        if (DB::table('leaders')->where('leader_id', $leaderId)->exists()) return back()->withInput()->withErrors(['leader_id' => 'Leader ID already exists.']);
        DB::table('leaders')->insert(array_merge(['leader_id' => $leaderId], $data));
        AuditService::log('created','leader',$leaderId,null,array_merge(['leader_id'=>$leaderId],$data),'Leader created');
        return redirect()->route('admin.leaders.show', $leaderId)->with('success', 'Leader created successfully.');
    }

    public function show($leaderId)
    {
        $leader = DB::table('leaders')->where('leader_id', $leaderId)->firstOrFail();
        $career = DB::table('career_history')->where('leader_id', $leaderId)->orderBy('seq')->get();
        $education = DB::table('education_training')->where('leader_id', $leaderId)->orderBy('start_year')->orderBy('education_id')->get();
        $network = DB::table('political_networks')->where('leader_id', $leaderId)->first();
        $inferred = DB::table('inferred_data')->where('leader_id', $leaderId)->first();
        return view('admin.leaders.show', compact('leader','career','education','network','inferred'));
    }

    public function edit($leaderId)
    {
        $leader = DB::table('leaders')->where('leader_id', $leaderId)->firstOrFail();
        return view('admin.leaders.form', compact('leader') + ['mode' => 'edit']);
    }

    public function update(Request $request, $leaderId)
    {
        DB::table('leaders')->where('leader_id', $leaderId)->firstOrFail();
        $data = $this->validated($request);
        $old=(array)DB::table('leaders')->where('leader_id', $leaderId)->first();
        DB::table('leaders')->where('leader_id', $leaderId)->update($data);
        AuditService::log('updated','leader',$leaderId,$old,$data,'Leader updated');
        return redirect()->route('admin.leaders.show', $leaderId)->with('success', 'Leader updated successfully.');
    }

    public function destroy($leaderId)
    {
        $old=(array)DB::table('leaders')->where('leader_id',$leaderId)->first();
        DB::transaction(function () use ($leaderId) {
            DB::table('career_history')->where('leader_id', $leaderId)->delete();
            DB::table('education_training')->where('leader_id', $leaderId)->delete();
            DB::table('political_networks')->where('leader_id', $leaderId)->delete();
            DB::table('inferred_data')->where('leader_id', $leaderId)->delete();
            DB::table('leaders')->where('leader_id', $leaderId)->delete();
        });
        AuditService::log('deleted','leader',$leaderId,$old,null,'Leader and related records deleted');
        return redirect()->route('admin.leaders.index')->with('success', 'Leader and related records deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = array_values(array_filter((array)$request->input('ids')));
        $request->validate(['ids' => 'required|array|min:1']);
        $before=DB::table('leaders')->whereIn('leader_id',$ids)->get()->map(function($r){return (array)$r;})->all();
        DB::transaction(function () use ($ids) {
            foreach (array_chunk($ids, 100) as $chunk) {
                DB::table('career_history')->whereIn('leader_id', $chunk)->delete();
                DB::table('education_training')->whereIn('leader_id', $chunk)->delete();
                DB::table('political_networks')->whereIn('leader_id', $chunk)->delete();
                DB::table('inferred_data')->whereIn('leader_id', $chunk)->delete();
                DB::table('leaders')->whereIn('leader_id', $chunk)->delete();
            }
        });
        AuditService::log('bulk_deleted','leader',null,$before,null,count($ids).' leaders deleted');
        return back()->with('success', count($ids) . ' leaders deleted.');
    }

    private function validated(Request $request)
    {
        $rules = [];
        foreach ($this->fields as $field) $rules[$field] = 'nullable';
        $rules['age'] = 'nullable|integer|min:0|max:150';
        $rules['cpc_membership_year'] = 'nullable|integer|min:1800|max:2100';
        $rules['number_of_children'] = 'nullable|string|max:100';
        return $request->validate($rules);
    }
}
