<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;
class PoliticalNetworkController extends Controller {
    private $fields=['sent_down_youth','location','time_period','overseas_education','overseas_location','overseas_time_period','primary_patron','secondary_patron','major_political_network','zhejiang_network','fujian_network','shanghai_network','communist_youth_league','tsinghua_network','personal_business_holdings','family_business_holdings','offshore_assets','property_holdings','confidence_level','investigated','party_disciplinary_action','associates_purged','patron_purged','books_published','academic_paper_published','us_sanctions','eu_sanctions','expected_retirement_congress'];
    private function leader($id){return DB::table('leaders')->where('leader_id',$id)->firstOrFail();}
    public function edit($leaderId){$leader=$this->leader($leaderId);$network=DB::table('political_networks')->where('leader_id',$leaderId)->first();return view('admin.network.form',compact('leader','network'));}
    public function update(Request $r,$leaderId){$this->leader($leaderId);$d=$r->validate(array_fill_keys($this->fields,'nullable'));$old=(array)DB::table('political_networks')->where('leader_id',$leaderId)->first();DB::table('political_networks')->updateOrInsert(['leader_id'=>$leaderId],$d);AuditService::log('updated','political_networks',$leaderId,$old,$d,'Political network data saved');return redirect()->route('admin.leaders.show',$leaderId)->with('success','Political network data saved.');}
    public function destroy($leaderId){$this->leader($leaderId);$old=(array)DB::table('political_networks')->where('leader_id',$leaderId)->first();DB::table('political_networks')->where('leader_id',$leaderId)->delete();AuditService::log('deleted','political_networks',$leaderId,$old,null,'Political network data deleted');return back()->with('success','Political network data deleted.');}
}
