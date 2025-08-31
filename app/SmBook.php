<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmBook extends Model
{
    use HasFactory;

    public static function getMemberDetails($memberID)
    {

        try {
            return SmStudent::select('full_name', 'email', 'mobile')->where('id', '=', $memberID)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getMemberStaffsDetails($memberID)
    {

        try {
            return SmStaff::select('full_name', 'email', 'mobile')->where('user_id', '=', $memberID)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function getParentDetails($memberID)
    {

        try {
            return $getMemberDetails = SmParent::select('full_name', 'email', 'mobile')->where('user_id', '=', $memberID)->first();
        } catch (Exception $exception) {
            return [];
        }
    }

    public function bookCategory()
    {
        return $this->belongsTo(SmBookCategory::class, 'book_category_id', 'id')->withDefault();
    }

    public function bookSubject()
    {
        return $this->belongsTo(LibrarySubject::class, 'book_subject_id', 'id')->withDefault();
    }

    public function bookCategoryApi()
    {
        return $this->belongsTo(SmBookCategory::class, 'book_category_id', 'id')->withoutGlobalScopes();
    }

    public function bookSubjectApi()
    {
        return $this->belongsTo(LibrarySubject::class, 'book_subject_id', 'id')->withoutGlobalScopes();
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
