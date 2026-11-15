<?php
namespace App\Services;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuditService {
    public static function log($action,$entityType=null,$entityId=null,$old=null,$new=null,$description=null){
        if (!DB::getSchemaBuilder()->hasTable('audit_logs')) return;
        DB::table('audit_logs')->insert([
            'user_id'=>Auth::id(), 'action'=>$action, 'entity_type'=>$entityType, 'entity_id'=>$entityId,
            'old_values'=>$old===null?null:json_encode($old,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'new_values'=>$new===null?null:json_encode($new,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'description'=>$description, 'ip_address'=>request()->ip(), 'user_agent'=>substr((string)request()->userAgent(),0,1000),
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
    }
}
