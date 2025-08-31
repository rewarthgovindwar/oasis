<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try{
            $custome_menu = Permission::where('route','custom-marksheet-report')->first();
            $report_menu  = Permission::where('route','mark_sheet_report_student')->first();
            if(!empty($report_menu) && !empty($custome_menu)){                
                $custome_menu->sidebar_menu = $report_menu->sidebar_menu;
                $custome_menu->name = "Subject wise MarkSheet Report";
                $custome_menu->parent_route = $report_menu->parent_route;
                $custome_menu->type = $report_menu->type;
                $custome_menu->lang_name = "reports.subject_wise_mark_sheet_report";
                $custome_menu->position = $report_menu->position + 1;
                $custome_menu->parent_id = $report_menu->parent_id;
                $custome_menu->menu_status = $report_menu->menu_status;
                $custome_menu->is_saas = $report_menu->is_saas;
                $custome_menu->relate_to_child = $report_menu->relate_to_child;
                $custome_menu->is_menu = $report_menu->is_menu;
                $custome_menu->is_admin = $report_menu->is_admin;
                $custome_menu->is_teacher = $report_menu->is_teacher;
                $custome_menu->is_student = $report_menu->is_student;
                $custome_menu->is_parent = $report_menu->is_parent;
                $custome_menu->is_alumni = $report_menu->is_alumni;
                $custome_menu->alternate_module = $report_menu->alternate_module;
                $custome_menu->user_id = $report_menu->user_id;
                $custome_menu->school_id = $report_menu->school_id;
                $custome_menu->custom_menu_id = $report_menu->custom_menu_id;
                $custome_menu->save();            
            } 
        }catch(Exception $e){
            Log::error($e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
