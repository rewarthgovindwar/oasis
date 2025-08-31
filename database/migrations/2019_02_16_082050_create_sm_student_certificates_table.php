<?php

use App\SmStudentCertificate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmStudentCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('sm_student_certificates', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('name')->nullable();
            $blueprint->string('header_left_text')->nullable();
            $blueprint->date('date')->nullable();
            $blueprint->text('body')->nullable();
            $blueprint->text('body_two')->nullable();
            $blueprint->text('certificate_no')->nullable();
            $blueprint->string('type')->nullable()->default('school');
            $blueprint->string('footer_left_text')->nullable();
            $blueprint->string('footer_center_text')->nullable();
            $blueprint->string('footer_right_text')->nullable();
            $blueprint->tinyInteger('student_photo')->default(1)->comment('1 = yes 0 no');
            $blueprint->string('file')->nullable();
            $blueprint->integer('layout')->nullable()->comment('1 = Portrait, 2 =  Landscape');
            $blueprint->string('body_font_family')->nullable()->default('Arial')->comment('body_font_family');
            $blueprint->string('body_font_size')->nullable()->default('2em')->comment('');
            $blueprint->string('height', 50)->nullable()->comment('Height in mm');
            $blueprint->string('width', 50)->nullable()->comment('width in mm');
            $blueprint->string('default_for', 50)->nullable()->comment('default_for course');
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();
            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        $smStudentCertificate = new SmStudentCertificate();
        $smStudentCertificate->name = 'Certificate in Technical Communication (PCTC)';
        $smStudentCertificate->header_left_text = 'Since 2020';
        $smStudentCertificate->date = '2020-05-17';
        $smStudentCertificate->body = "Earning my UCR Extension professional certificate is one of the most beneficial things I've done for my career. Before even completing the program, I was contacted twice by companies who were interested in hiring me as a technical writer. This program helped me reach my career goals in a very short time";
        $smStudentCertificate->footer_left_text = 'Advisor Signature';
        $smStudentCertificate->footer_center_text = 'Instructor Signature';
        $smStudentCertificate->footer_right_text = 'Principale Signature';
        $smStudentCertificate->student_photo = 0;
        $smStudentCertificate->body_font_family = 'Arial';
        $smStudentCertificate->body_font_size = '2em';
        $smStudentCertificate->file = 'public/uploads/certificate/c.jpg';
        $smStudentCertificate->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_student_certificates');

    }
}
