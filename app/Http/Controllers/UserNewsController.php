<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\News\Entities\News;
use Modules\News\Entities\NewsCategory;
use Modules\News\Entities\NewsComment;
use Modules\News\Entities\NewsCommentReply;

class UserNewsController extends Controller
{
    public function index()
    {
        $news_categories = NewsCategory::select('id', 'title')->get();
        $user = Auth::user();
        $role_id = $user->role_id;
        $school_id = $user->school_id;

        $current_date = Carbon::now()->format('Y-m-d');
        $current_time = Carbon::now()->format('H:i:s');

        $news = News::with('newsCategory')->orderBy('id', 'desc')->get();

        $filtered_news_items = $news->filter(function ($item) use ($role_id, $school_id, $current_date, $current_time): bool {
            $available_for = json_decode($item->available_for, true);
            $school_ids = json_decode($item->school_id, true);

            return in_array($role_id, $available_for)
                && in_array($school_id, $school_ids)
                && (
                    $item->publish_date < $current_date ||
                    ($item->publish_date == $current_date && $item->time <= $current_time)
                );
        });

        $lengthAwarePaginator = $this->paginateCollection($filtered_news_items, 9, route('user-news.index'), []);

        return view('backEnd.userNews.index', ['news_categories' => $news_categories, 'news_items' => $lengthAwarePaginator]);
    }

    public function userNewsSearch(Request $request)
    {
        $role_id = auth()->user()->role_id;
        $school_id = auth()->user()->school_id;
        Carbon::now()->format('Y-m-d');
        Carbon::now()->format('H:i:s');

        $news_categories = NewsCategory::select('id', 'title')->get();

        $date = $request->date ? date('Y-m-d', strtotime($request->date)) : null;

        $news = News::when($request->news_category_id, function ($q) use ($request): void {
            $q->where('news_category_id', $request->news_category_id);
        })
            ->when($date, function ($q) use ($date): void {
                $q->where('publish_date', $date);
            })
            ->get();

        $filtered_news_items = $news->filter(function ($item) use ($role_id, $school_id): bool {
            $available_for = json_decode($item->available_for, true);
            $school_ids = json_decode($item->school_id, true);

            return in_array($role_id, $available_for) && in_array($school_id, $school_ids);
        });

        $query = [
            'news_category_id' => $request->news_category_id,
            'date' => $request->date,
        ];
        $lengthAwarePaginator = $this->paginateCollection($filtered_news_items, 9, route('user-news-search'), $query);

        return view('backEnd.userNews.index', ['news_categories' => $news_categories, 'news_items' => $lengthAwarePaginator]);
    }

    public function newsView($id)
    {
        $news = News::with('newsCategory', 'tags')->where('id', $id)->first();

        return view('backEnd.userNews.newsDetail', ['news' => $news]);
    }

    public function commentStore(Request $request)
    {
        $request->validate([
            'news_id' => 'required|exists:news,id',
            'comment' => 'required|string',
        ]);

        $user = Auth::user();

        $comment = new NewsComment();
        $comment->news_id = $request->news_id;
        $comment->comment = $request->comment;
        $comment->user_id = $user->id;
        $comment->school_id = $user->school_id;
        $comment->created_at = now();
        $comment->save();

        Toastr::success('Operation Success', 'Success');

        return redirect()->back();
    }

    public function commentReplyStore(Request $request)
    {
        $request->validate([
            'news_id' => 'required|exists:news,id',
            'comment_id' => 'required',
            'reply' => 'required|string',
        ]);

        $user = Auth::user();
        $reply = new NewsCommentReply();
        $reply->news_id = $request->news_id;
        $reply->user_id = $user->id;
        $reply->reply = $request->reply;
        $reply->created_at = now();
        $reply->news_comment_id = $request->comment_id;
        $reply->save();

        Toastr::success('Operation Success', 'Success');

        return redirect()->back();
    }

    public function userTopicCommentUpdate(Request $request)
    {
        $request->validate([
            'news_id' => 'required|exists:news,id',
            'comment' => 'required|string',
            'comment_id' => 'nullable',
        ]);

        $user = Auth::user();

        $comment = NewsComment::where('id', $request->comment_id)->first();
        $comment->news_id = $request->news_id;
        $comment->comment = $request->comment;
        $comment->user_id = $user->id;
        $comment->school_id = $user->school_id;
        $comment->updated_at = now();
        $comment->save();

        Toastr::success('Operation Success', 'Success');

        return redirect()->back();
    }

    public function userCommentReplyUpdate(Request $request)
    {
        $request->validate([
            'news_id' => 'required|exists:news,id',
            'comment_id' => 'required',
            'reply_id' => 'nullable',
            'reply' => 'required|string',
        ]);

        $user = Auth::user();
        $reply = NewsCommentReply::where('id', $request->reply_id)->first();
        $reply->news_id = $request->news_id;
        $reply->user_id = $user->id;
        $reply->reply = $request->reply;
        $reply->updated_at = now();
        $reply->news_comment_id = $request->comment_id;
        $reply->save();

        Toastr::success('Operation Success', 'Success');

        return redirect()->back();
    }

    public function userCommentDelete($id)
    {
        $comment = NewsComment::where('id', $id)->first();

        if (! $comment) {
            Toastr::error('Data Not Found', 'Error');

            return redirect()->back();
        }

        $comment->delete();
        Toastr::success('Operation Success', 'Success');

        return redirect()->back();

    }

    public function userCommentReplyDelete($id)
    {
        $comment = NewsCommentReply::where('id', $id)->first();

        if (! $comment) {
            Toastr::error('Data Not Found', 'Error');

            return redirect()->back();
        }

        $comment->delete();
        Toastr::success('Operation Success', 'Success');

        return redirect()->back();

    }

    private function paginateCollection(Collection $items, int $perPage = 9, $path = null, array $query = []): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator($currentItems, $items->count(), $perPage, $currentPage, [
            'path' => $path ?: LengthAwarePaginator::resolveCurrentPath(),
            'query' => $query,
        ]);
    }
}
