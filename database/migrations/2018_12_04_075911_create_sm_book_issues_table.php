<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmBookIssuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_book_issues', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('quantity')->nullable();
            $blueprint->date('given_date')->nullable();
            $blueprint->date('due_date')->nullable();
            $blueprint->string('issue_status')->nullable();
            $blueprint->string('note', 500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('book_id')->nullable()->unsigned();
            $blueprint->foreign('book_id')->references('id')->on('sm_books')->onDelete('cascade');

            $blueprint->integer('member_id')->nullable()->unsigned();
            $blueprint->foreign('member_id')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        //  Schema::table('sm_book_issues', function($table) {
        //     $table->foreign('member_id')->references('id')->on('sm_library_members');
        //     $table->foreign('book_id')->references('id')->on('sm_books');

        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_book_issues');
    }
}
