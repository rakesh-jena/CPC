<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ImportHistoryController extends Controller {
    public function index(Request $r){
        $status=$r->input('status',''); $dataset=$r->input('dataset',''); $term=trim($r->input('q',''));
        $q=DB::table('import_history')->leftJoin('users','users.id','=','import_history.user_id')->select('import_history.*','users.name as user_name');
        if($status!=='') $q->where('import_history.status',$status);
        if($dataset!=='') $q->where('import_history.dataset',$dataset);
        if($term!=='') $q->where(function($w)use($term){$w->where('import_history.filename','ilike','%'.$term.'%')->orWhere('users.name','ilike','%'.$term.'%')->orWhere('import_history.error_summary','ilike','%'.$term.'%');});
        $imports=$q->orderByDesc('created_at')->paginate(30)->withQueryString();
        $statuses=DB::table('import_history')->distinct()->orderBy('status')->pluck('status');
        $datasets=DB::table('import_history')->distinct()->orderBy('dataset')->pluck('dataset');
        return view('admin.imports.history',compact('imports','status','dataset','term','statuses','datasets'));
    }
    public function show($id){ $import=DB::table('import_history')->leftJoin('users as u','u.id','=','import_history.user_id')->leftJoin('users as a','a.id','=','import_history.approved_by')->select('import_history.*','u.name as user_name','a.name as approver_name')->where('import_history.id',$id)->firstOrFail(); return view('admin.imports.history-show',compact('import')); }
}
