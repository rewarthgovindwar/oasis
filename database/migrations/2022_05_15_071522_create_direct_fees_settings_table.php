<?php

use App\InfixModuleManager;
use App\Models\DirectFeesSetting;
use App\SmSchool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectFeesSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('direct_fees_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->boolean('fees_installment')->default(0);
            $blueprint->boolean('fees_reminder')->default(0);
            $blueprint->integer('reminder_before')->default(5);
            $blueprint->integer('no_installment')->default(0);
            $blueprint->integer('due_date_from_sem')->default(10);
            $blueprint->integer('end_day')->nullable();
            $blueprint->unsignedInteger('academic_id')->nullable();
            $blueprint->unsignedInteger('school_id')->nullable();
            $blueprint->foreign('school_id')->on('sm_schools')->references('id')->cascadeOnDelete();
            $blueprint->timestamps();
        });
        try {
            $module_name = 'University';
            $schools = SmSchool::all();
            foreach ($schools as $school) {
                $new = new DirectFeesSetting();
                $new->school_id = $school->id;
                $new->academic_id = 1;
                $new->save();
                Schema::table('sm_general_settings', function (Blueprint $blueprint) use ($module_name): void {
                    if (! Schema::hasColumn('sm_general_settings', $module_name)) {
                        $blueprint->unsignedBigInteger($module_name)->nullable();
                    }

                    if (! Schema::hasColumn('sm_general_settings', 'direct_fees_assign')) {
                        $blueprint->boolean('direct_fees_assign')->default(0);
                    }
                });
            }

            $check = InfixModuleManager::where('name', $module_name)->first();
            if (! $check) {
                $infixModuleManager = new InfixModuleManager();
                $infixModuleManager->name = $module_name;
                $infixModuleManager->email = 'support@spondonit.com';
                $infixModuleManager->notes = 'Manage Your University Using This Module';
                $infixModuleManager->version = 1.0;
                $infixModuleManager->update_url = url('/');
                $infixModuleManager->is_default = 0;
                $infixModuleManager->installed_domain = url('/');
                $infixModuleManager->activated_date = date('Y-m-d');
                $infixModuleManager->save();
            }

            $class_id = 'class_id';
            $section_id = 'section_id';
            $direct_fees_installment_assign_id = 'direct_fees_installment_assign_id';

            Schema::table('sm_fees_masters', function (Blueprint $blueprint) use ($class_id): void {
                if (! Schema::hasColumn('sm_fees_masters', $class_id)) {
                    $blueprint->unsignedBigInteger($class_id)->nullable();
                }
            });

            Schema::table('sm_fees_masters', function (Blueprint $blueprint) use ($section_id): void {
                if (! Schema::hasColumn('sm_fees_masters', $section_id)) {
                    $blueprint->unsignedBigInteger($section_id)->nullable();
                }
            });

            Schema::table('sm_fees_assigns', function (Blueprint $blueprint) use ($class_id): void {
                if (! Schema::hasColumn('sm_fees_assigns', $class_id)) {
                    $blueprint->unsignedBigInteger($class_id)->nullable();
                }
            });

            Schema::table('sm_fees_assigns', function (Blueprint $blueprint) use ($section_id): void {
                if (! Schema::hasColumn('sm_fees_assigns', $section_id)) {
                    $blueprint->unsignedBigInteger($section_id)->nullable();
                }
            });

            Schema::table('sm_fees_payments', function (Blueprint $blueprint) use ($direct_fees_installment_assign_id): void {
                if (! Schema::hasColumn('sm_fees_payments', $direct_fees_installment_assign_id)) {
                    $blueprint->unsignedBigInteger($direct_fees_installment_assign_id)->nullable();
                }
            });

            if (moduleStatusCheck('ParentRegistration')) {
                $columns = ['un_session_id', 'un_faculty_id', 'un_department_id', 'un_academic_id', 'un_semester_id', 'un_semester_label_id', 'un_section_id'];
                foreach ($columns as $column) {
                    Schema::table('sm_student_registrations', function (Blueprint $blueprint) use ($column): void {
                        if (! Schema::hasColumn('sm_student_registrations', $column)) {
                            $blueprint->unsignedBigInteger($column)->nullable();
                        }
                    });
                }
            }
        } catch (Throwable $throwable) {
        }
    }
}
