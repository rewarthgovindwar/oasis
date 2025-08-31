<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_general_settings', function (Blueprint $blueprint): void {
            $blueprint->increments('id');
            $blueprint->string('school_name')->nullable();
            $blueprint->string('site_title')->nullable();
            $blueprint->string('school_code')->nullable();
            $blueprint->string('address')->nullable();
            $blueprint->string('phone')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('file_size')->default(102400);
            $blueprint->string('currency')->nullable()->default('USD');
            $blueprint->string('currency_symbol')->nullable()->default('$');
            $blueprint->string('currency_format')->nullable()->default('symbol_amount');
            $blueprint->integer('promotionSetting')->nullable()->default(0);
            $blueprint->string('logo')->nullable();
            $blueprint->string('favicon')->nullable();
            $blueprint->string('system_version')->nullable()->default('8.2.8');
            $blueprint->integer('active_status')->nullable()->default(1);
            $blueprint->string('currency_code')->nullable()->default('USD');
            $blueprint->string('language_name')->nullable()->default('en');
            $blueprint->string('session_year')->nullable()->default(date('Y'));
            $blueprint->text('system_purchase_code')->nullable();
            $blueprint->date('system_activated_date')->nullable();
            $blueprint->date('last_update')->nullable();
            $blueprint->string('envato_user')->nullable();
            $blueprint->string('envato_item_id')->nullable();
            $blueprint->string('system_domain')->nullable();
            $blueprint->text('copyright_text')->nullable();
            $blueprint->integer('api_url')->default(1);
            $blueprint->integer('website_btn')->default(1);
            $blueprint->integer('dashboard_btn')->default(1);
            $blueprint->integer('report_btn')->default(1);
            $blueprint->integer('style_btn')->default(1);
            $blueprint->integer('ltl_rtl_btn')->default(1);
            $blueprint->integer('lang_btn')->default(1);
            $blueprint->string('website_url')->nullable();
            $blueprint->integer('ttl_rtl')->default(2);
            $blueprint->integer('phone_number_privacy')->default(1)->comments('1=enable, 0=disable');
            $blueprint->timestamps();
            $blueprint->integer('week_start_id')->nullable();
            $blueprint->integer('time_zone_id')->nullable();
            $blueprint->integer('attendance_layout')->default(1)->nullable();
            $blueprint->integer('session_id')->nullable()->unsigned();
            $blueprint->foreign('session_id')->references('id')->on('sm_academic_years')->onDelete('set null');

            $blueprint->integer('language_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('language_id')->references('id')->on('sm_languages')->onDelete('set null');

            $blueprint->integer('date_format_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('date_format_id')->references('id')->on('sm_date_formats')->onDelete('set null');

            $blueprint->integer('ss_page_load')->nullable()->default(3);
            $blueprint->boolean('sub_topic_enable')->default(true);
            $blueprint->integer('school_id')->nullable()->default(1)->unsigned();
            $blueprint->foreign('school_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $blueprint->string('software_version', 100)->nullable();
            $blueprint->string('email_driver')->default('php');

            $blueprint->text('fcm_key')->nullable();
            $blueprint->tinyInteger('multiple_roll')->nullable()->default(0);

            $blueprint->integer('Lesson')->default(1)->nullable();
            $blueprint->integer('Chat')->default(1)->nullable();
            $blueprint->integer('FeesCollection')->default(0)->nullable();
            $blueprint->integer('income_head_id')->default(0)->nullable();
            $blueprint->integer('InfixBiometrics')->default(0)->nullable();
            $blueprint->integer('ResultReports')->default(0)->nullable();
            $blueprint->integer('TemplateSettings')->default(1)->nullable();
            $blueprint->integer('MenuManage')->default(1)->nullable();
            $blueprint->integer('RolePermission')->default(1)->nullable();
            $blueprint->integer('RazorPay')->default(0)->nullable();
            $blueprint->integer('Saas')->default(1)->nullable();
            $blueprint->integer('StudentAbsentNotification')->default(1)->nullable();
            $blueprint->integer('ParentRegistration')->default(0)->nullable();
            $blueprint->integer('Zoom')->default(0)->nullable();
            $blueprint->integer('BBB')->default(0)->nullable();
            $blueprint->integer('VideoWatch')->default(0)->nullable();
            $blueprint->integer('Jitsi')->default(0)->nullable();
            $blueprint->integer('OnlineExam')->default(0)->nullable();
            $blueprint->integer('SaasRolePermission')->default(0)->nullable();
            $blueprint->integer('BulkPrint')->default(1)->nullable();
            $blueprint->integer('HimalayaSms')->default(1)->nullable();
            $blueprint->integer('XenditPayment')->default(1)->nullable();
            $blueprint->integer('Wallet')->default(1)->nullable();
            $blueprint->integer('Lms')->default(0)->nullable();
            $blueprint->integer('ExamPlan')->default(1)->nullable();
            $blueprint->integer('University')->default(0)->nullable();
            $blueprint->integer('Gmeet')->default(0)->nullable();
            $blueprint->integer('KhaltiPayment')->default(0)->nullable();
            $blueprint->integer('Raudhahpay')->default(0)->nullable();
            $blueprint->integer('AppSlider')->default(1)->nullable();
            $blueprint->integer('BehaviourRecords')->default(0)->nullable();
            $blueprint->integer('DownloadCenter')->default(1)->nullable();
            $blueprint->integer('AiContent')->default(0)->nullable();
            $blueprint->integer('WhatsappSupport')->default(0)->nullable();
            $blueprint->integer('InAppLiveClass')->default(0)->nullable();
            $blueprint->integer('fees_status')->default(1)->nullable();
            $blueprint->integer('lms_checkout')->default(0)->nullable();
            $blueprint->integer('academic_id')->nullable()->unsigned();
            $blueprint->foreign('academic_id')->references('id')->on('sm_academic_years')->onDelete('set null');
            $blueprint->tinyInteger('is_comment')->default(0)->nullable();
            $blueprint->tinyInteger('auto_approve')->default(0)->nullable();
            $blueprint->tinyInteger('blog_search')->default(1)->nullable();
            $blueprint->tinyInteger('recent_blog')->default(1)->nullable();

            // for university
            $blueprint->integer('un_academic_id')->default(1)->nullable()->unsigned();
            $blueprint->boolean('direct_fees_assign')->default(0);
            $blueprint->boolean('with_guardian')->default(1);
            $blueprint->string('result_type')->nullable();
            $blueprint->boolean('preloader_status')->default(1);
            $blueprint->tinyInteger('preloader_style')->default(3);
            $blueprint->tinyInteger('preloader_type')->default(1);
            $blueprint->string('preloader_image')->default('public/uploads/settings/preloader/preloader1.gif');
            $blueprint->boolean('due_fees_login')->default(0)->comment('1 = Login restricted by due date , 0 = No Restriction ');
            $blueprint->boolean('two_factor')->default(0)->comment('1 = Enable , 0 = Disable');
            $blueprint->string('active_theme')->default('edulia');
            $blueprint->string('queue_connection')->default('database');
            $blueprint->boolean('role_based_sidebar')->default(false);
        });

        DB::table('sm_general_settings')->insert([
            [
                'copyright_text' => 'Copyright © '.date('Y').' All rights reserved | This application is made with by Codethemes',
                'logo' => 'public/uploads/settings/logo.png',
                'favicon' => 'public/uploads/settings/favicon.png',
                'phone' => '+96897002784',
                'school_code' => '12345678',
                'email' => 'hello@aorasoft.com',
                'address' => 'Al Khuwair, Muscat, Oman',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'school_name' => 'Infix Edu',
                'site_title' => 'Infix Education software',
                'session_id' => 1,
                'week_start_id' => 3,
                'time_zone_id' => 51,
                'software_version' => '8.2.8',
                'system_activated_date' => date('Y-m-d'),
                'last_update' => date('Y-m-d'),
                'system_domain' => url('/'),
                'email_driver' => 'php',
                'income_head_id' => 1,
                'academic_id' => 1,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_general_settings');
    }
}
