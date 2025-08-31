<?php

use App\SmChartOfAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_chart_of_accounts', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('head', 200)->nullable();
            $blueprint->string('type', 1)->nullable()->comment('E = expense, I = income');
            $blueprint->integer('active_status')->nullable()->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        $smChartOfAccount = new SmChartOfAccount();
        $smChartOfAccount->id = 1;
        $smChartOfAccount->head = 'Fees Collection';
        $smChartOfAccount->type = 'I';
        $smChartOfAccount->save();

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_chart_of_accounts');
    }
}
