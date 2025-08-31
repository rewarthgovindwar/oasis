<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\MenuManage\Entities\SmMenu;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(Schema::hasColumn('sm_general_settings','shift_enable'))
        {
            DB::table('sm_general_settings')->where('id',1)->update([
                "shift_enable" => 0
            ]);
            
            
        }
        DB::table('sm_menus')->where('id',69494)->delete();
        DB::table('sm_menus')->where('id',69493)->delete();
        DB::table('default_menus')->where('id',69493)->delete();
        DB::table('default_menus')->where('id',69494)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
