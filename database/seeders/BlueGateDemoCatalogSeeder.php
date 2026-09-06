<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class BlueGateDemoCatalogSeeder extends Seeder {
 public function run(): void {
  $products=[
   ['slug'=>'blueping-standard','name'=>'BluePing Standard','description'=>'اتصال اقتصادی و پایدار برای استفاده روزمره.','sort'=>10],
   ['slug'=>'blueping-pro','name'=>'BluePing Pro','description'=>'سرویس حرفه‌ای برای پایداری بیشتر، استریم و استفاده سنگین.','sort'=>20],
   ['slug'=>'blueping-iran','name'=>'BluePing Iran','description'=>'دسترسی با IP ایران برای سرویس‌های داخلی.','sort'=>30],
  ];
  foreach($products as $p){
   $existing=DB::table('products')->where('slug',$p['slug'])->first();
   $id=$existing?->id ?: (string)Str::uuid();
   if($existing){ DB::table('products')->where('id',$id)->update(['name'=>$p['name'],'description'=>$p['description'],'category'=>'network_service','active'=>true,'sort_order'=>$p['sort'],'updated_at'=>now()]); }
   else { DB::table('products')->insert(['id'=>$id,'slug'=>$p['slug'],'name'=>$p['name'],'description'=>$p['description'],'category'=>'network_service','active'=>true,'sort_order'=>$p['sort'],'created_at'=>now(),'updated_at'=>now()]); }
   if(!DB::table('plans')->where('product_id',$id)->exists()){
    $prices=$p['slug']==='blueping-standard' ? [[30,30,149000],[50,30,249000],[100,30,499000]] : ($p['slug']==='blueping-pro' ? [[30,30,269000],[50,30,399000],[100,30,699000]] : [[5,30,199000],[20,30,449000],[50,30,799000]]);
    foreach($prices as [$gb,$days,$price]) DB::table('plans')->insert(['id'=>(string)Str::uuid(),'product_id'=>$id,'name'=>"{$gb} GB / {$days} روز",'traffic_gb'=>$gb,'duration_days'=>$days,'device_limit'=>2,'base_price'=>$price,'currency'=>'IRR','active'=>true,'renewable'=>true,'upgradeable'=>true,'created_at'=>now(),'updated_at'=>now()]);
   }
  }
 }
}
