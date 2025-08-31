<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmContactPagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_contact_pages', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('title')->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->string('image')->nullable();
            $blueprint->string('button_text')->nullable();
            $blueprint->string('button_url')->nullable();
            $blueprint->string('address')->nullable();
            $blueprint->string('address_text')->nullable();
            $blueprint->string('phone')->nullable();
            $blueprint->string('phone_text')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('email_text')->nullable();
            $blueprint->string('latitude')->nullable();
            $blueprint->string('longitude')->nullable();
            $blueprint->integer('zoom_level')->nullable();
            $blueprint->string('google_map_address')->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
        });
        DB::table('sm_contact_pages')->insert([
            [
                'title' => 'Contact Us',
                'description' => 'Have any questions? We’d love to hear from you! Here’s how to get in touch with us.',
                'image' => 'public/uploads/contactPage/contact.jpg',
                'button_text' => 'Learn More About Us',
                'button_url' => 'about',
                'address' => 'Al Khuwair, Muscat, Oman',
                'address_text' => 'Santa monica bullevard',
                'phone' => '+96897002784',
                'phone_text' => 'Mon to Fri 9am to 6 pm',
                'email' => 'hello@aorasoft.com',
                'email_text' => 'Send us your query anytime!',
                'latitude' => '23.707310',
                'longitude' => '90.415480',
                'zoom_level' => 15,
                'google_map_address' => 'Al Khuwair, Muscat, Oman',
                'school_id' => 1,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_contact_pages');
    }
}
