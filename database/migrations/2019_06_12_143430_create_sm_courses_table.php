<?php

use App\SmCourse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmCoursesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_courses', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title');
            $blueprint->text('image');
            $blueprint->integer('category_id');
            $blueprint->text('overview')->nullable();
            $blueprint->text('outline')->nullable();
            $blueprint->text('prerequisites')->nullable();
            $blueprint->text('resources')->nullable();
            $blueprint->text('stats')->nullable();
            $blueprint->integer('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });

        for ($i = 1; $i <= 5; $i++) {
            $new = new SmCourse();
            $new->title = fake()->text(50);
            $new->image = 'public/uploads/theme/edulia/course/academic1.jpg';
            $new->overview = fake()->text(2000);
            $new->outline = fake()->text(2000);
            $new->prerequisites = fake()->text(2000);
            $new->resources = fake()->text(2000);
            $new->stats = fake()->text(2000);
            $new->active_status = 1;
            $new->created_at = date('Y-m-d h:i:s');
            $new->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_courses');
    }
}
