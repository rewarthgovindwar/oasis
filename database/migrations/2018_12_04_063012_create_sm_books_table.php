<?php

use App\SmBook;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmBooksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_books', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('book_title', 200)->nullable();
            $blueprint->string('book_number', 200)->nullable();
            $blueprint->string('isbn_no', 200)->nullable();
            $blueprint->string('publisher_name', 200)->nullable();
            $blueprint->string('author_name', 200)->nullable();
            // $table->string('subject',200)->nullable();
            $blueprint->string('rack_number', 50)->nullable();
            $blueprint->integer('quantity')->nullable()->default(0);
            $blueprint->integer('book_price')->nullable();

            $blueprint->date('post_date')->nullable();
            $blueprint->string('details', 500)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->timestamps();

            $blueprint->integer('book_subject_id')->nullable()->unsigned();

            $blueprint->integer('book_category_id')->nullable()->unsigned();
            $blueprint->foreign('book_category_id')->references('id')->on('sm_book_categories')->onDelete('cascade');

            $blueprint->integer('created_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('updated_by')->nullable()->default(1)->unsigned();

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('academic_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
        // $books = [
        //         'Algorithms & Data Structures',
        //         'Cellular Automata',
        //         'Cloud Computing',
        //         'Competitive Programming',
        //         'Compiler Design',
        //         'Database',
        //         'Datamining',
        //         'Information Retrieval',
        //         'Licensing',
        //         'Machine Learning',
        //         'Mathematics',
        //     ];
        //     $i=1;
        //     foreach ($books as $book) {
        //     $store = new SmBook();
        //     $store->book_category_id = $i;
        //     $store->book_title = $book;
        //     $store->book_number = 'B-'.$i;
        //     $store->isbn_no = 'ISBN-0'.$i;
        //     $store->publisher_name = 'Infix';
        //     $store->author_name = 'Author Infix';
        //     $store->subject = 1+ $i%5;
        //     $store->rack_number = $i;
        //     $store->quantity =200+ $i;
        //     $store->book_price =300+ 20* $i;
        //     $store->save();
        //     $i++;
        // }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_books');
    }
}
