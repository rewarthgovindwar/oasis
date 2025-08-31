<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibrarySubject extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'subject_name' => 'string',
    ];

    public function subjectBook()
    {
        return $this->belongsTo('App\Book', 'book', 'id');
    }

    public function category()
    {
        return $this->belongsTo(SmBookCategory::class, 'sb_category_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
