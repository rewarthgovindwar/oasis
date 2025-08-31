<?php

namespace Database\Seeders\Academics;

use App\Models\StudentRecord;
use App\SmAdmissionQuery;
use App\SmAdmissionQueryFollowup;
use App\SmClass;
use App\SmParent;
use App\SmSection;
use App\SmStaff;
use App\SmStaffAttendence;
use App\SmStudent;
use App\SmStudentAttendance;
use App\SmSubject;
use App\User;
use Illuminate\Database\Seeder;

class SmClassesTableSeeder extends Seeder
{
    public $sections;

    public $subjects;

    public function __construct()
    {
        $this->sections = SmSection::all();
        $this->subjects = SmSubject::all();
    }

    /**
     * Run the database seeds.
     */
    public function run($school_id = 1, $academic_id = 1, int $count = 1): void
    {
        $sections = SmSection::where('school_id', $school_id)->where('academic_id', $academic_id)->get();
        SmSubject::where('school_id', $school_id)->where('academic_id', $academic_id)->get();

        SmClass::factory()->times($count)->create([
            'school_id' => $school_id,
            'academic_id' => $academic_id,
        ])->each(function ($class) use ($sections, $school_id): void {
            $class_sections = [];
            foreach ($sections as $section) {
                $class_sections[] = [
                    'section_id' => $section->id,
                    'school_id' => $school_id,
                    'academic_id' => $class->academic_id,
                ];
                $i = 0;
                SmStudent::factory()->times(5)->create()->each(function ($student) use ($class, $section, $school_id): void {

                    User::factory()->times(1)->create([
                        'role_id' => 2,
                        'email' => $student->email,
                        'username' => $student->email,
                        'school_id' => $school_id,
                    ])->each(function ($user) use ($student): void {
                        $student->user_id = $user->id;
                        $student->save();
                    });

                    SmParent::factory()->times(1)->create([
                        'school_id' => $school_id,
                        'guardians_email' => 'guardian_'.$student->id.'@infixedu.com',
                    ])->each(function ($parent) use ($student, $school_id): void {
                        $student->parent_id = $parent->id;
                        $student->save();
                        User::factory()->times(1)->create([
                            'role_id' => 3,
                            'email' => $parent->guardians_email,
                            'username' => $parent->guardians_email,
                            'school_id' => $school_id,
                        ])->each(function ($user) use ($parent): void {
                            $parent->user_id = $user->id;
                            $parent->save();
                        });
                    });

                    $studentRecord = new StudentRecord();
                    $studentRecord->class_id = $class->id;
                    $studentRecord->section_id = $section->id;
                    $studentRecord->school_id = $school_id;
                    $studentRecord->academic_id = $class->academic_id;
                    $studentRecord->roll_no = $student->roll_no;
                    $studentRecord->session_id = $class->academic_id;
                    $studentRecord->is_default = 1;
                    $studentRecord->student_id = $student->id;
                    $studentRecord->save();

                    $attendance_type = ['P', 'L', 'A', 'F'];
                    foreach (lastOneMonthDates() as $date) {
                        shuffle($attendance_type);
                        $studentAttendance = new SmStudentAttendance();
                        $studentAttendance->student_record_id = $studentRecord->id;
                        $studentAttendance->student_id = $studentRecord->student_id;
                        $studentAttendance->class_id = $studentRecord->class_id;
                        $studentAttendance->section_id = $studentRecord->section_id;
                        $studentAttendance->attendance_type = $attendance_type[0];
                        $studentAttendance->notes = $studentAttendance->attendance_type === 'P' ? 'Good' : 'Bad';
                        $studentAttendance->attendance_date = $date;
                        $studentAttendance->school_id = $school_id;
                        $studentAttendance->academic_id = $studentRecord->academic_id;
                        $studentAttendance->save();
                    }
                });
            }

            $class_sections = $class->classSection()->createMany($class_sections);
            $assign_class_teachers = [];
            foreach ($class_sections as $class_section) {
                $assign_class_teachers[] = [
                    'class_id' => $class_section->class_id,
                    'section_id' => $class_section->section_id,
                    'academic_id' => $class_section->academic_id,
                    'school_id' => $school_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                User::factory()->times(1)->create([
                    'school_id' => $school_id,
                ])->each(function ($userStaff) use ($school_id): void {
                    SmStaff::factory()->times(1)->create([
                        'user_id' => $userStaff->id,
                        'email' => $userStaff->email,
                        'first_name' => $userStaff->first_name,
                        'last_name' => $userStaff->last_name,
                        'full_name' => $userStaff->full_name,
                        'school_id' => $school_id,
                        'role_id' => 4,
                    ])->each(function ($staff) use ($school_id): void {
                        $staff->staff_no = $staff->id;
                        $staff->mobile = '+8801234567'.$staff->id;
                        $staff->save();

                        $attendance_type = ['P', 'L', 'A', 'F'];
                        foreach (lastOneMonthDates() as $date) {
                            shuffle($attendance_type);
                            $attendanceStaff = new SmStaffAttendence();
                            $attendanceStaff->staff_id = $staff->id;
                            $attendanceStaff->school_id = $school_id;
                            $attendanceStaff->attendence_type = $attendance_type[0];
                            $attendanceStaff->notes = $attendanceStaff->attendance_type === 'P' ? 'Good' : 'Bad';
                            $attendanceStaff->attendence_date = $date;
                            $attendanceStaff->save();
                        }
                    });
                });
            }

            SmAdmissionQuery::factory()->times(10)->create([
                'class' => $class->id,
                'school_id' => $school_id,
                'academic_id' => $class->academic_id,
            ])->each(function ($admission_query) use ($school_id): void {
                SmAdmissionQueryFollowup::factory()->times(random_int(5, 10))->create([
                    'admission_query_id' => $admission_query->id,
                    'school_id' => $school_id,
                    'academic_id' => $admission_query->academic_id,
                ]);
            });
        });
    }
}
