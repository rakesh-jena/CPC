<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AuditController extends Controller {
    public function index(Request $r){ $q=trim($r->input('q','')); $action=$r->input('action',''); $query=DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.user_id')->select('audit_logs.*','users.name as user_name','users.email as user_email'); if($q!=='')$query->where(function($w)use($q){$w->where('audit_logs.entity_id','ilike','%'.$q.'%')->orWhere('audit_logs.description','ilike','%'.$q.'%')->orWhere('users.name','ilike','%'.$q.'%');}); if($action!=='')$query->where('audit_logs.action',$action); $logs=$query->orderByDesc('audit_logs.id')->paginate(50)->withQueryString(); $actions=DB::table('audit_logs')->distinct()->orderBy('action')->pluck('action'); return view('admin.audit.index',compact('logs','q','action','actions')); }
    public function show($id){ $log=DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.user_id')->select('audit_logs.*','users.name as user_name','users.email as user_email')->where('audit_logs.id',$id)->firstOrFail(); return view('admin.audit.show',compact('log')); }
}
