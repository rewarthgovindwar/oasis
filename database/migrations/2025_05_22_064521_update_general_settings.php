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
        if(Schema::hasTable('sm_general_settings'))
        {
            $year = date("Y");
            DB::table('sm_general_settings')->where('id',1)->update([
                'email' => "hello@aorasoft.com",
                "copyright_text" => "Copyright © {$year} InfixEdu. All rights reserved | Codethemes made with this application",
                "result_type" => "gpa",
                "site_title" => "Ultimate Education ERP",
                "school_name" => "InfixEdu",
                "fees_status" => 1,
                "promotionSetting" => 1
            ]);
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
