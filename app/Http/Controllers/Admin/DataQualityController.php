<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DataQualityController extends Controller {
    public function index(){
        $checks=[];
        $checks[]=['label'=>'Leaders without Chinese name','count'=>DB::table('leaders')->whereNull('chinese_name')->orWhere('chinese_name','')->count(),'route'=>route('admin.leaders.index',['q'=>''])];
        $checks[]=['label'=>'Leaders without status','count'=>DB::table('leaders')->whereNull('status')->orWhere('status','')->count(),'route'=>route('admin.leaders.index')];
        $checks[]=['label'=>'Leaders without employment province','count'=>DB::table('leaders')->whereNull('province_of_employment')->orWhere('province_of_employment','')->count(),'route'=>route('admin.leaders.index')];
        $checks[]=['label'=>'Career rows with missing position','count'=>DB::table('career_history')->whereNull('position')->orWhere('position','')->count(),'route'=>route('admin.imports.show','career_import')];
        $checks[]=['label'=>'Education rows with missing institution','count'=>DB::table('education_training')->whereNull('institution')->orWhere('institution','')->count(),'route'=>route('admin.imports.show','education_import')];
        $orphanCareer=DB::table('career_history')->leftJoin('leaders','leaders.leader_id','=','career_history.leader_id')->whereNull('leaders.leader_id')->count();
        $orphanEducation=DB::table('education_training')->leftJoin('leaders','leaders.leader_id','=','education_training.leader_id')->whereNull('leaders.leader_id')->count();
        $checks[]=['label'=>'Orphan career records','count'=>$orphanCareer,'route'=>route('admin.imports.show','career_import')];
        $checks[]=['label'=>'Orphan education records','count'=>$orphanEducation,'route'=>route('admin.imports.show','education_import')];
        return view('admin.data-quality.index',compact('checks'));
    }
}
