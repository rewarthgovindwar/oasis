<?php

namespace App\Models;

use App\SmNews;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmNewsComment extends Model
{
    use HasFactory;

    public function onlyChildrenFrontend()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->with(['onlyChildrenFrontend'])->where('status', 1);
    }

    public function onlyChildrenBackend()
    {
        return $this->hasMany(self::class, 'parent_id', 'id')->with(['onlyChildrenBackend']);
    }

    public function news()
    {
        return $this->belongsTo(SmNews::class, 'news_id', 'id')->withDefault('');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault('');
    }

    public function getCountApproveCommentAttribute()
    {
        return self::where('news_id', $this->news_id)->where('status', 1)->count();
    }

    public function getCountUnaproveCommentAttribute()
    {
        return self::where('news_id', $this->news_id)->where('status', 0)->count();
    }

    public function reply_to($parentId)
    {
        $commentData = self::with('user')->find($parentId);

        return $commentData->user->full_name;
    }
}
