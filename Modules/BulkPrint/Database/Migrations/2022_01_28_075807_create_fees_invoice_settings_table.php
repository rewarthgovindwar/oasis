<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\BulkPrint\Entities\FeesInvoiceSetting;

class CreateFeesInvoiceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fees_invoice_settings', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->integer('per_th')->default(2);
            $blueprint->string('invoice_type')->default('invoice');
            $blueprint->tinyInteger('student_name')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('student_section')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('student_class')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('student_roll')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('student_group')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('student_admission_no')->default(1)->comment('0=No, 1=Yes');

            $blueprint->string('footer_1', 255)->default('Parent/Student')->nullable();
            $blueprint->string('footer_2', 255)->default('Casier');
            $blueprint->string('footer_3', 255)->default('Officer');

            $blueprint->tinyInteger('signature_p')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('signature_c')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('signature_o')->default(1)->comment('0=No, 1=Yes');

            $blueprint->tinyInteger('c_signature_p')->default(1)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('c_signature_c')->default(0)->comment('0=No, 1=Yes');
            $blueprint->tinyInteger('c_signature_o')->default(1)->comment('0=No, 1=Yes');

            $blueprint->string('copy_s', 255)->default('Parent/Student')->nullable();
            $blueprint->string('copy_o', 255)->default('Office');
            $blueprint->string('copy_c', 255)->default('Casier');

            $blueprint->timestamps();

            $blueprint->text('copy_write_msg')->nullable();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();
            $blueprint->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        try {
            $feesInvoiceSetting = new FeesInvoiceSetting();
            $feesInvoiceSetting->per_th = 2;
            $feesInvoiceSetting->invoice_type = 'invoice';
            $feesInvoiceSetting->save();
        } catch (Throwable $throwable) {
            Log::info($throwable);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_invoice_settings');
    }
}
