<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;
class EducationController extends Controller {
    private function leader($id){ return DB::table('leaders')->where('leader_id',$id)->firstOrFail(); }
    private function rules(){ return ['type'=>'nullable|string','institution'=>'nullable|string','degree'=>'nullable|string','major'=>'nullable|string','location'=>'nullable|string','start_year'=>'nullable|integer|min:1800|max:2100','end_year'=>'nullable|integer|min:1800|max:2100']; }
    public function create($leaderId){$leader=$this->leader($leaderId);return view('admin.education.form',compact('leader')+['education'=>null,'mode'=>'create']);}
    public function store(Request $r,$leaderId){$this->leader($leaderId);$d=$r->validate($this->rules()); $id=DB::table('education_training')->insertGetId(array_merge(['leader_id'=>$leaderId],$d),'education_id');AuditService::log('created','education_training',$id,null,array_merge(['leader_id'=>$leaderId],$d),'Education record added');return redirect()->route('admin.leaders.show',$leaderId)->with('success','Education record added (#'.$id.').');}
    public function edit($leaderId,$educationId){$leader=$this->leader($leaderId);$education=DB::table('education_training')->where('leader_id',$leaderId)->where('education_id',$educationId)->firstOrFail();return view('admin.education.form',compact('leader','education')+['mode'=>'edit']);}
    public function update(Request $r,$leaderId,$educationId){$this->leader($leaderId);$d=$r->validate($this->rules());$old=(array)DB::table('education_training')->where('leader_id',$leaderId)->where('education_id',$educationId)->first();DB::table('education_training')->where('leader_id',$leaderId)->where('education_id',$educationId)->update($d);AuditService::log('updated','education_training',$educationId,$old,$d,'Education record updated');return redirect()->route('admin.leaders.show',$leaderId)->with('success','Education record updated.');}
    public function destroy($leaderId,$educationId){$this->leader($leaderId);$old=(array)DB::table('education_training')->where('leader_id',$leaderId)->where('education_id',$educationId)->first();DB::table('education_training')->where('leader_id',$leaderId)->where('education_id',$educationId)->delete();AuditService::log('deleted','education_training',$educationId,$old,null,'Education record deleted');return back()->with('success','Education record deleted.');}
    public function bulkDestroy(Request $r,$leaderId){$this->leader($leaderId);$ids=(array)$r->input('education_ids');$r->validate(['education_ids'=>'required|array|min:1']);DB::table('education_training')->where('leader_id',$leaderId)->whereIn('education_id',$ids)->delete();return back()->with('success',count($ids).' education records deleted.');}
}
