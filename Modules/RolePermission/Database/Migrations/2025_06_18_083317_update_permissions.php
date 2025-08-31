<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Update Walet Routes for Parent
        
        DB::table("permissions")->where('parent_route','wallet.my-wallet')->update(['is_parent' => 1]);
        DB::table('permissions')->where('route','behaviour_records.assign_incident_save')->update([
            'name' => "Assign / View"
        ]);
        DB::table('permissions')->where('route','student_settings')->update([
            "parent_route" => "student_info"
        ]);
        DB::table('permissions')->where('route','lesson.lessonPlan-setting')->update([
            "parent_route" => "lesson-plan"
        ]);
        DB::table('permissions')->where('route','staff_settings')->update([
            "parent_route" => "human_resource"
        ]);

        DB::table('permissions')->where('route','chat.settings')->update([
            "parent_route" => "chat"
        ]);

        DB::table('permissions')->where('route','manage-adons')->update([
            "parent_route" => null
        ]);

        DB::table('permissions')->where('route','disabled_student')->whereNull('sidebar_menu')->update([
            'route' => 'disabled_student_delete'
        ]);

        //
        
        //$routes = [
        //      [
        //         "module" => "",
        //         "name" => "View",
        //         "parent_route" => "download-center.content-share-list",
        //         "lang_name" =>    "common.view",
        //         "route" => "download-center.content-generate-url-view",
        //         "status" => 1,
        //         "menu_status" => 1,
        //         "position" => 0,
        //         "is_saas" => 0,
        //         "relate_to_child" => 0,
        //         "is_menu" => 0,
        //         "is_admin" => 1,
        //         "type" => 3,
        //         "parent_id" => 0,
        //         "permission_section" => 0,
        //         "school_id" => 1,
        //     ],
        //     [
        //         "module" => "",
        //         "name" => "Delete",
        //         "parent_route" => "download-center.content-share-list",
        //         "lang_name" =>    "common.delete",
        //         "route" => "download-center.content-generate-url-delete",
        //         "status" => 1,
        //         "menu_status" => 1,
        //         "position" => 0,
        //         "is_saas" => 0,
        //         "relate_to_child" => 0,
        //         "is_menu" => 0,
        //         "is_admin" => 1,
        //         "type" => 3,
        //         "parent_id" => 0,
        //         "permission_section" => 0,
        //         "school_id" => 1,
        //     ],
        //];
        //DB::table('permissions')->insert($routes);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
