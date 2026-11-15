<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder {
    public function run(){
        $email=env('ADMIN_EMAIL','admin@example.com');
        $password=env('ADMIN_PASSWORD','ChangeMeNow!123');
        $name=env('ADMIN_NAME','CPC Administrator');
        $existing=DB::table('users')->where('email',$email)->first();
        if($existing){ DB::table('users')->where('id',$existing->id)->update(['role'=>'super_admin','is_active'=>true,'updated_at'=>now()]); return; }
        DB::table('users')->insert(['name'=>$name,'email'=>$email,'password'=>Hash::make($password),'role'=>'super_admin','is_active'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }
}
