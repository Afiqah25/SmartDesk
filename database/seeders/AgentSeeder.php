<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'agent@smartdesk.test'],
            [
                'name' => 'SmartDesk Agent',
                'password' => Hash::make('SmartDesk@2026'),
            ]
        );
    }
}
