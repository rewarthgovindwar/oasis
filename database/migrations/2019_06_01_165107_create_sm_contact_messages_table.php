<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmContactMessagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_contact_messages', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name')->nullable();
            $blueprint->string('phone')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('subject')->nullable();
            $blueprint->text('message')->nullable();
            $blueprint->tinyInteger('view_status')->default(0);
            $blueprint->tinyInteger('reply_status')->default(0);
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_contact_messages');
    }
}
