<?php

use App\SmNews;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmNewsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_news', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('news_title');
            $blueprint->integer('view_count')->nullable();
            $blueprint->integer('active_status')->nullable();
            $blueprint->string('image')->nullable();
            $blueprint->string('image_thumb')->nullable();
            $blueprint->longText('news_body')->nullable();
            $blueprint->date('publish_date')->nullable();
            $blueprint->tinyInteger('status')->default(1)->nullable();
            $blueprint->tinyInteger('is_global')->default(1)->nullable();
            $blueprint->tinyInteger('auto_approve')->default(0)->nullable();
            $blueprint->tinyInteger('is_comment')->default(0)->nullable();
            $blueprint->string('order')->nullable();
            $blueprint->timestamps();

            $blueprint->integer('category_id')->nullable()->unsigned();
            $blueprint->foreign('category_id')->references('id')->on('sm_news_categories')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
        });

        $i = 1;
        $cid = [1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3];
        foreach (array_keys(range(1, 12)) as $key) {
            $storeData = new SmNews();
            if ($key === 0) {
                $storeData->news_title = 'Digital Transformation in Education: INFIX EDU Paving the Way';
                $storeData->news_body = "As the education landscape continues to evolve, INFIX EDU remains at the forefront of digital transformation. In this blog post, we explore how INFIX EDU's innovative school management system is not just adapting to change but actively shaping the future of education. From online assessments to parent-teacher communication tools, discover the key elements driving this digital revolution in schools.";
            } elseif ($key === 1) {
                $storeData->news_title = 'Success Stories: How INFIX EDU ERP Empowers Schools Worldwide';
                $storeData->news_body = "In this blog series, we highlight success stories from schools around the globe that have embraced INFIX EDU's school management system. From improving communication between stakeholders to boosting overall efficiency, these stories provide insights into the transformative impact of INFIX EDU's technology. Join us in celebrating the achievements of schools that have elevated their educational experience with INFIX EDU.";
            } elseif ($key === 2) {
                $storeData->news_title = 'INFIX EDU Launches Enhanced Features for a Seamless School Year';
                $storeData->news_body = 'In a recent update, INFIX EDU, the leading school management system provider, unveiled a set of enhanced features aimed at optimizing administrative processes and fostering a smoother school year. From streamlined enrollment procedures to advanced reporting tools, schools can now benefit from an even more comprehensive and user-friendly platform. Read more to discover how these updates can positively impact your institution.';
            } else {
                $storeData->news_title = fake()->text(40);
                $storeData->news_body = fake()->text(500);
            }

            $storeData->view_count = fake()->randomDigit;
            $storeData->active_status = 1;
            $storeData->image = 'public/uploads/news/news'.$i.'.jpg';
            $storeData->publish_date = '2019-06-02';
            $storeData->category_id = $cid[$i - 1];
            $storeData->order = $i++;
            $storeData->created_at = date('Y-m-d h:i:s');
            $storeData->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_news');
    }
}
