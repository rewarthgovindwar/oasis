<?php

namespace App;

use App\Scopes\StatusAcademicSchoolScope;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\RolePermission\Entities\InfixRole;

class SmNoticeBoard extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'notice_title' => 'string',
        'notice_message' => 'string',
        'notice_date' => 'string',
        'publish_on' => 'string',
    ];

    public static function getRoleName($role_id)
    {
        try {
            $getRoleName = InfixRole::select('name')
                ->where('id', $role_id)
                ->first();

            return $getRoleName ?? false;

        } catch (Exception$exception) {
            return false;
        }
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new StatusAcademicSchoolScope);
    }
}
