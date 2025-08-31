<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('full_name', 192)->nullable();
            $blueprint->string('username', 192)->nullable();
            $blueprint->string('phone_number', 191)->nullable();
            $blueprint->string('email', 192)->nullable();
            $blueprint->string('password', 100)->nullable();
            $blueprint->string('usertype', 210)->nullable();
            $blueprint->tinyInteger('active_status')->default(1);
            $blueprint->text('random_code')->nullable();
            $blueprint->text('notificationToken')->nullable();
            $blueprint->rememberToken();
            $blueprint->timestamps();

            $blueprint->string('language')->nullable()->default('en');
            $blueprint->integer('style_id')->nullable()->default(1);
            $blueprint->integer('rtl_ltl')->nullable()->default(2);
            $blueprint->integer('selected_session')->nullable()->default(1);

            $blueprint->integer('created_by')->nullable()->default(1);
            $blueprint->integer('updated_by')->nullable()->default(1);
            $blueprint->integer('access_status')->nullable()->default(1);

            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');

            $blueprint->integer('role_id')->nullable()->unsigned();
            $blueprint->foreign('role_id')->references('id')->on('infix_roles')->onDelete('cascade');
            $blueprint->enum('is_administrator', ['yes', 'no'])->default('no');
            $blueprint->tinyInteger('is_registered')->default(0);
            $blueprint->text('device_token')->nullable();

            $blueprint->string('stripe_id')->nullable();
            $blueprint->string('card_brand')->nullable();
            $blueprint->string('card_last_four', 4)->nullable();
            $blueprint->string('verified')->nullable();
            $blueprint->timestamp('trial_ends_at')->nullable();
        });

        $data = User::find(1);

        if (empty($data)) {
            $user = new User();
            $user->created_by = 1;
            $user->updated_by = 1;
            $user->school_id = 1;
            $user->role_id = 1;
            $user->full_name = 'admin';
            $user->email = 'admin@infixedu.com';
            $user->is_administrator = 'yes';
            $user->username = 'admin@infixedu.com';
            $user->password = Hash::make('123456');
            $user->created_at = date('Y-m-d h:i:s');
            $user->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
