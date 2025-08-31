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
       if(!Schema::hasTable('sm_menus'))
        {
            Schema::create('sm_menus', function (Blueprint $table) {
                 $table->id();
                $table->string('name')->nullable();
                $table->string('module')->nullable();
                $table->string('route')->nullable();
                $table->string('lang_name')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->string('icon')->nullable();
                $table->tinyInteger('status')->nullable();
                $table->tinyInteger('is_saas')->nullable();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->tinyInteger('is_alumni')->nullable();
                $table->tinyInteger('menu_status')->nullable();
                $table->tinyInteger('permission_section')->nullable();
                $table->integer('position')->nullable();    
                $table->integer('default_position')->nullable();    
                $table->unsignedBigInteger('parent')->nullable();            
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('school_id')->nullable();
                $table->string('alternate_module')->nullable();
                $table->unsignedBigInteger('permission_id')->nullable();
                $table->tinyInteger('ignore')->default(0);
                $table->timestamps();
            });
        }
		
		if(!Schema::hasTable('default_menus'))
        {
            Schema::create('default_menus', function (Blueprint $table) {
                 $table->id();
                $table->string('name')->nullable();
                $table->string('module')->nullable();
                $table->string('route')->nullable();
                $table->string('lang_name')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                $table->string('icon')->nullable();
                $table->tinyInteger('status')->nullable();
                $table->tinyInteger('is_saas')->nullable();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->tinyInteger('is_alumni')->nullable();
                $table->tinyInteger('menu_status')->nullable();
                $table->tinyInteger('permission_section')->nullable();
                $table->integer('position')->nullable();    
                $table->integer('default_position')->nullable();    
                $table->unsignedBigInteger('parent')->nullable();            
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('school_id')->nullable();
                $table->string('alternate_module')->nullable();
                $table->unsignedBigInteger('permission_id')->nullable();
                $table->tinyInteger('ignore')->default(0);
                $table->timestamps();
            });
        }
        
        
		
        if(Schema::hasTable('sm_menus')){
			$menuFile = base_path('Modules/MenuManage/Resources/var/menus.sql');
            if(file_exists($menuFile))
            {
                DB::table('sm_menus')->truncate();                
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::unprepared(file_get_contents($menuFile));
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }
		
		if(Schema::hasTable('default_menus')){
			$defaultMenuFile = base_path('Modules/MenuManage/Resources/var/default_menus.sql');
            if(file_exists($defaultMenuFile))
            {
				DB::table('default_menus')->truncate();   					
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::unprepared(file_get_contents($defaultMenuFile));
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
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
