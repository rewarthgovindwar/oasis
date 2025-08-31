<?php

namespace App\Rules;

use App\SmBookCategory;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UniqueCategory implements Rule
{
    public $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function passes($attribute, $value)
    {

        $isExist = SmBookCategory::where('id', '!=', $this->id)->where('school_id', Auth::user()->school_id)->where('category_name', $value)->exists();

        return ! $isExist;
    }

    public function message()
    {
        return 'category name has already been taken';
    }
}
