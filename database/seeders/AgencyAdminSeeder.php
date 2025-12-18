<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AgencyAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample Agency
        $agency = Agency::create([
            'name' => 'Sample Inspection Agency',
            'status' => 'active',
        ]);

        // Create an Admin user for this Agency
        User::create([
            'agency_id' => $agency->id,
            'name' => 'Agency Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        User::create([
            'agency_id' => $agency->id,
            'name' => 'StormProof Advisor',
            'email' => 'advisor@example.com',
            'password' => Hash::make('password'),
            'role' => 'advisor',
            'crm_user_id' => 'ADV_CRM_123',
        ]);
        User::create([
            'agency_id' => $agency->id,
            'name' => 'Reliable Repairs Co.',
            'email' => 'partner@example.com',
            'password' => Hash::make('password'),
            'role' => 'partner',
        ]);
    }
}
