<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentShareListsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('content_share_lists', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title')->nullable();
            $blueprint->date('share_date')->nullable();
            $blueprint->date('valid_upto')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->string('send_type')->nullable()->comment('G, C, I, P');
            $blueprint->json('content_ids')->nullable();
            $blueprint->json('gr_role_ids')->nullable();
            $blueprint->json('ind_user_ids')->nullable();
            $blueprint->integer('class_id')->nullable();
            $blueprint->json('section_ids')->nullable();
            $blueprint->text('url')->nullable();
            $blueprint->integer('shared_by')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_share_lists');
    }
}
