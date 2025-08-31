<?php

namespace App\Rules;

use App\LibrarySubject;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UniqueSubject implements Rule
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {

        $isExist = LibrarySubject::where('id', '!=', $this->id)->where('school_id', Auth::user()->school_id)->where('subject_name', $value)->exists();

        return ! $isExist;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'subject name has already been taken';
    }
}
