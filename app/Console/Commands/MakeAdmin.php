<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
class MakeAdmin extends Command {
 protected $signature='bluegate:make-admin {email}'; protected $description='Promote a BlueGate user to super_admin';
 public function handle(): int { $u=User::where('email',$this->argument('email'))->first(); if(!$u){$this->error('User not found.'); return 1;} $u->update(['role'=>'super_admin']); $this->info('User promoted to super_admin.'); return 0; }
}
