<?php
namespace App\Domain\Reseller;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class ResellerBillingService {
 public function debit(int $userId,float $amount,string $description='Reseller API purchase'): void {
  if($amount<=0) return;
  DB::transaction(function() use($userId,$amount,$description){
   $profile=DB::table('reseller_profiles')->where('user_id',$userId)->where('active',true)->lockForUpdate()->firstOrFail();
   $wallet=Wallet::where('user_id',$userId)->where('currency','IRR')->lockForUpdate()->first();
   if(!$wallet){
    $wallet=Wallet::create(['id'=>(string)Str::uuid(),'user_id'=>$userId,'currency'=>'IRR','balance_cached'=>0]);
    $wallet=Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
   }
   $before=(float)$wallet->balance_cached;
   $limit=(float)$profile->credit_limit;
   if(($before+$limit)<$amount) throw new RuntimeException('Reseller balance/credit limit is insufficient.');
   $after=$before-$amount;
   $wallet->update(['balance_cached'=>$after]);
   DB::table('wallet_transactions')->insert([
    'id'=>(string)Str::uuid(),'wallet_id'=>$wallet->id,'type'=>'reseller_order','direction'=>'debit','amount'=>$amount,
    'balance_before'=>$before,'balance_after'=>$after,'reference_type'=>null,'reference_id'=>null,
    'description'=>$description,'created_at'=>now(),'updated_at'=>now()
   ]);
  });
 }
}