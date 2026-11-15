<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    private $roles=['super_admin','reviewer','editor','analyst'];
    public function index(Request $r){
        $term=trim($r->input('q','')); $role=$r->input('role',''); $active=$r->input('active','');
        $query=DB::table('users');
        if($term!=='') $query->where(function($w)use($term){$w->where('name','ilike','%'.$term.'%')->orWhere('email','ilike','%'.$term.'%');});
        if($role!=='') $query->where('role',$role);
        if($active!=='') $query->where('is_active',$active==='1');
        $users=$query->orderBy('name')->paginate(25)->withQueryString();
        return view('admin.users.index',compact('users','term','role','active'));
    }
    public function create(){ return view('admin.users.form',['user'=>null,'roles'=>$this->roles]); }
    public function store(Request $r){
        $d=$r->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','password'=>'required|string|min:8|confirmed','role'=>'required|in:super_admin,reviewer,editor,analyst']);
        $id=DB::table('users')->insertGetId(['name'=>$d['name'],'email'=>$d['email'],'password'=>Hash::make($d['password']),'role'=>$d['role'],'is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
        AuditService::log('created','user',$id,null,['name'=>$d['name'],'email'=>$d['email'],'role'=>$d['role']]); return redirect()->route('admin.users.index')->with('success','Admin user created.');
    }
    public function edit($id){ $user=DB::table('users')->find($id); abort_unless($user,404); return view('admin.users.form',compact('user')+['roles'=>$this->roles]); }
    public function update(Request $r,$id){
        $user=DB::table('users')->find($id); abort_unless($user,404);
        $d=$r->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email,'.$id,'password'=>'nullable|string|min:8|confirmed','role'=>'required|in:super_admin,reviewer,editor,analyst','is_active'=>'nullable|boolean']);
        if($id==auth()->id() && empty($d['is_active'])) $d['is_active']=true;
        $old=(array)$user; $update=['name'=>$d['name'],'email'=>$d['email'],'role'=>$d['role'],'is_active'=>$d['is_active']??false,'updated_at'=>now()]; if(!empty($d['password'])) $update['password']=Hash::make($d['password']); DB::table('users')->where('id',$id)->update($update);
        AuditService::log('updated','user',$id,$old,$update); return redirect()->route('admin.users.index')->with('success','Admin user updated.');
    }
    public function destroy($id){ abort_if($id==auth()->id(),422,'You cannot delete your own account.'); $user=DB::table('users')->find($id); abort_unless($user,404); DB::table('users')->where('id',$id)->delete(); AuditService::log('deleted','user',$id,(array)$user,null); return back()->with('success','Admin user deleted.'); }
}
