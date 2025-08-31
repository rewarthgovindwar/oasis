<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class StatusAcademicSchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $auth = Auth::user();
        $table = $model->getTable();
        $academicId = '.academic_id';
        if (moduleStatusCheck('University')) {
            $columns = Schema::getColumnListing($table);
            if (in_array('un_academic_id', $columns)) {
                $academicId = '.un_academic_id';
            }

        }

        if (Auth::check()) {
            $academic = getAcademicId();
            if (moduleStatusCheck('Saas') === true && $auth->is_administrator === 'yes' && Session::get('isSchoolAdmin') === false && $auth->role_id === 1) {
                $builder->where($table.'.active_status', 1)->where($table.$academicId, getAcademicId());
            } else {
                $builder->where($table.'.active_status', 1)->where($table.$academicId, getAcademicId())->where($table.'.school_id', $auth->school_id);
            }
        } else {
            $builder->where($table.'.active_status', 1);
        }
    }
}
