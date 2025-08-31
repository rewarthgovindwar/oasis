<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            [
                "module" => "DownloadCenter",
                "name" => "view",
                "parent_route" => "download-center.video-list",
                "lang_name" =>    "common.view",
                "route" => "download-center.video-list-view-modal",
                "status" => 1,
                "menu_status" => 1,
                "position" => 5,
                "is_saas" => 0,
                "relate_to_child" => 0,
                "is_menu" => 0,
                "is_admin" => 1,
                "type" => 3,
                "parent_id" => 0,
                "permission_section" => 0,
                "school_id" => 1,
            ],
            [
                "module" => "",
                "name" => "view",
                "parent_route" => "upload-content",
                "lang_name" =>    "common.view",
                "route" => "upload-content-view",
                "status" => 1,
                "menu_status" => 1,
                "position" => 5,
                "is_saas" => 0,
                "relate_to_child" => 0,
                "is_menu" => 0,
                "is_admin" => 1,
                "type" => 3,
                "parent_id" => 0,
                "permission_section" => 0,
                "school_id" => 1,
            ],
            [
                "module" => "",
                "name" => "view",
                "parent_route" => "assignment-list",
                "lang_name" =>    "common.view",
                "route" => "assignment-list-view",
                "status" => 1,
                "menu_status" => 1,
                "position" => 4,
                "is_saas" => 0,
                "relate_to_child" => 0,
                "is_menu" => 0,
                "is_admin" => 1,
                "type" => 3,
                "parent_id" => 0,
                "permission_section" => 0,
                "school_id" => 1,
            ],
            [
                "module" => "",
                "name" => "view",
                "parent_route" => "syllabus-list",
                "lang_name" =>    "common.view",
                "route" => "syllabus-list-view",
                "status" => 1,
                "menu_status" => 1,
                "position" => 4,
                "is_saas" => 0,
                "relate_to_child" => 0,
                "is_menu" => 0,
                "is_admin" => 1,
                "type" => 3,
                "parent_id" => 0,
                "permission_section" => 0,
                "school_id" => 1,
            ],
            [
                "module" => "",
                "name" => "view",
                "parent_route" => "other-download-list",
                "lang_name" =>    "common.view",
                "route" => "other-download-list-view",
                "status" => 1,
                "menu_status" => 1,
                "position" => 4,
                "is_saas" => 0,
                "relate_to_child" => 0,
                "is_menu" => 0,
                "is_admin" => 1,
                "type" => 3,
                "parent_id" => 0,
                "permission_section" => 0,
                "school_id" => 1,
            ],
        ];
        // DB::table('permissions')->insert($permission);
        foreach($permissions as $permission)
        {
            DB::table('permissions')->where('route',$permission['route'])->delete();
            DB::table('permissions')->insert($permission);
        }

         $permission = ['download-center.content-type-update','download-center.content-list-update','download-center.video-list-update'];
         DB::table('permissions')->whereIn('route',$permission)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
    }
};
