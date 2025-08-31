<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use Exception;
use App\SmNews;
use DataTables;
use App\SmNewsCategory;
use Illuminate\Http\Request;
use App\Models\SmNewsComment;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\Admin\FrontSettings\SmNewsRequest;

class SmNewsController extends Controller
{


    public function index()
    {

        /*
        try {
        */
            $news = SmNews::where('school_id', app('school')->id)->get();
            $news_category = SmNewsCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.frontSettings.news.news_page', ['news' => $news, 'news_category' => $news_category]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmNewsRequest $smNewsRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/news/';
            $date = strtotime($smNewsRequest->date);
            $newformat = date('Y-m-d', $date);

            $smNews = new SmNews();
            $smNews->news_title = $smNewsRequest->title;
            $smNews->category_id = $smNewsRequest->category_id;
            $smNews->publish_date = $newformat;
            $smNews->image = fileUpload($smNewsRequest->image, $destination);
            $smNews->news_body = $smNewsRequest->description;
            $smNews->school_id = app('school')->id;
            $smNews->status = $smNewsRequest->status ?? 0;
            $smNews->mark_as_archive = $smNewsRequest->mark_as_archive ?? 0;
            if ($smNewsRequest->is_global == 1) {
                $smNews->is_global = $smNewsRequest->is_global;
                $smNews->auto_approve = 0;
                $smNews->is_comment = 0;
            } else {
                $smNews->is_global = 0;
                $smNews->auto_approve = $smNewsRequest->auto_approve ?? 0;
                $smNews->is_comment = $smNewsRequest->is_comment ?? 0;
            }

            $smNews->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        /*
        try {
        */
            $news = SmNews::where('school_id', app('school')->id)->get();
            $add_news = SmNews::find($id);
            $news_category = SmNewsCategory::where('school_id', app('school')->id)->get();

            return view('backEnd.frontSettings.news.news_page', ['add_news' => $add_news, 'news' => $news, 'news_category' => $news_category]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request)
    {
        /*
        try {
        */
            $date = strtotime($request->date);
            $newformat = date('Y-m-d', $date);
            $destination = 'public/uploads/news/';

            $news = SmNews::find($request->id);
            $news->news_title = $request->title;
            $news->category_id = $request->category_id;
            $news->publish_date = $newformat;
            $news->image = fileUpdate($news->image, $request->image, $destination);
            $news->news_body = $request->description;
            $news->school_id = app('school')->id;
            $news->status = $request->status ?? 0;
            $news->mark_as_archive = $request->mark_as_archive ?? 0;
            if ($request->is_global == 1) {
                $news->is_global = $request->is_global;
                $news->auto_approve = 0;
                $news->is_comment = 0;
            } else {
                $news->is_global = 0;
                $news->auto_approve = $request->auto_approve ?? 0;
                $news->is_comment = $request->is_comment ?? 0;
            }

            $news->save();
            Toastr::success('Operation successful', 'Success');

            return redirect('news');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function newsDetails($id)
    {
        /*
        try {
        */
            $news = SmNews::find($id);

            return view('backEnd.frontSettings.news.news_details', ['news' => $news]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function forDeleteNews($id)
    {
        /*
        try {
        */
            return view('backEnd.frontSettings.news.delete_modal', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete($id)
    {
        /*
        try {
        */
            SmNews::destroy($id);
            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function commentList()
    {
        /*
        try {
        */
            return view('backEnd.frontSettings.news.news_comment_page');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function commentListDatatable()
    {
        if (request()->comment_filter_type) {
            if (request()->comment_filter_type == 'approve') {
                $news_comments = SmNewsComment::with('news', 'user')
                    ->where('news_id', request()->comment_news_id)
                    ->where('status', 1);
            } else {
                $news_comments = SmNewsComment::with('news', 'user')
                    ->where('news_id', request()->comment_news_id)
                    ->where('status', 0);
            }
        } else {
            $news_comments = SmNewsComment::with('news', 'user');
        }

        return DataTables::of($news_comments)
            ->addIndexColumn()
            ->addColumn('user_name', function ($comment) {
                return view('backEnd.frontSettings.news._news_author_view', ['comment' => $comment]);
            })
            ->addColumn('message', function ($comment) {
                return view('backEnd.frontSettings.news._news_message_view', ['comment' => $comment]);
            })
            ->addColumn('post_info', function ($comment) {
                return view('backEnd.frontSettings.news._news_response_view', ['comment' => $comment]);
            })
            ->addColumn('created_at', function ($comment) {
                return dateConvert($comment->created_at);
            })
            ->rawColumns(['user_name', 'message', 'post_info'])
            ->make(true);
    }

    public function commentUpdate(Request $request)
    {
        /*
        try {
        */
            $commentData = SmNewsComment::find($request->comment_id);
            $commentData->message = $request->message;
            $commentData->update();

            return response()->json(['success' => true]);
        /*
        } catch (Exception $exception) {
            return response()->json(['error' => $exception]);
        }
        */
    }

    public function commentDelete(Request $request)
    {
        /*
        try {
        */
            $parentDatas = SmNewsComment::where('parent_id', $request->comment_id)->get();
            if ($parentDatas->count() > 0) {
                foreach ($parentDatas as $parentData) {
                    $parentData->delete();
                }
            }

            SmNewsComment::destroy($request->comment_id);
            Toastr::success('Comment Deleted Successful', 'Success');
            if ($request->type == 'frontend') {
                return redirect()->route('frontend.news-details', $request->news_id);
            }

            return redirect()->route('news-comment-list');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function commentStatus($id, $news_id, $type)
    {
        /*
        try {
        */
            $data = SmNewsComment::find($id);
            if ($data->status == 1) {
                $data->status = 0;
                $data->update();
            } else {
                $data->status = 1;
                $data->update();
            }

            Toastr::success('Comment Status Update Successful', 'Success');
            if ($type == 'frontend') {
                return redirect()->route('frontend.news-details', $news_id);
            }

            return redirect()->route('news-comment-list');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function commentStatusBackend($id)
    {
        /*
        try {
        */
            $data = SmNewsComment::find($id);
            if ($data->status == 1) {
                $data->status = 0;
                $data->update();
            } else {
                $data->status = 1;
                $data->update();
            }

            return response()->json(['success' => true]);
        /*
        } catch (Exception $exception) {
            return response()->json(['error' => $exception]);
        }
        */
    }

    public function viewNewsCategory($id)
    {
        /*
        try {
        */
            $category_id = SmNewsCategory::find($id);
            $newsCtaegories = SmNews::where('category_id', $category_id->id)
                ->where('school_id', app('school')->id)
                ->get();

            return view('frontEnd.home.category_news', ['category_id' => $category_id, 'newsCtaegories' => $newsCtaegories]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
