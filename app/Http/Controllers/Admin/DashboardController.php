<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller {
    public function index(){
        $stats=['leaders'=>DB::table('leaders')->count(),'career'=>DB::table('career_history')->count(),'education'=>DB::table('education_training')->count(),'network'=>DB::table('political_networks')->count(),'inferred'=>DB::table('inferred_data')->count()];
        $quality=['missing_chinese_name'=>DB::table('leaders')->whereNull('chinese_name')->orWhere('chinese_name','')->count(),'missing_status'=>DB::table('leaders')->whereNull('status')->orWhere('status','')->count(),'missing_province'=>DB::table('leaders')->whereNull('province_of_employment')->orWhere('province_of_employment','')->count(),'without_career'=>DB::table('leaders')->whereNotExists(function($q){$q->select(DB::raw(1))->from('career_history')->whereColumn('career_history.leader_id','leaders.leader_id');})->count(),'without_education'=>DB::table('leaders')->whereNotExists(function($q){$q->select(DB::raw(1))->from('education_training')->whereColumn('education_training.leader_id','leaders.leader_id');})->count()];
        $recentImports=DB::table('import_history')->orderByDesc('created_at')->limit(8)->get(); $recentAudit=DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.user_id')->select('audit_logs.*','users.name as user_name')->orderByDesc('audit_logs.id')->limit(8)->get();
        return view('admin.dashboard.index',compact('stats','quality','recentImports','recentAudit'));
    }
}
