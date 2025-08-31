<?php

use App\SmBaseSetup;
use App\SmSchool;
use App\SmStaff;
use App\SmStudent;
use Illuminate\Database\Migrations\Migration;

class BaseSetupUpdate extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        $schools = SmSchool::where('id', '!=', 1)->get();
        $base_setups = SmBaseSetup::where('school_id', 1)->get();

        foreach ($schools as $school) {
            foreach ($base_setups as $base_setup) {
                $exit = SmBaseSetup::where('base_setup_name', $base_setup->base_setup_name)->where('base_group_id', $base_setup->base_group_id)->where('school_id', $school->id)->first();
                if (! $exit) {
                    $new_setup = $base_setup->replicate();
                    $new_setup->school_id = $school->id;
                    $new_setup->save();

                    $this->update($new_setup, $base_setup);
                }
            }

        }
    }

    public function update($new_setup, $old_setup): void
    {
        if ($new_setup->base_group_id === 1) {
            $column = 'gender_id';
            SmStaff::where('gender_id', $old_setup->id)->where('school_id', $new_setup->school_id)->update([$column => $new_setup->id]);
        } elseif ($new_setup->base_group_id === 2) {
            $column = 'religion_id';
        } else {
            $column = 'bloodgroup_id';
        }

        SmStudent::where($column, $old_setup->id)->where('school_id', $new_setup->school_id)->update([$column => $new_setup->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
