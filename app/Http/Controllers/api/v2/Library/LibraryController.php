<?php

namespace App\Http\Controllers\api\v2\Library;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\StudentBookListResource;
use App\Http\Resources\v2\StudentIssuedBookListResource;
use App\Scopes\AcademicSchoolScope;
use App\Scopes\ActiveStatusSchoolScope;
use App\Scopes\SchoolScope;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmBook;
use App\SmBookIssue;
use App\SmLibraryMember;
use App\SmStudent;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function studentBookList(Request $request)
    {
        $all_book = SmBook::join('library_subjects', 'library_subjects.id', '=', 'sm_books.book_subject_id')->withoutGlobalScope(ActiveStatusSchoolScope::class)
            ->with(['bookCategory' => function ($q): void {
                $q->withoutGlobalScope(AcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }, 'bookSubject' => function ($q): void {
                $q->withoutGlobalScope(StatusAcademicSchoolScope::class)->where('school_id', auth()->user()->school_id);
            }])
            ->where('sm_books.school_id', auth()->user()->school_id)
            ->where('sm_books.active_status', 1);

        if (! empty($request->book_title)) {
            $all_book = $all_book->where('sm_books.book_title', 'like', sprintf('%%%s%%', $request->book_title))
                ->orWhere('library_subjects.subject_name', 'like', sprintf('%%%s%%', $request->book_title))
                ->orWhere('sm_books.book_number', '=', $request->book_title);

        }

        $all_book = $all_book->latest('sm_books.id')->get();

        $anonymousResourceCollection = StudentBookListResource::collection($all_book);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Library book list',
            ];
        }

        return response()->json($response);
    }

    public function studentBookIssue(Request $request)
    {
        $student_detail = SmStudent::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', auth()->user()->school_id)
            ->where('id', $request->student_id)
            ->firstOrFail();

        $library_member = SmLibraryMember::withoutGlobalScopes([StatusAcademicSchoolScope::class])
            ->where('member_type', 2)
            ->where('student_staff_id', $student_detail->user_id)
            ->where('school_id', auth()->user()->school_id)->first();
        if (empty($library_member)) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'You are not library member ! Please contact with librarian',
            ];

            return response()->json($response, 200);
        }

        $issueBooks = SmBookIssue::withoutGlobalScopes([StatusAcademicSchoolScope::class])
            ->where('member_id', $library_member->student_staff_id)
            ->leftjoin('sm_books', 'sm_books.id', 'sm_book_issues.book_id')
            ->leftjoin('library_subjects', 'library_subjects.id', 'sm_books.book_subject_id')
            ->where('sm_book_issues.issue_status', 'I')
            ->where('sm_book_issues.school_id', auth()->user()->school_id)
            ->get();

        $anonymousResourceCollection = StudentIssuedBookListResource::collection($issueBooks);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Issued book list',
            ];
        }

        return response()->json($response);
    }
}
