<?php

namespace Database\Seeders;

use App\SmSchool;
use App\SmStaff;
use App\User;
use Illuminate\Database\Seeder;
use Modules\Saas\Events\InstituteRegistration;

class SmSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SmSchool::factory()->times(2)->create()->each(
            function ($school): void {
                $role_id = User::where('school_id', $school->id)->where('role_id', 1)->first() ? 5 : 1;
                // school admin user
                User::factory()->times(1)->create([
                    'school_id' => $school->id,
                    'username' => $school->email,
                    'email' => $school->email,
                    'role_id' => $role_id,
                ])->each(function ($user) use ($role_id): void {
                    SmStaff::factory()->times(1)->create([
                        'role_id' => $role_id,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                    ]);
                });
                event(new InstituteRegistration($school));
            });
    }
}
