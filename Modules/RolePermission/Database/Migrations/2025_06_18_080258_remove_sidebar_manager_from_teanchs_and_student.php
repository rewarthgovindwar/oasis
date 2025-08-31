<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSidebarManagerFromTeanchsAndStudent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        DB::table('permissions')->where('route','menumanage.index')->where('is_parent',1)->delete();
        DB::table('permissions')->where('route','menumanage.index')->where('is_student',1)->delete();
        DB::table('permissions')->where('route','dormitory-list-store')->where('is_admin',1)->delete();
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
