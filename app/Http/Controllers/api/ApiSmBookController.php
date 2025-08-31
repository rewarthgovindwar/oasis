<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\LibrarySubject;
use App\Scopes\ActiveStatusSchoolScope;
use App\SmBook;
use App\SmBookCategory;
use App\SmSubject;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ApiSmBookController extends Controller
{
    public function Library_index(Request $request)
    {

        try {
            $books = DB::table('sm_books')
                ->leftjoin('library_subjects', 'sm_books.book_subject_id', '=', 'library_subjects.id')
                ->leftjoin('sm_book_categories', 'sm_books.book_category_id', '=', 'sm_book_categories.id')
                ->select('sm_books.*', 'library_subjects.subject_name', 'sm_book_categories.category_name')
                ->get();

            return ApiBaseMethod::sendResponse($books, null);

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function library_subject_index(Request $request)
    {
        try {
            $subjects = LibrarySubject::where('active_status', 1)->orderBy('id', 'DESC')->where('school_id', $request->user()->id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($subjects, null);
            }

            return view('backEnd.academics.subject', ['subjects' => $subjects]);
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_Library_index(Request $request, $school_id)
    {
        try {
            $books = SmBook::withoutGlobalScope(ActiveStatusSchoolScope::class)
                ->where('school_id', $school_id)
                ->get()
                ->map(function ($book): array {
                    return [
                        'id' => $book->id,
                        'book_title' => $book->book_title,
                        'book_number' => $book->book_number,
                        'isbn_no' => $book->isbn_no,
                        'category_name' => optional($book->bookCategoryApi)->category_name,
                        'publisher_name' => $book->publisher_name,
                        'author_name' => $book->author_name,
                        'quantity' => $book->quantity,
                        'book_price' => $book->book_price,
                        'subject_name' => optional($book->bookSubjectApi)->subject_name,
                        'rack_number' => $book->rack_number,
                    ];
                });

            if ($books->isEmpty()) {
                return ApiBaseMethod::sendResponse([], 'No books found.');
            }

            return ApiBaseMethod::sendResponse($books, 'Books retrieved successfully.');
        } catch (Exception $exception) {
            Log::error('Error in saas_Library_index: '.$exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong, please try again.',
            ], 500);
        }
    }

    public function saveBookData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_title' => 'required|max:200',
                'book_category_id' => 'required',
                'user_id' => 'required',
                'quantity' => 'sometimes|nullable|integer|min:0',
                'book_price' => 'sometimes|nullable|integer|min:0',
                'school_id' => 'required',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
        }

        try {

            $user = Auth()->user();

            $user_id = $user ? $user->id : $request->user_id;

            $smBook = new SmBook();
            $smBook->book_title = $request->book_title;
            $smBook->book_category_id = $request->book_category_id;
            $smBook->book_number = $request->book_number;
            $smBook->isbn_no = $request->isbn_no;
            $smBook->publisher_name = $request->publisher_name;
            $smBook->author_name = $request->author_name;
            $smBook->school_id = $request->school_id;
            if (@$request->subject_id) {
                $smBook->book_subject_id = $request->subject_id;
            }

            $smBook->rack_number = $request->rack_number;
            if (@$request->quantity !== '') {
                $smBook->quantity = $request->quantity;
            }

            if (@$request->book_price !== '') {
                $smBook->book_price = $request->book_price;
            }

            $smBook->details = $request->details;
            $smBook->post_date = date('Y-m-d');
            $smBook->created_by = $user_id;

            $results = $smBook->save();

            return ApiBaseMethod::sendResponse(null, 'New Book has been added successfully.');

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_saveBookData(Request $request)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_title' => 'required|max:200',
                'book_category_id' => 'required',
                'user_id' => 'required',
                'quantity' => 'sometimes|nullable|integer|min:0',
                'book_price' => 'sometimes|nullable|integer|min:0',
                'school_id' => 'required',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());

        }

        try {

            $user = Auth()->user();

            $user_id = $user ? $user->id : $request->user_id;

            $smBook = new SmBook();
            $smBook->book_title = $request->book_title;
            $smBook->book_category_id = $request->book_category_id;
            $smBook->book_number = $request->book_number;
            $smBook->isbn_no = $request->isbn_no;
            $smBook->publisher_name = $request->publisher_name;
            $smBook->author_name = $request->author_name;
            $smBook->school_id = $request->school_id;
            if (@$request->subject_id) {
                $smBook->book_subject_id = $request->subject_id;
            }

            $smBook->rack_number = $request->rack_number;
            if (@$request->quantity !== '') {
                $smBook->quantity = $request->quantity;
            }

            if (@$request->book_price !== '') {
                $smBook->book_price = $request->book_price;
            }

            $smBook->details = $request->details;
            $smBook->post_date = date('Y-m-d');
            $smBook->created_by = $user_id;

            $results = $smBook->save();

            return ApiBaseMethod::sendResponse(null, 'New Book has been added successfully.');

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function editBook(Request $request, $id)
    {

        try {
            $editData = SmBook::find($id);
            $categories = SmBookCategory::all();
            $subjects = SmSubject::all();

            $data = [];
            $data['editData'] = $editData->toArray();
            $data['categories'] = $categories->toArray();
            $data['subjects'] = $subjects->toArray();

            return ApiBaseMethod::sendResponse($data, null);

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_editBook(Request $request, $school_id, $id)
    {

        try {
            $editData = SmBook::where('school_id', $school_id)->find($id);
            $categories = SmBookCategory::where('school_id', $school_id)->get();
            $subjects = SmSubject::where('school_id', $school_id)->get();

            $data = [];
            $data['editData'] = $editData->toArray();
            $data['categories'] = $categories->toArray();
            $data['subjects'] = $subjects->toArray();

            return ApiBaseMethod::sendResponse($data, null);

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function updateBookData(Request $request, $id)
    {

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_title' => 'required',
                'book_category_id' => 'required',
                'user_id' => 'required',
                'quantity' => 'sometimes|nullable|integer|min:0',
                'book_price' => 'sometimes|nullable|integer|min:0',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());

        }

        try {

            $user = Auth()->user();

            $user_id = $user ? $user->id : $request->user_id;

            $books = SmBook::find($id);
            $books->book_title = $request->book_title;
            $books->book_category_id = $request->book_category_id;
            $books->book_number = $request->book_number;
            $books->isbn_no = $request->isbn_no;
            $books->publisher_name = $request->publisher_name;
            $books->author_name = $request->author_name;
            if (@$request->subject) {
                $books->subject_id = $request->subject;
            }

            $books->rack_number = $request->rack_number;
            if (@$request->quantity !== '') {
                $books->quantity = $request->quantity;
            }

            if (@$request->book_price !== '') {
                $books->book_price = $request->book_price;
            }

            $books->details = $request->details;
            $books->post_date = date('Y-m-d');
            $books->updated_by = $user_id;
            $results = $books->update();

            return ApiBaseMethod::sendResponse(null, 'Book Data has been updated successfully');

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_updateBookData(Request $request, $id)
    {

        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'book_title' => 'required',
                'book_category_id' => 'required',
                'user_id' => 'required',
                'quantity' => 'sometimes|nullable|integer|min:0',
                'book_price' => 'sometimes|nullable|integer|min:0',
                'school_id' => 'required',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());

        }

        try {

            $user = Auth()->user();

            $user_id = $user ? $user->id : $request->user_id;

            $books = SmBook::find($id);
            $books->book_title = $request->book_title;
            $books->book_category_id = $request->book_category_id;
            $books->book_number = $request->book_number;
            $books->isbn_no = $request->isbn_no;
            $books->publisher_name = $request->publisher_name;
            $books->author_name = $request->author_name;
            if (@$request->subject) {
                $books->subject_id = $request->subject;
            }

            $books->rack_number = $request->rack_number;
            if (@$request->quantity !== '') {
                $books->quantity = $request->quantity;
            }

            if (@$request->book_price !== '') {
                $books->book_price = $request->book_price;
            }

            $books->details = $request->details;
            $books->school_id = $request->school_id;
            $books->post_date = date('Y-m-d');
            $books->updated_by = $user_id;
            $results = $books->update();

            return ApiBaseMethod::sendResponse(null, 'Book Data has been updated successfully');

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function deleteBookView(Request $request, string $id)
    {

        try {
            $title = 'Are you sure to detete this Book?';
            $url = url('delete-book/'.$id);

            return ApiBaseMethod::sendResponse($id, null);
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function saas_deleteBookView(Request $request, string $school_id, string $id)
    {

        try {
            $title = 'Are you sure to detete this Book?';
            $url = url('school/'.$school_id.'/'.'delete-book/'.$id);

            return ApiBaseMethod::sendResponse($id, null);

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }

    public function deleteBook(Request $request, $school_id, $id)
    {

        try {
            $tables = \App\tableList::getTableList('book_id', $id);
            try {
                $result = SmBook::where('school_id', $school_id)->destroy($id);

                return ApiBaseMethod::sendResponse(null, 'Operation successful');
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : '.$tables.' Please remove those data first';

                return ApiBaseMethod::sendError('Error.', $msg);
            }
        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Error.', $exception->getMessage());
        }
    }
}
