<?php

use App\SmSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmSessionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_sessions', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('session', 255);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        $smSession = new SmSession();
        $smSession->session = '2020-2021';
        $smSession->school_id = 1;
        $smSession->created_by = 1;
        $smSession->updated_by = 1;
        $smSession->active_status = 1;
        $smSession->created_at = date('Y-m-d h:i:s');
        $smSession->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_sessions');
    }
}
