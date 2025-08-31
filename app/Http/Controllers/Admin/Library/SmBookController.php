<?php

namespace App\Http\Controllers\Admin\Library;

use Exception;
use App\SmBook;
use App\SmStaff;
use App\SmParent;
use App\SmStudent;
use App\tableList;
use Carbon\Carbon;
use App\SmBookIssue;
use App\ApiBaseMethod;
use App\LibrarySubject;
use App\SmBookCategory;
use App\SmLibraryMember;
use Illuminate\Http\Request;
use App\Traits\NotificationSend;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\Library\SmBookRequest;
use App\Http\Requests\Admin\Library\SaveIssueBookRequest;
use App\Http\Requests\Admin\Library\LibrarySubjectRequest;

class SmBookController extends Controller
{
    use NotificationSend;

    // public static function pp($data): void
    // {

    //     echo '<pre>';
    //     print_r($data);
    //     exit;
    // }

    public function index(Request $request)
    {

        /*
        try {
        */
        $books = SmBook::leftjoin('library_subjects', 'sm_books.book_subject_id', '=', 'library_subjects.id')
            ->leftjoin('sm_book_categories', 'sm_books.book_category_id', '=', 'sm_book_categories.id')
            ->select('sm_books.*', 'library_subjects.subject_name', 'sm_book_categories.category_name')
            ->orderby('sm_books.id', 'DESC')
            ->get();

        return view('backEnd.library.bookList', ['books' => $books]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function addBook(Request $request)
    {
        /*
        try {
        */
        $categories = SmBookCategory::get();
        $subjects = LibrarySubject::get();

        return view('backEnd.library.addBook', ['categories' => $categories, 'subjects' => $subjects]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function saveBookData(SmBookRequest $smBookRequest)
    {
        /*
        try {
        */
        $smBook = new SmBook();
        $smBook->book_title = $smBookRequest->book_title;
        $smBook->book_category_id = $smBookRequest->book_category_id;
        $smBook->book_number = $smBookRequest->book_number;
        $smBook->isbn_no = $smBookRequest->isbn_no;
        $smBook->publisher_name = $smBookRequest->publisher_name;
        $smBook->author_name = $smBookRequest->author_name;
        if (@$smBookRequest->subject) {
            $smBook->book_subject_id = $smBookRequest->subject;
        }

        $smBook->rack_number = $smBookRequest->rack_number;
        if (@$smBookRequest->quantity !== '') {
            $smBook->quantity = $smBookRequest->quantity;
        }

        if (@$smBookRequest->book_price !== '') {
            $smBook->book_price = $smBookRequest->book_price;
        }

        $smBook->details = $smBookRequest->details;
        $smBook->post_date = date('Y-m-d');
        $smBook->created_by = Auth::user()->id;
        $smBook->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $smBook->un_academic_id = getAcademicId();
        } else {
            $smBook->academic_id = getAcademicId();
        }

        $smBook->save();

        Toastr::success('Operation successful', 'Success');

        return redirect('book-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function editBook(Request $request, $id)
    {
        /*
        try {
        */
        $editData = checkAdmin() == true ? SmBook::find($id) : SmBook::where('id', $id)->first();
        $categories = SmBookCategory::get();
        $subjects = LibrarySubject::get();

        return view('backEnd.library.addBook', ['editData' => $editData, 'categories' => $categories, 'subjects' => $subjects]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function updateBookData(SmBookRequest $smBookRequest, $id)
    {
        /*
        try {
        */
        $books = checkAdmin() == true ? SmBook::find($id) : SmBook::where('id', $id)->first();
        $books->book_title = $smBookRequest->book_title;
        $books->book_category_id = $smBookRequest->book_category_id;
        $books->book_number = $smBookRequest->book_number;
        $books->isbn_no = $smBookRequest->isbn_no;
        $books->publisher_name = $smBookRequest->publisher_name;
        $books->author_name = $smBookRequest->author_name;
        if (@$smBookRequest->subject) {
            $books->book_subject_id = $smBookRequest->subject;
        }

        $books->rack_number = $smBookRequest->rack_number;
        if (@$smBookRequest->quantity !== '') {
            $books->quantity = $smBookRequest->quantity;
        }

        $books->book_price = $smBookRequest->book_price;
        $books->details = $smBookRequest->details;
        $books->post_date = date('Y-m-d');
        $books->updated_by = auth()->user()->id;
        $books->update();

        Toastr::success('Operation successful', 'Success');

        return redirect('book-list');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteBookView(Request $request, string $id)
    {
        /*
        try {
        */
        $title = __('common.are_you_sure_to_delete');
        $url = url('delete-book/'.$id);

        return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteBook(Request $request, $id)
    {
        $tables = tableList::getTableList('book_id', $id);
        /*
        try {
        */
        if ($tables == null) {

            $result = checkAdmin() == true ? SmBook::destroy($id) : SmBook::where('id', $id)->delete();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }

        $msg = 'This data already used in  : '.$tables.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();

        /*
        } catch (\Illuminate\Database\QueryException $queryException) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
        */
    }

    public function memberList(Request $request)
    {

        /*
        try {
        */
        $activeMembers = SmLibraryMember::with('roles', 'studentDetails', 'staffDetails', 'parentsDetails', 'memberTypes')->where('school_id', Auth::user()->school_id)->where('active_status', '=', 1)->get();

        return view('backEnd.library.memberLists', ['activeMembers' => $activeMembers]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function issueBooks(Request $request, $member_type, $student_staff_id)
    {

        /*
        try {
        */
        $memberDetails = SmLibraryMember::where('student_staff_id', '=', $student_staff_id)->first();

        if ($member_type == 2) {
            $getMemberDetails = SmStudent::where('user_id', '=', $student_staff_id)
                ->select('first_name', 'last_name', 'full_name', 'email', 'mobile', 'student_photo')
                ->first();
        } elseif ($member_type == 3) {
            $getMemberDetails = SmParent::where('user_id', '=', $student_staff_id)
                ->select('guardians_name', 'guardians_email', 'guardians_mobile', 'guardians_photo')
                ->first();
        } else {
            $getMemberDetails = SmStaff::where('user_id', '=', $student_staff_id)
                ->select('full_name', 'email', 'mobile', 'staff_photo')
                ->first();
        }

        $books = SmBook::where('school_id', Auth::user()->school_id)->get();
        $totalIssuedBooks = SmBookIssue::where('school_id', Auth::user()->school_id)->where('member_id', '=', $student_staff_id)->get();

        return view('backEnd.library.issueBooks', ['memberDetails' => $memberDetails, 'books' => $books, 'getMemberDetails' => $getMemberDetails, 'totalIssuedBooks' => $totalIssuedBooks]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function saveIssueBookData(SaveIssueBookRequest $saveIssueBookRequest)
    {
        // dd($saveIssueBookRequest->all());
        $input = $saveIssueBookRequest->all();
        if (ApiBaseMethod::checkUrl($saveIssueBookRequest->fullUrl())) {
            $validator = Validator::make($input, [
                'book_id' => 'required',
                'due_date' => 'required|after:now',
                'user_id' => 'required',
            ]);
        } else {
            $validator = Validator::make($input, [
                'book_id' => 'required',
                'due_date' => 'required|after:now',
            ]);
        }

        $check_issue_status = SmBookIssue::where('member_id', $saveIssueBookRequest->member_id)
            ->where('book_id', $saveIssueBookRequest->book_id)
            ->where('issue_status', '=', 'I')
            ->first();
        if ($check_issue_status) {
            Toastr::warning('You have already issued this book', 'Failed');

            return redirect()->back();
        }

        $book_quantity = SmBook::find($saveIssueBookRequest->book_id);
        $book_quantity = $book_quantity->quantity;

        if ($book_quantity == 0) {
            Toastr::warning('This book not available now', 'Failed');

            return redirect()->back();
        }

        /*
        try {
        */
        $smBookIssue = new SmBookIssue();
        $smBookIssue->book_id = $saveIssueBookRequest->book_id;
        $smBookIssue->member_id = $saveIssueBookRequest->member_id;
        $smBookIssue->given_date = date('Y-m-d');
        $smBookIssue->due_date = date('Y-m-d', strtotime($saveIssueBookRequest->due_date));
        $smBookIssue->issue_status = 'I';
        $smBookIssue->school_id = Auth::user()->school_id;

        if (moduleStatusCheck('University')) {
            $smBookIssue->un_academic_id = getAcademicId();
        } else {
            $smBookIssue->academic_id = getAcademicId();
        }

        $smBookIssue->created_by = auth()->user()->id;
        $results = $smBookIssue->save();

        $data['date'] = $smBookIssue->given_date;
        $data['book'] = $smBookIssue->books->book_title;
        $data['class_id'] = $return->member?->studentDetails?->studentRecord['class_id'] ?? '';
        $data['section_id'] = $return->member?->studentDetails?->studentRecord['section_id'] ?? '';
        $records = $this->studentRecordInfo($data['class_id'], $data['section_id'])->pluck('studentDetail.user_id');
        $this->sent_notifications('Issue/Return_Book', $records, $data, ['Student', 'Parent']);

        $smBookIssue->toArray();

        if ($results) {
            $books = SmBook::find($saveIssueBookRequest->book_id);
            $books->quantity -= 1;
            $books->update();
        }

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function returnBookView(Request $request, $issue_book_id)
    {
        /*
        try {
        */
        return view('backEnd.library.returnBookView', ['issue_book_id' => $issue_book_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function returnBook(Request $request, $issue_book_id)
    {

        /*
        try {
        */
        $user = Auth()->user();
        $updated_by = $user ? $user->id : $request->updated_by;
        $return = SmBookIssue::find($issue_book_id);
        $return->issue_status = 'R';
        $return->updated_by = Auth()->user()->id;
        $results = $return->update();

        if ($results) {
            $books_id = SmBookIssue::where('id', $issue_book_id)
                ->select('book_id')
                ->first();
            $books = SmBook::find($books_id->book_id);
            $books->quantity += 1;
            $books->update();
        }

        $data['date'] = $return->given_date;
        $data['book'] = $return->books->book_title;
        $data['class_id'] = $return->member?->studentDetails?->studentRecord?->class_id ?? '';
        $data['section_id'] = $return->member?->studentDetails?->studentRecord?->section_id ?? '';

        $records = $this->studentRecordInfo($data['class_id'], $data['section_id'])->pluck('studentDetail.user_id');
        $this->sent_notifications('Issue/Return_Book', $records, $data, ['Student', 'Parent']);

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function allIssuedBook(Request $request)
    {
        /*
        try {
        */
        $books = SmBook::select('id', 'book_title')->get();
        $subjects = LibrarySubject::select('id', 'subject_name')->get();
        $now = Carbon::now();

        return view('backEnd.library.allIssuedBook', ['books' => $books, 'subjects' => $subjects, 'now' => $now]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function searchIssuedBook(Request $request)
    {
        /*
        try {
        */
        $book_id = $request->book_id;
        $book_number = $request->book_number;
        $subject_id = $request->subject_id;
        $now = Carbon::now();
        $issueBooks = SmBookIssue::whereHas('books', function ($query) use ($request): void {
            $query->where('id', $request->book_id);
        })->get();

        if ($request->book_number) {
            $issueBooks = SmBookIssue::whereHas('books', function ($query) use ($request): void {
                $query->where('id', $request->book_id)->where('book_number', $request->book_number);
            })->get();
        }

        if ($request->subject_id) {
            $issueBooks = SmBookIssue::whereHas('books', function ($query) use ($request): void {
                $query->where('id', $request->book_id)->where('book_subject_id', $request->subject_id);
            })->get();
        }

        if ($request->subject_id && $request->book_number) {
            $issueBooks = SmBookIssue::whereHas('books', function ($query) use ($request): void {
                $query->where('id', $request->book_id)->where('book_number', $request->book_number)->where('book_subject_id', $request->subject_id);
            })->get();
        }

        $books = SmBook::select('id', 'book_title')->where('active_status', 1)->get();
        $subjects = LibrarySubject::select('id', 'subject_name')->where('active_status', 1)->get();

        return view('backEnd.library.allIssuedBook', ['issueBooks' => $issueBooks, 'books' => $books, 'subjects' => $subjects, 'book_id' => $book_id, 'book_number' => $book_number, 'subject_id' => $subject_id, 'now' => $now]);
        /*
        } catch (\Exception $e) {
           Toastr::error($e->getMessage(), 'Failed');
           return redirect()->back();
        }
        */
    }

    public function bookListApi(Request $request)
    {

        /*
        try {
        */
        $books = DB::table('sm_books')
            ->join('library_subjects', 'sm_books.subject', '=', 'library_subjects.id')
            ->where('sm_books.school_id', Auth::user()->school_id)
            ->get();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {

            return ApiBaseMethod::sendResponse($books, null);
        }
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    // Library Book Subjects

    public function subjectList(Request $request)
    {
        /*
        try {
        */
        $subjects = LibrarySubject::where('school_id', auth()->user()->school_id)->with('category')->get();
        $bookCategories = SmBookCategory::get();

        // return $subjects;
        return view('backEnd.library.subject', ['subjects' => $subjects, 'bookCategories' => $bookCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error($exception->getMessage(), 'Failed');
            return redirect()->back();
        }
        */
    }

    public function store(LibrarySubjectRequest $librarySubjectRequest)
    {
        /*
        try {
        */
        $librarySubject = new LibrarySubject();
        $librarySubject->subject_name = $librarySubjectRequest->subject_name;
        $librarySubject->subject_type = $librarySubjectRequest->subject_type;
        $librarySubject->sb_category_id = $librarySubjectRequest->category;
        $librarySubject->subject_code = $librarySubjectRequest->subject_code;
        $librarySubject->school_id = Auth::user()->school_id;
        if (moduleStatusCheck('University')) {
            $librarySubject->un_academic_id = getAcademicId();
        } else {
            $librarySubject->academic_id = getAcademicId();
        }

        $librarySubject->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
        $subject = checkAdmin() == true ? LibrarySubject::find($id) : LibrarySubject::where('id', $id)->first();
        $subjects = LibrarySubject::where('school_id', auth()->user()->school_id)->with('category')->get();

        $bookCategories = SmBookCategory::get();

        return view('backEnd.library.subject', ['subject' => $subject, 'subjects' => $subjects, 'bookCategories' => $bookCategories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(LibrarySubjectRequest $librarySubjectRequest)
    {
        /*
        try {
        */
        if (checkAdmin() == true) {
            $subject = LibrarySubject::find($librarySubjectRequest->id);
        } else {
            $subject = LibrarySubject::where('id', $librarySubjectRequest->id)->first();
        }

        $subject->subject_name = $librarySubjectRequest->subject_name;
        $subject->subject_type = $librarySubjectRequest->subject_type;
        $subject->sb_category_id = $librarySubjectRequest->category;
        $subject->subject_code = $librarySubjectRequest->subject_code;
        $subject->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->route('library_subject');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request, $id)
    {
        /*
        try {
        */
        $tables = tableList::getTableList('book_subject_id', $id);
        /*
        try {
        */
        if ($tables == null) {
            // $delete_query = $section = LibrarySubject::destroy($request->id);
            if (checkAdmin() == true) {
                $delete_query = LibrarySubject::destroy($request->id);
            } else {
                $delete_query = LibrarySubject::where('id', $request->id)->where('school_id', Auth::user()->school_id)->delete();
            }

            if ($delete_query) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->route('library_subject');
            }
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

        }
        $msg = 'This data already used in  : '.$tables.' Please remove those data first';
        Toastr::error($msg, 'Failed');

        return redirect()->back();

        /*
        } catch (\Illuminate\Database\QueryException $e) {

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();
        }
        } catch (Exception $exception) {
        Toastr::error('Operation Failed', 'Failed');

        return redirect()->back();
        }
        */
    }
}
