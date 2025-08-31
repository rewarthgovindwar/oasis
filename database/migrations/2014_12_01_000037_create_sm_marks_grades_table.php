<?php

use App\SmMarksGrade;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmMarksGradesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_marks_grades', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('grade_name')->nullable();
            $blueprint->float('gpa')->nullable();
            $blueprint->float('from')->nullable();
            $blueprint->float('up')->nullable();
            $blueprint->float('percent_from')->nullable();
            $blueprint->float('percent_upto')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();
            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });

        $data = [
            ['A+',  '5.00',  5.00,    5.99,   80, 100,     'Outstanding !'],
            ['A',  '4.00',  4.00,    4.99,   70, 79.99,      'Very Good !'],
            ['A-',  '3.50',  3.50,    3.99,   60, 69.99,      'Good !'],
            ['B',  '3.00',  3.00,    3.49,   50, 59.99,     'Outstanding !'],
            ['C',  '2.00',  2.00,    2.99,   40, 49.99,      'Bad !'],
            ['D',  '1.00',  1.00,    1.99,   33, 39.99,      'Very Bad !'],
            ['F',  '0.00',  0.00,    0.99,   0, 32.99,       'Failed !'],
        ];
        foreach ($data as $r) {
            $store = new SmMarksGrade();
            $store->academic_id = 1;
            $store->grade_name = $r[0];
            $store->gpa = $r[1];
            $store->from = $r[2];
            $store->up = $r[3];
            $store->percent_from = $r[4];
            $store->percent_upto = $r[5];
            $store->description = $r[6];
            $store->created_at = date('Y-m-d h:i:s');
            $store->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_marks_grades');
    }
}
