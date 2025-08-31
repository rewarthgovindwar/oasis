<?php

use App\Models\SchoolModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolModulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_modules', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->longText('modules')->nullable();
            $blueprint->longText('menus')->nullable();
            $blueprint->string('module_name')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->integer('updated_by')->nullable();
            $blueprint->integer('school_id')->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        if (moduleStatusCheck('Lead')) {
            $schools = App\SmSchool::all();
            foreach ($schools as $school) {
                $exists = SchoolModule::where('school_id', $school->id)->first();
                if (! $exists) {
                    $settings = new SchoolModule;
                    $settings->module_name = 'lead';
                    $settings->school_id = $school->id;
                    $settings->active_status = $school->id === 1 ? 1 : 0;
                    $settings->save();
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_modules');
    }
}
