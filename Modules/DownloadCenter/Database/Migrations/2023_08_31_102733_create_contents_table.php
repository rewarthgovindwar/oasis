<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('file_name')->nullable();
            $blueprint->integer('file_size')->nullable();
            $blueprint->integer('content_type_id');
            $blueprint->string('youtube_link')->nullable();
            $blueprint->string('upload_file')->length(200)->nullable();
            $blueprint->integer('uploaded_by');
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
        Schema::dropIfExists('contents');
    }
}
