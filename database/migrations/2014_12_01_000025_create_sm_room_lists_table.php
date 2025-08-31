<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmRoomListsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_room_lists', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name', 255);
            $blueprint->integer('number_of_bed');
            $blueprint->double('cost_per_bed')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('dormitory_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('dormitory_id')->references('id')->on('sm_dormitory_lists')->onDelete('cascade');

            $blueprint->integer('room_type_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('room_type_id')->references('id')->on('sm_room_types')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_room_lists');
    }
}
