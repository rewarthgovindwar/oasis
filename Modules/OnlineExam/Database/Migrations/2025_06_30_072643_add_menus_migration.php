<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMenusMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $routes = $this->getMenus();
        foreach($routes as $route)
        {
            DB::table('sm_menus')->where('route',$route['route'])->delete();
            DB::table('default_menus')->where('route',$route['route'])->delete();
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $routes = $this->getMenus();
        foreach($routes as $route)
        {
            DB::table('sm_menus')->where('route',$route['route'])->delete();
            DB::table('default_menus')->where('route',$route['route'])->delete();
        }
    }

    public function getMenus()
    {
        $admin_menu = DB::table('sm_menus')->where('route','online_exam')->where('role_id',1)->first();
        $student_menu  = DB::table('sm_menus')->where('route','online_exam')->where('role_id',2)->first();
        $parent  = DB::table('sm_menus')->where('route','online_exam')->where('role_id',3)->first();
        $schools = DB::table('sm_schools')->get();
        $menus = [];
        
        foreach($schools as $school)
         {
            $menus = [
            //Admin
            [
                "parent" =>  $admin_menu->id,
                "parent_id" =>  $admin_menu->id,
                "name" => "Question Group",
                "lang_name" => "onlineexam::onlineExam.question_group",
                'route' => "online-question-group",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 1,
                'module' => 'OnlineExam',
                'position' => 1,
                'default_position' => 1,
                'school_id' => $school->id
            ],
            [
                "parent" =>  $admin_menu->id,
                "parent_id" =>  $admin_menu->id,
                "name" => "Question Bank",
                "lang_name" => "onlineexam::onlineExam.question_bank",
                'route' => "online-question-bank",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 1,
                'module' => 'OnlineExam',
                'position' => 2,
                'default_position' => 2,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $admin_menu->id,
                "parent_id" =>  $admin_menu->id,
                "name" => "Add Online Exam",
                "lang_name" => "onlineexam::onlineExam.add_online_exam",
                'route' => "om-online-exam-add",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 1,
                'module' => 'OnlineExam',
                'position' => 3,
                'default_position' => 3,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $admin_menu->id,
                "parent_id" =>  $admin_menu->id,
                "name" => "Online Exam",
                "lang_name" => "onlineexam::onlineExam.online_exam",
                'route' => "om-online-exam",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 1,
                'module' => 'OnlineExam',
                'position' => 4,
                'default_position' => 4,
                'school_id' => $school->id

            ],            
            [
                "parent" =>  $admin_menu->id,
                "parent_id" =>  $admin_menu->id,
                "name" => "Written Exam",
                "lang_name" => "onlineexam::onlineExam.written_exam",
                'route' => "written_exam",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 1,
                'module' => 'OnlineExam',
                'position' => 5,
                'default_position' => 5,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $admin_menu->id,
                "parent_id" =>  $admin_menu->id,
                "name" => "Setting",
                "lang_name" => "onlineexam::onlineExam.settings",
                'route' => "online-exam-setting",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 1,
                'module' => 'OnlineExam',
                'position' => 6,
                'default_position' => 6,
                'school_id' => $school->id

            ],

            //Student
            [
                "parent" =>  $student_menu->id,
                "parent_id" =>  $student_menu->id,
                "name" => "Active Exams",
                "lang_name" => "exam.active_exams",
                'route' => "om_student_online_exam",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 2,
                'module' => 'OnlineExam',
                'position' => 1,
                'default_position' => 1,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $student_menu->id,
                "parent_id" =>  $student_menu->id,
                "name" => "View Results",
                "lang_name" => "exam.view_result",
                'route' => "om_student_view_result",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 2,
                'module' => 'OnlineExam',
                'position' => 2,
                'default_position' => 2,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $student_menu->id,
                "parent_id" =>  $student_menu->id,
                "name" => "Written Exam",
                "lang_name" => "exam.written_exam",
                'route' => "student_written_exam",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 2,
                'module' => 'OnlineExam',
                'position' => 3,
                'default_position' => 3,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $student_menu->id,
                "parent_id" =>  $student_menu->id,
                "name" => "PDF Exam Result",
                "lang_name" => "exam.PDF Exam Result",
                'route' => "student_view_written_result",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 2,
                'module' => 'OnlineExam',
                'position' => 4,
                'default_position' => 4,
                'school_id' => $school->id

            ],

            //Parents
            [
                "parent" =>  $parent->id,
                "parent_id" =>  $parent->id,
                "name" => "Active Exams",
                "lang_name" => "exam.active_exams",
                'route' => "om_parent_online_examination",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 3,
                'module' => 'OnlineExam',
                'position' => 1,
                'default_position' => 1,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $parent->id,
                "parent_id" =>  $parent->id,
                "name" => "Exam Result",
                "lang_name" => "onlineexam::onlineExam.online_exam_result",
                'route' => "om_parent_online_examination_result",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 3,
                'module' => 'OnlineExam',
                'position' => 2,
                'default_position' => 2,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $parent->id,
                "parent_id" =>  $parent->id,
                "name" => "Pdf Exam",
                "lang_name" => "onlineexam::onlineExam.pdf_exam",
                'route' => "parent_pdf_exam",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 3,
                'module' => 'OnlineExam',
                'position' => 3,
                'default_position' => 3,
                'school_id' => $school->id

            ],
            [
                "parent" =>  $parent->id,
                "parent_id" =>  $parent->id,
                "name" => "Pdf Exam Result",
                "lang_name" => "onlineexam::onlineExam.pdf_exam_result",
                'route' => "parent_view_pdf_result",
                'status' => 1,
                'menu_status' => 1,
                'role_id' => 3,
                'module' => 'OnlineExam',
                'position' => 4,
                'default_position' => 4,
                'school_id' => $school->id

            ],
          ];
         }
        return $menus;
    }
}
