<?php

namespace App\Traits;

use Carbon\Carbon;
use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait FileStore
{
    public static function saveFile(UploadedFile $uploadedFile): ?string
    {
        $current_date = Carbon::now()->format('d-m-Y');
        if (! File::isDirectory('uploads/file/'.$current_date)) {
            File::makeDirectory('uploads/file/'.$current_date, 0777, true, true);
        }

        $uploadedFile->extension();
        uniqid();
        // $file->storeAs('uploads/file/'.$current_date.'/', $file_name);
        // $s = Storage::disk('custom')->put('file/'.$current_date, $file);
        // return 'uploads/file/'.$current_date.'/'.$file_name;
        $s = Storage::disk('custom')->put('file/'.$current_date, $uploadedFile);

        return 'uploads/'.$s;
    }
}
