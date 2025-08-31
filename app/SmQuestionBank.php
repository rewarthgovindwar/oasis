<?php

namespace App;
use App\Models\Shift;
use App\Scopes\StatusAcademicSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmQuestionBank extends Model
{
    use HasFactory;

    public function questionGroup()
    {
        return $this->belongsTo(SmQuestionGroup::class, 'q_group_id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function questionLevel()
    {
        return $this->belongsTo(SmQuestionLevel::class, 'question_level_id');
    }

    public function class()
    {
        return $this->belongsTo(SmClass::class, 'class_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
    }

    public function section()
    {
        if (moduleStatusCheck('University')) {
            return $this->belongsTo(SmSection::class, 'un_section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);
        }

        return $this->belongsTo(SmSection::class, 'section_id', 'id')->withOutGlobalScope(StatusAcademicSchoolScope::class);

    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }

    public function unSemesterLabel()
    {
        return $this->belongsTo(\Modules\University\Entities\UnSemesterLabel::class, 'un_semester_label_id', 'id')->withDefault();
    }

    public function questionMu()
    {
        return $this->hasMany(SmQuestionBankMuOption::class, 'question_bank_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
