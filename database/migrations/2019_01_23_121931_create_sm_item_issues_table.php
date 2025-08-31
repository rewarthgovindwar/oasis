<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmItemIssuesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_item_issues', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->integer('issue_to')->nullable()->unsigned();
            $blueprint->integer('issue_by')->nullable()->unsigned();
            $blueprint->date('issue_date')->nullable();
            $blueprint->date('due_date')->nullable();
            $blueprint->integer('quantity')->nullable()->unsigned();
            $blueprint->string('issue_status')->nullable();
            $blueprint->string('note', 500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $blueprint->integer('item_category_id')->nullable()->unsigned();
            $blueprint->foreign('item_category_id')->references('id')->on('sm_item_categories')->onDelete('cascade');

            $blueprint->integer('item_id')->nullable()->unsigned();
            $blueprint->foreign('item_id')->references('id')->on('sm_items')->onDelete('cascade');

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
        Schema::dropIfExists('sm_item_issues');
    }
}
