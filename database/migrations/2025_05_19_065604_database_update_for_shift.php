<?php

use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\ShiftController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $shift_controller = ShiftController::updateDatabase();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
