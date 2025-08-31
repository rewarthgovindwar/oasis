<?php

use App\Models\FeesInvoice;
use App\SmSchool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fees_invoices', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('prefix')->nullable();
            $blueprint->integer('start_form')->nullable();
            $blueprint->integer('un_academic_id')->nullable()->default(1)->unsigned();
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->timestamps();
        });

        $schools = SmSchool::all();
        foreach ($schools as $school) {
            $store = new FeesInvoice();
            $store->prefix = 'infixEdu';
            $store->start_form = 101 + $school->id;
            $store->school_id = $school->id;
            $store->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_invoices');
    }
}
