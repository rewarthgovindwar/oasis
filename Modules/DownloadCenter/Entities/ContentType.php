<?php

namespace Modules\DownloadCenter\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    use HasFactory;

    protected $fillable = [];

    /* protected static function newFactory()
    {
        return \Modules\DownloadCenter\Database\factories\ContentTypeFactory::new();
    } */
}
