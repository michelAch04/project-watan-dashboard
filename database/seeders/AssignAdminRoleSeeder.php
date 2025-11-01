<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AssignAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1);
        if ($user) {
            $user->syncRoles(['admin']);
        }
    }
}