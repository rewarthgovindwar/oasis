<?php

namespace App\Http\Controllers\Theme\Edulia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSection\SmVisitorRequest;
use App\Http\Requests\Admin\FrontSettings\ExamResultSearch;
use App\Models\FrontendExamResult;
use App\Models\SmDonor;
use App\Models\SmExpertTeacher;
use App\Models\SmNewsComment;
use App\Models\SmPhotoGallery;
use App\Models\SpeechSlider;
use App\Models\StudentRecord;
use App\SmAssignSubject;
use App\SmClass;
use App\SmClassOptionalSubject;
use App\SmCourse;
use App\SmCourseCategory;
use App\SmEvent;
use App\SmExam;
use App\SmExamSetting;
use App\SmExamType;
use App\SmMarksGrade;
use App\SmMarksRegister;
use App\SmNews;
use App\SmNewsCategory;
use App\SmNewsPage;
use App\SmNoticeBoard;
use App\SmOptionalSubjectAssign;
use App\SmResultStore;
use App\SmSection;
use App\SmStaff;
use App\SmStudent;
use App\SmVisitor;
use App\YearCheck;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function singleCourseDetails($course_id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.course_details'),
            ];
            $data['course'] = SmCourse::where('school_id', app('school')->id)->find($course_id);
            return view('frontEnd.theme.' . activeTheme() . '.course.single_course_details_page', $data);
    }

    public function singleNewsDetails($news_id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.blog_details'),
            ];
            $data['news'] = SmNews::with(['newsComments.onlyChildrenFrontend'])->where('school_id', app('school')->id)->findOrFail($news_id);
            return view('frontEnd.theme.' . activeTheme() . '.news.single_news_details_page', $data);
    }

    public function storeNewsComment(Request $request)
    {
        try {
            $newsDeyails = SmNews::find($request->news_id);
            $smNewsComment = new SmNewsComment();
            $smNewsComment->message = $request->message;
            $smNewsComment->news_id = $request->news_id;
            $smNewsComment->user_id = $request->user_id;
            $smNewsComment->parent_id = $request->parent_id ?? null;
            if ($newsDeyails->is_global === 1 && generalSetting()->auto_approve === 1) {
                $smNewsComment->status = 1;
            } elseif ($newsDeyails->is_global === 0 && $newsDeyails->auto_approve === 1) {
                $smNewsComment->status = 1;
            } else {
                $smNewsComment->status = 0;
            }

            $smNewsComment->save();
            if (request()->ajax()) {
                return response()->json(['success' => true]);
            }

            Toastr::success('Comment Store Successfully', 'Success');

            return redirect()->route('frontend.news-details', $request->news_id);

        } catch (Exception $exception) {
            if (request()->ajax()) {
                return response()->json(['error' => $exception]);
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        }
    }

    public function singleGalleryDetails($gallery_id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.gallery_details'),
            ];
            $data['gallery_feature'] = SmPhotoGallery::where('school_id', app('school')->id)->where('parent_id', '=', null)->findOrFail($gallery_id);
            $data['galleries'] = SmPhotoGallery::where('school_id', app('school')->id)->where('parent_id', '!=', null)->where('parent_id', $gallery_id)->get();
            return view('frontEnd.theme.' . activeTheme() . '.photoGallery.single_photo_gallery', $data);
    }

    public function singleNoticeDetails($notice_id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.notice_details'),
            ];
            $data['notice_detail'] = SmNoticeBoard::where('is_published', 1)->where('school_id', app('school')->id)->findOrFail($notice_id);
            $data['notices'] = SmNoticeBoard::where('publish_on', '<=', date('Y-m-d'))->where('is_published', 1)->where('school_id', app('school')->id)->get();
            return view('frontEnd.theme.' . activeTheme() . '.notice.single_notice', $data);
    }

    public function indiviualResult(ExamResultSearch $examResultSearch)
    {
            $exam_types = SmExamType::where('school_id', app('school')->id)->get();
            $page = FrontendExamResult::where('school_id', app('school')->id)->first();
            $school_id = app('school')->id;
            $student = SmStudent::where('admission_no', $examResultSearch->admission_number)->where('school_id', $school_id)->with('parents', 'group')->first();
            if ($student) {
                $exam_content = SmExamSetting::where('exam_type', $examResultSearch->exam)
                    ->where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', app('school')->id)
                    ->first();
                $student_detail = StudentRecord::where('student_id', $student->id)
                    ->where('academic_id', getAcademicId())
                    ->where('is_promote', 0)
                    ->where('school_id', $school_id)
                    ->first();
                $studentDetails = $student_detail;

                $section_id = $student_detail->section_id;
                $class_id = $student_detail->class_id;
                $exam_type_id = $examResultSearch->exam;
                $student_id = $student->id;
                $exam_id = $examResultSearch->exam;

                $failgpa = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->min('gpa');

                $failgpaname = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->where('gpa', $failgpa)
                    ->first();

                $exams = SmExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $examSubjects = SmExam::where([['exam_type_id',  $exam_type_id], ['section_id', $section_id], ['class_id', $class_id]])
                    ->where('school_id', $school_id)
                    ->where('academic_id', getAcademicId())
                    ->get();
                $examSubjectIds = [];
                foreach ($examSubjects as $examSubject) {
                    $examSubjectIds[] = $examSubject->subject_id;
                }

                $subjects = $studentDetails->class->subjects->where('section_id', $section_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id);
                $subjects = $examSubjects;

                $exam_details = $exams->where('active_status', 1)->find($exam_type_id);

                $optional_subject = '';
                $get_optional_subject = SmOptionalSubjectAssign::where('record_id', '=', $student_detail->id)
                    ->where('session_id', '=', $student_detail->session_id)
                    ->first();

                if ($get_optional_subject !== '') {
                    $optional_subject = $get_optional_subject->subject_id;
                }

                $optional_subject_setup = SmClassOptionalSubject::where('class_id', '=', $class_id)
                    ->first();

                $mark_sheet = SmResultStore::where([['class_id', $class_id], ['exam_type_id', $examResultSearch->exam], ['section_id', $section_id], ['student_id', $student_id]])
                    ->whereIn('subject_id', $subjects->pluck('subject_id')->toArray())
                    ->where('school_id', $school_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->orderBy('gpa', 'desc')
                    ->get();

                $maxGrade = SmMarksGrade::where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->max('gpa');

                if (count($mark_sheet) === 0) {
                    Toastr::error('Ops! Your result is not found! Please check mark register', 'Failed');

                    return redirect()->back();
                }

                $is_result_available = SmResultStore::where([['class_id', $class_id], ['exam_type_id', $examResultSearch->exam], ['section_id', $section_id], ['student_id', $student_id]])
                    ->where('created_at', 'LIKE', '%'.YearCheck::getYear().'%')
                    ->where('school_id', $school_id)
                    ->get();

                $marks_register = SmMarksRegister::where('exam_id', $examResultSearch->exam)
                    ->where('student_id', $student_id)
                    ->first();

                $subjects = SmAssignSubject::where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->whereIn('subject_id', $examSubjectIds)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $exams = SmExamType::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $classes = SmClass::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $grades = SmMarksGrade::where('active_status', 1)
                    ->where('academic_id', getAcademicId())
                    ->where('school_id', $school_id)
                    ->get();

                $class = SmClass::find($class_id);
                $section = SmSection::find($section_id);
                $exam_detail = SmExam::find($examResultSearch->exam);

                return view('frontEnd.theme.' . activeTheme() . '.indivisualResult.indivisual_result', compact(
                    'student',
                    'optional_subject',
                    'classes',
                    'studentDetails',
                    'exams',
                    'classes',
                    'marks_register',
                    'subjects',
                    'class',
                    'section',
                    'exam_detail',
                    'exam_content',
                    'grades',
                    'student_detail',
                    'mark_sheet',
                    'exam_details',
                    'maxGrade',
                    'failgpaname',
                    'exam_id',
                    'exam_type_id',
                    'class_id',
                    'section_id',
                    'student_id',
                    'optional_subject_setup',
                    'exam_types',
                    'page'
                ));
            } else {
                Toastr::error('Student Not Found', 'Failed');
                return redirect()->back();
            }
    }

    public function allBlogList()
    {
            $data['news'] = SmNews::where('school_id', app('school')->id)->where('mark_as_archive', 0)->paginate(8);
            $data['newsPage'] = SmNewsPage::where('school_id', app('school')->id)->first();
            return view('frontEnd.theme.' . activeTheme() . '.news.all_news_list', $data);
    }

    public function loadMoreBlogs(Request $request)
    {
            $data['count'] = SmNews::where('mark_as_archive', 0)->count();
            $data['skip'] = $request->skip;
            $data['limit'] = $data['count'] - $data['skip'];
            $data['news'] = SmNews::skip($data['skip'])->where('school_id', app('school')->id)->where('mark_as_archive',0)->take(4)->get();
            return view('frontEnd.theme.' . activeTheme() . '.news.load_more_news', $data);
    }

    public function singleEventDetails($id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.event_details'),
            ];
            $data['event'] = SmEvent::with('user')->find($id);
            return view('frontEnd.theme.' . activeTheme() . '.single_event', $data);
    }

    public function blogList()
    {
            $data['blogs'] = SmNews::with('category')->where('mark_as_archive',0)->where('school_id', app('school')->id);
            return view('frontEnd.theme.' . activeTheme() . '.blog_list', $data);
    }

    public function loadMoreBlogList(Request $request)
    {
            $skip = $request->skip;
            $take = 5;
            $totalDataCount = SmNews::where('school_id', app('school')->id)->where('mark_as_archive', 0)->count();
            $blogs = SmNews::where('school_id', app('school')->id)->where('mark_as_archive', 0)
                ->skip($skip)
                ->take($take)
                ->get();

            $html = view('frontEnd.theme.'.activeTheme().'.read_more_blog_list', ['blogs' => $blogs])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'loaded_data_count' => $blogs->count(),
                'total_data' => $totalDataCount,
            ]);
    }

    public function loadMorePhotoGalleryList(Request $request)
    {
            $skip = $request->skip;
            $take = $request->take;
            $totalCount = SmPhotoGallery::where('school_id', app('school')->id)->count();
            $remainingCount = $totalCount - $skip;

            $photoGalleries = SmPhotoGallery::where('school_id', app('school')->id)
                ->skip($skip)
                ->take($take)
                ->get();

            $html = view('frontEnd.theme.'.activeTheme().'.read_more_photo_gallery_list', [
                'photoGalleries' => $photoGalleries,
                'column' => $request->row_each_column,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'has_more' => $remainingCount > $take,
            ]);
    }

    public function loadMoreEvents(Request $request)
    {
            $skip = $request->skip;
            $take = $request->take;
            $totalCount = SmEvent::where('school_id', app('school')->id)->count();
            $remainingCount = $totalCount - $skip;

            $events = SmEvent::where('school_id', app('school')->id)
                ->skip($skip)
                ->take($take)
                ->orderBy('id', $request->sorting === 'asc' ? 'asc' : ($request->sorting === 'desc' ? 'desc' : 'random'))
                ->get();

            $html = view('frontEnd.theme.'.activeTheme().'.read_more_events', [
                'events' => $events,
                'dateshow' => $request->dateshow,
                'enevtlocation' => $request->enevtlocation,
                'column' => $request->row_each_column,
                'button' => $request->button,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'has_more' => $remainingCount > $take,
            ]);
    }

    public function loadMoreCourseList(Request $request)
    {
            $skip = $request->skip;
            $take = $request->take;
            $totalCount = SmCourse::where('school_id', app('school')->id)->count();
            $remainingCount = $totalCount - $skip;

            $coursesQuery = SmCourse::where('school_id', app('school')->id);

            if ($request->sorting === 'asc') {
                $coursesQuery->orderBy('id', 'asc');
            } elseif ($request->sorting === 'desc') {
                $coursesQuery->orderBy('id', 'desc');
            } else {
                $coursesQuery->inRandomOrder();
            }

            $courses = $coursesQuery->skip($skip)->take($take)->get();

            $html = view('frontEnd.theme.'.activeTheme().'.read_more_course_list', [
                'courses' => $courses,
                'column' => $request->row_each_column,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'has_more' => $remainingCount > $take,
            ]);
    }

    public function singleSpeechSlider($id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.speech_details'),
            ];
            $data['singleSpeechSlider'] = SpeechSlider::where('school_id', app('school')->id)->findOrFail($id);
            return view('frontEnd.theme.' . activeTheme() . '.speechSlider.single_speech_slider', $data);
    }

    public function courseList()
    {
            $data['courseCategories'] = SmCourseCategory::where('school_id', app('school')->id)->with('courses')->get();
            return view('frontEnd.theme.' . activeTheme() . '.courseList.course_list', $data);
    }

    public function singleCourseDetail($id)
    {
            $data['singleCourseDetail'] = SmCourse::where('id', $id)->where('school_id', app('school')->id)->with('courseCategory')->first();
            return view('frontEnd.theme.' . activeTheme() . '.courseList.single_course_details', $data);
    }

    public function frontendSingleStudentDetails($id)
    {
            $data['page'] = (object) [
                'title' => __('edulia.student_details'),
            ];
            $data['singleStudent'] = SmStudent::where('id', $id)->where('school_id', app('school')->id)->with('parents', 'gender', 'religion', 'bloodGroup', 'studentRecord.class', 'studentRecord.section')->first();
            return view('frontEnd.theme.' . activeTheme() . '.frontend_single_student_details', $data);
    }

    public function archiveList()
    {
            $data['page'] = (object) [
                'title' => __('edulia.archive_list'),
            ];
            $data['archives'] = SmNews::with('category')->where('mark_as_archive', 1)->where('school_id', app('school')->id);
            $data['archiveYears'] = $data['archives']->get()->groupBy(function ($q) {
                return $q->created_at->format('Y');
            });
            $data['archiveCategories'] = SmNewsCategory::where('school_id', app('school')->id)->get();
            return view('frontEnd.theme.' . activeTheme() . '.archive.archive_list', $data);
    }

    public function loadMoreArchiveList(Request $request)
    {
            $years = $request->year;
            $data['count'] = SmNews::where('mark_as_archive', 1)->count();
            $data['skip'] = $request->skip;
            $data['limit'] = $data['count'] - $data['skip'];
            $data['archives'] = SmNews::where('mark_as_archive', 1)->when($request->year, function ($q) use ($years): void {
                $q->where(function ($query) use ($years): void {
                    foreach ($years as $year) {
                        $query->whereYear('created_at', '=', $year, 'or');
                    }
                });
            })->skip($data['skip'])->where('school_id', app('school')->id)->take(5)->get();
            $html = view('frontEnd.theme.'.activeTheme().'.archive.read_more_archive_list', $data)->render();

            return response()->json(['success' => true, 'html' => $html, 'total_data' => $data['count']]);
    }

    public function archiveYearFilter(Request $request)
    {
            $years = $request->year;
            $data['archives'] = SmNews::with('category')
                ->where('school_id', app('school')->id)
                ->where('mark_as_archive', 1)
                ->when($request->data_count > 0, function ($q) use ($years): void {
                    $q->where(function ($q) use ($years): void {
                        foreach ($years as $year) {
                            $q->whereYear('created_at', '=', $year, 'or');
                        }
                    });
                })->paginate(5);
            $html = view('frontEnd.theme.'.activeTheme().'.archive.archive_year_filter', $data)->render();

            return response()->json(['success' => true, 'html' => $html]);
    }

    public function bookAVisit()
    {
            $data['page'] = (object) [
                'title' => __('edulia.book_a_visit'),
            ];
            return view('frontEnd.theme.' . activeTheme() . '.visit_a_book', $data);
    }

    public function bookAVisitStore(SmVisitorRequest $smVisitorRequest)
    {
            $destination = 'public/uploads/visitor/';
            $fileName = fileUpload($smVisitorRequest->upload_event_image, $destination);
            $smVisitor = new SmVisitor();
            $smVisitor->name = $smVisitorRequest->name;
            $smVisitor->phone = $smVisitorRequest->phone;
            $smVisitor->visitor_id = $smVisitorRequest->visitor_id;
            $smVisitor->no_of_person = $smVisitorRequest->no_of_person;
            $smVisitor->purpose = $smVisitorRequest->purpose;
            $smVisitor->date = date('Y-m-d', strtotime($smVisitorRequest->date));
            $smVisitor->in_time = $smVisitorRequest->in_time;
            $smVisitor->out_time = $smVisitorRequest->out_time;
            $smVisitor->file = $fileName;
            $smVisitor->created_by = null;
            $smVisitor->school_id = app('school')->id;
            $smVisitor->academic_id = getAcademicId();
            $smVisitor->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
    }

    public function donorDetails($id)
    {
            $data['donorDetails'] = SmDonor::where('id', $id)->where('school_id', app('school')->id)->where('show_public', 1)->first();
            $data['custom_filed_values'] = json_decode($data['donorDetails']->custom_field);
            return view('frontEnd.theme.' . activeTheme() . '.donor.donor_details', $data);
    }

    public function staffDetails($id = null)
    {
            $data['page'] = (object) [
                'title' => __('edulia.staff_details'),
            ];

            $expert = SmExpertTeacher::where('staff_id', $id)->first();
            if ($expert) {
                $data['staffDetails'] = SmStaff::where('id', $id)->where('school_id', app('school')->id)->first();
                return view('frontEnd.theme.' . activeTheme() . '.staff.staff_details', $data);
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
    }
}
