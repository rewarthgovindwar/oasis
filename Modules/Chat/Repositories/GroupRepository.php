<?php

namespace Modules\Chat\Repositories;

use Modules\Chat\Entities\Group;

class GroupRepository
{
    protected $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getAllGroup()
    {
        $groups = Group::whereHas('users', function ($q): void {
            $q->where('user_id', auth()->id());
        })->get();

        foreach ($groups as $group) {
            $last = $group->threads()->first();
            $group->custom_order = $last->id ?? 0;
        }

        return $groups->sortByDesc(function ($group) {
            return $group->custom_order;
        })->values();
    }
}
