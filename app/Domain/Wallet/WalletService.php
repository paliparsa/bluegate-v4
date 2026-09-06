<?php
namespace App\Domain\Wallet;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class WalletService {
 public function walletFor(int $userId, string $currency='IRR'): Wallet {
  return Wallet::firstOrCreate(['user_id'=>$userId,'currency'=>$currency],[
   'id'=>(string)Str::uuid(),'balance_cached'=>0
  ]);
 }
 public function credit(Wallet $wallet, float $amount, string $type, string $description='', mixed $reference=null): void {
  if($amount<=0) throw new RuntimeException('Invalid credit amount');
  DB::transaction(function() use($wallet,$amount,$type,$description,$reference){
   $locked=Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
   $before=(float)$locked->balance_cached; $after=$before+$amount;
   $locked->update(['balance_cached'=>$after]);
   DB::table('wallet_transactions')->insert([
    'id'=>(string)Str::uuid(),'wallet_id'=>$locked->id,'type'=>$type,'direction'=>'credit','amount'=>$amount,
    'balance_before'=>$before,'balance_after'=>$after,
    'reference_type'=>$reference ? get_class($reference) : null,'reference_id'=>$reference?->id,
    'description'=>$description,'created_at'=>now(),'updated_at'=>now()
   ]);
  });
 }
 public function debit(Wallet $wallet, float $amount, string $type, string $description='', mixed $reference=null): void {
  if($amount<=0) throw new RuntimeException('Invalid debit amount');
  DB::transaction(function() use($wallet,$amount,$type,$description,$reference){
   $locked=Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
   $before=(float)$locked->balance_cached;
   if($before < $amount) throw new RuntimeException('موجودی کیف پول کافی نیست.');
   $after=$before-$amount; $locked->update(['balance_cached'=>$after]);
   DB::table('wallet_transactions')->insert([
    'id'=>(string)Str::uuid(),'wallet_id'=>$locked->id,'type'=>$type,'direction'=>'debit','amount'=>$amount,
    'balance_before'=>$before,'balance_after'=>$after,
    'reference_type'=>$reference ? get_class($reference) : null,'reference_id'=>$reference?->id,
    'description'=>$description,'created_at'=>now(),'updated_at'=>now()
   ]);
  });
 }
}