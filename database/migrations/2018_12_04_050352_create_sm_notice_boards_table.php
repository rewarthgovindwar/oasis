<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmNoticeBoardsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_notice_boards', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('notice_title', 200)->nullable();
            $blueprint->text('notice_message')->nullable();
            $blueprint->date('notice_date')->nullable();
            $blueprint->date('publish_on')->nullable();
            $blueprint->string('inform_to', 200)->nullable()->comment('Notice message sent to these roles');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->integer('is_published')->nullable()->default(0);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        DB::table('sm_notice_boards')->insert([
            [
                'notice_title' => 'This is a sample notice 1',
                'notice_message' => 'This a demo notice',
                'notice_date' => date('Y-m-d'),
                'publish_on' => date('Y-m-d'),
                'inform_to' => '[1]',
                'is_published' => 1,
            ],
            [
                'notice_title' => 'This is another sample notice 2',
                'notice_message' => 'This a demo notice',
                'notice_date' => date('Y-m-d'),
                'publish_on' => date('Y-m-d'),
                'inform_to' => '[1]',
                'is_published' => 1,
            ],
            [
                'notice_title' => 'This is another sample notice 3',
                'notice_message' => 'This a demo notice',
                'notice_date' => date('Y-m-d'),
                'publish_on' => date('Y-m-d'),
                'inform_to' => '[1]',
                'is_published' => 1,
            ],
            [
                'notice_title' => 'This is another sample notice 4',
                'notice_message' => 'This a demo notice',
                'notice_date' => date('Y-m-d'),
                'publish_on' => date('Y-m-d'),
                'inform_to' => '[1]',
                'is_published' => 1,
            ],
            [
                'notice_title' => 'This is another sample notice 5',
                'notice_message' => 'This a demo notice',
                'notice_date' => date('Y-m-d'),
                'publish_on' => date('Y-m-d'),
                'inform_to' => '[1]',
                'is_published' => 1,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_notice_boards');
    }
}
