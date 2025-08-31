<?php

namespace App\Models;

class CustomMixin
{
    public function whereTeacher()
    {
        return function () {
            return $this->where(function ($q): void {
                $q->where('role_id', 4)->orWhere('previous_role_id', 4);
            });
        };
    }

    public function whereRole()
    {
        return function ($role_id) {
            return $this->where(function ($q) use ($role_id): void {
                $q->where('role_id', $role_id)->orWhere('previous_role_id', $role_id);
            });
        };
    }
}
