<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_form_downloads', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('title')->nullable();
            $blueprint->string('short_description', 200)->nullable();
            $blueprint->date('publish_date')->nullable();
            $blueprint->string('link')->nullable();
            $blueprint->string('file')->nullable();
            $blueprint->tinyInteger('show_public')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        $datas = [
            ['Exam Routine', 'Exam Routine'],
            ['Class Routine', 'Class Routine'],
            ['Open An Bank Account Routine', 'Open An Bank Account Routine'],
        ];
        foreach ($datas as $key => $data) {
            $key++;
            DB::table('sm_form_downloads')->insert([
                'title' => $data[0],
                'short_description' => $data[1],
                'publish_date' => date('Y-m-d'),
                'file' => sprintf('public/uploads/theme/edulia/form_download/file-%s.pdf', $key),
                'school_id' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_form_downloads');
    }
};
