<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropForeignColumn extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE `sm_exam_schedules` DROP FOREIGN KEY sm_exam_schedules_room_id_foreign');
            DB::statement('ALTER TABLE `sm_exam_schedules` DROP INDEX `sm_exam_schedules_room_id_foreign`');
        } catch (Throwable $throwable) {
            // throw $th;
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
